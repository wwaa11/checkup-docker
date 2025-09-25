<?php
namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::where('enabled', true)->get();

        return view('stations.index', compact('stations'));
    }

    public function staionIndex(Request $request)
    {
        $station = Station::where('code', $request->station)->first();
        if (! $station || ! $station->enabled) {
            abort(404);
        }
        $user          = Auth::user();
        $user->station = $station->code;
        $user->save();

        $allStations = Station::where('enabled', true)->get();

        return view('stations.station', compact('station', 'allStations'));
    }
}
