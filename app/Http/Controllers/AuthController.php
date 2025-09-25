<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function index()
    {

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'userid'   => 'required',
            'password' => 'required',
        ]);

        $userid   = $request->input('userid');
        $password = $request->input('password');
        $data     = [
            'status'          => 'failed',
            'message'         => null,
            'stationRedirect' => route('stations.index'),
        ];

        if (env('APP_ENV') == 'local') {
            $data['status']  = 'success';
            $data['message'] = 'เข้าสู่ระบบสำเร็จ';

            $user = User::where('userid', $userid)->first();
            if ($user == null) {
                $response = Http::withHeaders(['token' => env('API_AUTH_KEY')])
                    ->timeout(30)
                    ->post('http://172.20.1.12/dbstaff/api/getuser', [
                        'userid' => $userid,
                    ]);
                if ($response->successful()) {
                    $responseData = $response->json();
                    if ($responseData['status'] == 1) {
                        $user             = new User();
                        $user->userid     = $userid;
                        $user->name       = $responseData['user']['name'];
                        $user->position   = $responseData['user']['position'];
                        $user->department = $responseData['user']['department'];
                        $user->division   = $responseData['user']['division'];
                        $user->save();
                    }
                }
            }
            Auth::login($user);
            $data['stationRedirect'] = $user->stationRedirect;

            return response()->json($data, 200);
        } else {
            $response = Http::withHeaders(['token' => env('API_AUTH_KEY')])
                ->timeout(30)
                ->post('http://172.20.1.12/dbstaff/api/auth', [
                    'userid'   => $userid,
                    'password' => $password,
                ]);

            if (! $response->successful()) {
                $data['message'] = 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง';
            }
            $responseData = $response->json();

            if (! isset($responseData['status'])) {
                $data['message'] = 'ข้อมูลที่ได้รับจากระบบไม่ถูกต้อง';
            }

            if ($responseData['status'] == 1) {
                $user = User::where('userid', $userid)->first();
                if (! $user) {
                    $user         = new User();
                    $user->userid = $userid;
                }
                $user->name       = $responseData['user']['name'];
                $user->position   = $responseData['user']['position'];
                $user->department = $responseData['user']['department'];
                $user->division   = $responseData['user']['division'];
                $user->save();

                Auth::login($user);
                $data['status']          = 'success';
                $data['message']         = 'เข้าสู่ระบบสำเร็จ';
                $data['stationRedirect'] = $user->stationRedirect;
            }
        }

        return response()->json($data, 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
