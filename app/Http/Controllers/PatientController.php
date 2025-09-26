<?php
namespace App\Http\Controllers;

use App\Models\NumberMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PatientController extends Controller
{
    private function distantCheck($input_lat, $input_lon)
    {
        $distant = 100.5;

        $base_lat      = "13.7530601";
        $base_lon      = "100.5688306";
        $theta         = $input_lon - $base_lon;
        $dist          = sin(deg2rad($input_lat)) * sin(deg2rad($base_lat)) + cos(deg2rad($input_lat)) * cos(deg2rad($base_lat)) * cos(deg2rad($theta));
        $dist          = acos($dist);
        $dist          = rad2deg($dist);
        $miles         = $dist * 60 * 1.1515;
        $outputDistant = $miles * 1.609344;

        return $outputDistant > $distant ? false : true;
    }

    public function sms(Request $request, $hashHN)
    {
        $hn = DB::connection('SMS')
            ->table('TB_HAS_HN')
            ->where('hasHN', $hashHN)
            ->select('HN')
            ->first();
        if (! $hn) {
            $patient = false;
            $message = 'URL not found!';
            logger()->channel('patients')->error($hashHN . ' : Patient not found');

            return view('patient.invalid', compact('patient', 'message'));
        }
        $hn       = $hn->HN;
        $response = Http::withHeaders(['key' => env('API_SSB_KEY')])
            ->timeout(30)
            ->post('http://172.20.1.22/w_phr/api/patient/info', [
                'hn' => $hn,
            ]);
        if (! $response->successful()) {
            $patient = false;
            $message = 'Patient not found!';
            logger()->channel('patients')->error($hn . ' : Patient response error: ' . $response->json());

            return view('patient.invalid', compact('patient', 'message'));
        }
        $patientInfo  = $response->json();
        $prefer_thai  = ($patientInfo['patient']['race'] == 'THA') ? true : false;
        $patient_name = $patientInfo['patient']['name']['first_th'] . ' ' . $patientInfo['patient']['name']['last_th'];
        $lineId       = $patientInfo['patient']['line_id'];
        $patient      = [
            'hn'   => $hn,
            'name' => $patient_name,
        ];
        $appointment = DB::connection('SSB')
            ->table('HNAPPMNT_HEADER')
            ->whereDate('HNAPPMNT_HEADER.AppointDateTime', date('Y-m-d'))
            ->where('HNAPPMNT_HEADER.Clinic', '1800')
            ->where('HNAPPMNT_HEADER.HN', $hn)
            ->select(
                'HNAPPMNT_HEADER.AppointDateTime',
                'HNAPPMNT_HEADER.AppmntProcedureCode1',
                'HNAPPMNT_HEADER.AppmntProcedureCode2',
                'HNAPPMNT_HEADER.AppmntProcedureCode3',
                'HNAPPMNT_HEADER.AppmntProcedureCode4',
                'HNAPPMNT_HEADER.AppmntProcedureCode5',
            )
            ->first();
        if ($appointment == null) {
            logger()->channel('patients')->error($hn . ' : Patient appointment not found');

            return view('patient.invalid', compact('patient', 'prefer_thai'));
        }

        $numberMaster = NumberMaster::whereDate('date', date('Y-m-d'))->where('hn', $hn)->first();
        if ($numberMaster == null) {
            $followUpCode = ['A1', 'A2', 'A3', 'A4', 'A7', 'A10', 'AI', 'AB2', 'AB3', 'AG2', 'AG3', 'A31', 'A129'];
            if (in_array($appointment->AppmntProcedureCode1, $followUpCode) ||
                in_array($appointment->AppmntProcedureCode2, $followUpCode) ||
                in_array($appointment->AppmntProcedureCode3, $followUpCode) ||
                in_array($appointment->AppmntProcedureCode4, $followUpCode) ||
                in_array($appointment->AppmntProcedureCode5, $followUpCode)) {
                $code = 'U';
            } else {
                $time = date('H', strtotime($appointment->AppointDateTime));
                switch ($time) {
                    case '7':
                        $code = 'A';
                        break;
                    case '8':
                        $code = 'B';
                        break;
                    case '9':
                        $code = 'C';
                        break;
                    case '10':
                        $code = 'D';
                        break;
                    case '11':
                        $code = 'E';
                        break;
                    case '12':
                        $code = 'H';
                        break;
                    case '13':
                        $code = 'V';
                        break;
                    default:
                        $code = 'M';
                        break;
                }

                if ($code == 'V' && substr($appointment->AppointmentNo, 0, 3) !== 'VAP') {
                    $code = "M";
                }
            }
            NumberMaster::create([
                'date'           => date('Y-m-d'),
                'hn'             => $hn,
                'name'           => $patient_name,
                'type'           => $code,
                'prefer_english' => $prefer_thai ? false : true,
                'line'           => $lineId,
            ]);

            logger()->channel('patients')->info($hn . ' : Patient master created');
        }

        return view('patient.sms', compact('patient', 'appointment', 'prefer_thai', 'numberMaster'));
    }

    public function smsCheck(Request $request, $HN)
    {
        $lat = $request->input('lat');
        $log = $request->input('log');

        $distant  = $this->distantCheck($lat, $log);
        $canCheck = false;
        if ($distant) {
            $numberMaster = NumberMaster::whereDate('date', date('Y-m-d'))
                ->where('hn', $HN)
                ->first();
            $canCheck = $numberMaster->checkin == null ? true : false;
        }

        return response()->json([
            'distant'  => $distant,
            'canCheck' => $canCheck,
            'master'   => $numberMaster,
        ]);
    }

    public function smscheckin(Request $request, $HN)
    {
        $numberMaster          = NumberMaster::whereDate('date', date('Y-m-d'))->where('hn', $HN)->first();
        $numberMaster->checkin = date('Y-m-d H:i:s');
        $numberMaster->save();

        logger()->channel('patients')->info($HN . ' : Patient checkin');

        return response()->json([
            'success' => true,
            'message' => 'Check in success!',
        ]);
    }

    public function smsCheckNumber(Request $request, $HN)
    {
        $numberMaster = NumberMaster::whereDate('date', date('Y-m-d'))->where('hn', $HN)->first();

        if ($numberMaster->number == null) {
            return response()->json([
                'success' => false,
                'message' => 'Number not found!',
            ]);
        }

        return response()->json([
            'success' => true,
            'number'  => $numberMaster->number,
        ]);
    }
}
