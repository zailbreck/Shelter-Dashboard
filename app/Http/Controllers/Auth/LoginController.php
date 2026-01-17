<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

/**
 * Login controller
 * 
 * Handles user authentication with 2FA support
 * 
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller
{
    /**
     * @param AuthService $authService Authentication service
     */
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->authService->attemptLogin(
            $validated,
            $request->boolean('remember')
        );

        return redirect($result['redirect']);
    }

    /**
     * Show 2FA verification form
     */
    public function show2faForm()
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    /**
     * Verify 2FA code
     */
    public function verify2fa(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $this->authService->verify2FACode($validated['code']);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forced password change form
     */
    public function showForcePasswordChange()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user->password_change_required) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    /**
     * Handle forced password change
     */
    public function forcePasswordChange(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'password_change_required' => false,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully!');
    }
}
