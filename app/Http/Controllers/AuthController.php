<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string', 'remember' => 'boolean']);
        $credentials = ['email' => $data['email'], 'password' => $data['password']];
        $credentials['is_admin'] = $request->is('admin/*');
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect. Please try again.']);
        }
        $request->session()->regenerate();

        return redirect($request->user()->is_admin ? '/admin/dashboard' : '/my-account')->with('success', 'Welcome back, '.$request->user()->name.'!');
    }

    public function register(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()]]);
        Auth::login(User::create($data));
        $request->session()->regenerate();
        $request->session()->flash('success', 'Your account is ready. Welcome!');

        return Inertia::location('/my-account');
    }

    public function profile(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)], 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500']);
        if ($data['email'] !== $request->user()->email) {
            $request->user()->email_verified_at = null;
        }
        $request->user()->fill($data)->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->mixedCase()->numbers()]]);
        $request->user()->update(['password' => Hash::make($data['password'])]);
        $request->session()->regenerate();

        return back()->with('success', 'Password updated successfully.');
    }

    public function logout(Request $request)
    {
        $admin = $request->user()->is_admin;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($admin ? '/admin/login' : '/login')->with('success', 'You have been logged out.');
    }
}
