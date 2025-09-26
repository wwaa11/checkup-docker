<?php
namespace App\Http\Controllers;

use App\Jobs\GenerateNumber;
use DB;
use Illuminate\Support\Facades\Redis;

class ServiceController extends Controller
{

    public function test()
    {
        $newDatas = DB::connection('NEWUI')
            ->table('HIS_CHKUP_HEADER')
            ->join('HIS_CHECKUP_STATION_DETAIL', 'HIS_CHKUP_HEADER.RequestNo', '=', 'HIS_CHECKUP_STATION_DETAIL.CheckUpRequestNo')
            ->whereDate('HIS_CHECKUP_STATION_DETAIL.Visitdate', date('Y-m-d'))
            ->where('HIS_CHKUP_HEADER.Clinic', '1800')
            ->where('HIS_CHKUP_HEADER.ComputerLocation', 'LIKE', 'B12%')
            ->whereIn('HIS_CHECKUP_STATION_DETAIL.StationCode', ['01', '011', '02', '03', '04', '06', '07', '08', '09', '10'])
            ->select(
                'HIS_CHECKUP_STATION_DETAIL.Visitdate',
                'HIS_CHECKUP_STATION_DETAIL.HN',
                'HIS_CHECKUP_STATION_DETAIL.VN',
                'HIS_CHKUP_HEADER.FirstName',
                'HIS_CHKUP_HEADER.LastName',
                'HIS_CHKUP_HEADER.EnglishResult',
                'HIS_CHECKUP_STATION_DETAIL.StationCode',
                'HIS_CHECKUP_STATION_DETAIL.FacilityRequestNo',
            )
            ->orderBy('HIS_CHECKUP_STATION_DETAIL.Visitdate', 'DESC')
            ->orderBy('HIS_CHECKUP_STATION_DETAIL.VN', 'ASC')
            ->orderBy('HIS_CHECKUP_STATION_DETAIL.StationCode', 'ASC')
            ->get();

        dd($newDatas->pluck('HN'));

        $patients = Patient::where('date', date('Y-m-d'))->get();
        $allTasks = Patienttask::where('date', date('Y-m-d'))->get();
        foreach ($newDatas as $data) {
            $patient = collect($patients)->where('hn', $data->HN)->first();
            if ($patient == null) {
                $patient       = new Patient;
                $patient->date = date('Y-m-d');
                $patient->hn   = $data->HN;
                $patient->name = $data->FirstName . ' ' . $data->LastName;
                $patient->lang = ($data->EnglishResult == 0) ? 'th' : 'en';
                $patient->vn   = $data->VN;
                $patient->save();

                $this->setLog($patient, 'นำเข้าข้อมูลผู้ป่วยจาก NewUI');
            }

            $checkValid = false;
            switch ($data->StationCode) {
                case '01':
                    $checkValid = true;
                    $code       = 'b12_vitalsign';
                    $text       = 'Vital Sign';
                    break;
                case '011':
                    $checkValid = true;
                    $code       = 'b12_lab';
                    $text       = 'Lab';
                    break;
                case '02':
                    $checkValid = true;
                    $code       = 'b12_abi';
                    $text       = 'ABI';
                    break;
                case '04':
                    $checkValid = true;
                    $code       = 'b12_estecho';
                    $text       = 'Est & Echo';
                    break;
                case '06':
                    $checkValid = true;
                    $code       = 'b12_xray';
                    $text       = 'Xray';
                    break;
                case '07':
                    $checkValid = true;
                    $code       = 'b12_ultrasound';
                    $text       = 'Ultrasound';
                    break;
                case '08':
                    $checkValid = true;
                    $code       = 'b12_mammogram';
                    $text       = 'Mammogram';
                    break;
                case '09':
                    $checkValid = true;
                    $code       = 'b12_bonedensity';
                    $text       = 'Bone Density';
                    break;
            }

            if ($checkValid) {
                $task = collect($allTasks)->where('hn', $data->HN)->where('code', $code)->first();
                if ($task == null) {
                    $newTask             = new Patienttask;
                    $newTask->patient_id = $patient->id;
                    $newTask->date       = date('Y-m-d');
                    $newTask->hn         = $data->HN;
                    $newTask->vn         = $data->VN;
                    $newTask->code       = $code;
                    if ($code == 'b12_vitalsign') {
                        $newTask->assign = now();
                    }
                    if ($code == 'b12_lab') {
                        $newTask->memo1 = $data->FacilityRequestNo;
                    }
                    $newTask->save();

                    // Patient Log
                    $this->setLog($patient, 'สร้างรายการ Check UP : ' . $text);
                    if ($code == 'b12_vitalsign') {
                        $this->setLog($patient, 'ลงทะเบียนคิวที่ : วัดความดัน');
                    }
                }
            }
        }

        ProcessCreateTask::dispatch()->delay(5);
    }

    private function getQueueRedisKey($type = 'default')
    {
        $prefix    = config('database.redis.options.prefix');
        $queueName = config('queue.connections.redis.queue', 'default');

        switch ($type) {
            case 'delayed':
                $key = $prefix . 'queues:' . $queueName . ':delayed';
                break;
            case 'reserved':
                $key = $prefix . 'queues:' . $queueName . ':reserved';
                break;
            case 'failed':
                $key = 'queues:failed';
                break;
            default:
                $key = $prefix . 'queues:' . $queueName;
                break;
        }

        return $key;
    }

    public function index()
    {
        $currentJobs = $this->getCurrentJobs();
        $delayedJobs = $this->getDelayedJobs();

        return view('services.index', compact('currentJobs', 'delayedJobs'));
    }

    private function getCurrentJobs($limit = 10)
    {
        $key = $this->getQueueRedisKey('default');
        $job = Redis::executeRaw(['LINDEX', $key, 0]);

        $decoded    = json_decode($job, true);
        $jobDetails = [
            'id'         => $decoded['id'] ?? 'N/A',
            'job'        => $decoded['displayName'] ?? $decoded['job'] ?? 'Unknown',
            'attempts'   => $decoded['attempts'] ?? 0,
            'created_at' => isset($decoded['pushedAt']) ? date('Y-m-d H:i:s', $decoded['pushedAt']) : 'N/A',
            'payload'    => $decoded['data'] ?? [],
        ];

        return $jobDetails;
    }

    private function getDelayedJobs($limit = 10)
    {
        $key  = $this->getQueueRedisKey('delayed');
        $jobs = Redis::executeRaw(['ZRANGE', $key, '0', (string) ($limit - 1), 'WITHSCORES']);

        $delayedDetails = [];
        for ($i = 0; $i < count($jobs); $i += 2) {
            $job       = json_decode($jobs[$i], true);
            $executeAt = $jobs[$i + 1];

            $delayedDetails[] = [
                'id'         => $job['id'] ?? 'N / A',
                'job'        => $job['displayName'] ?? $job['job'] ?? 'Unknown',
                'execute_at' => date('Y - m - d H: i: s', $executeAt),
                'payload'    => $job['data'] ?? [],
            ];
        }

        return $delayedDetails;
    }

    public function dispatchGenerateNumber()
    {
        GenerateNumber::dispatch()->onQueue('number');
        logger()->channel('services')->info('GenerateNumber dispatch: ');

        return response()->json(['message' => 'Generate number dispatched']);
    }
}
