<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AttendeeAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'lastname' => 'required|string',
            'registration_code' => 'required|string|size:6',
        ]);

        $attendee = Attendee::where('lastname', $request->lastname)
            ->where('registration_code', $request->registration_code)
            ->first();

        if (!$attendee) {
            return response()->json(['message' => 'Invalid login'], 401);
        }

        // Generate token (MD5 hash of username)
        $token = md5($attendee->username);
        $attendee->login_token = $token;
        $attendee->save();

        return response()->json([
            'firstname' => $attendee->firstname,
            'lastname' => $attendee->lastname,
            'username' => $attendee->username,
            'email' => $attendee->email,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $attendee = Attendee::where('login_token', $token)->first();

        if (!$attendee) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $attendee->login_token = null;
        $attendee->save();

        return response()->json(['message' => 'Logout success']);
    }
}
