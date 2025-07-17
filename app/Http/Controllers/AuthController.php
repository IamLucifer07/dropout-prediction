<?php

namespace App\Http\Controllers;

use App\Models\CollegeAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    // Show registration form
    public function showRegistrationForm()
    {
        return inertia('auth/Register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:college_admins'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'college_name' => ['required', 'string', 'max:255'],
        ]);

        $admin = CollegeAdmin::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'college_name' => $validated['college_name'],
        ]);

        // Log the user in
        Auth::guard('web')->login($admin);
        // Redirect to dashboard
        return Redirect::route('dashboard');
    }

    // Show login form
    public function showLoginForm()
    {
        return inertia('auth/Login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = CollegeAdmin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Log the user in
        Auth::guard('web')->login($admin);

        // Redirect
        return Redirect::intended(route('dashboard'));
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login');
    }
}
