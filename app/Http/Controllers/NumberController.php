<?php

namespace App\Http\Controllers;

use App\Events\NumberBroadcast;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NumberController extends Controller
{
    /**
     * Show the send page for user1
     */
    public function sendPage()
    {
        return view('send');
    }

    /**
     * Show the receive page for all users
     */
    public function receivePage()
    {
        return view('receive');
    }

    /**
     * Send a number to all connected users
     */
    public function sendNumber(Request $request): JsonResponse
    {
        $request->validate([
            'number' => 'required|numeric',
        ]);

        $number = $request->input('number');
        
        // Broadcast the number to all connected users
        broadcast(new NumberBroadcast($number, 'user1'));

        return response()->json([
            'success' => true,
            'message' => 'Number sent successfully',
            'number' => $number,
        ]);
    }
}