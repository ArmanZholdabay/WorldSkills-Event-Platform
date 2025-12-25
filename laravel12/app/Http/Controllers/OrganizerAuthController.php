<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OrganizerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('organizer.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $organizer = Organizer::where('email', $request->email)->first();

        if (!$organizer || !Hash::check($request->password, $organizer->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Email or password not correct'],
            ]);
        }

        Auth::guard('organizer')->login($organizer);

        return redirect()->route('organizer.events.index');
    }

    public function logout(Request $request)
    {
        Auth::guard('organizer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('organizer.login');
    }
}
