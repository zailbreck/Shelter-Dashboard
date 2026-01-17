<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Two-factor authentication controller
 * 
 * Handles 2FA setup and management
 * 
 * @package App\Http\Controllers\Auth
 */
class TwoFactorController extends Controller
{
    /**
     * @param AuthService $authService Authentication service
     */
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        $data = $this->authService->generate2FASecret($user);

        return view('auth.2fa-setup', $data);
    }

    /**
     * Verify and enable 2FA
     */
    public function verifySetup(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);

        $this->authService->enable2FA(
            Auth::user(),
            $validated['code']
        );

        return redirect()->route('dashboard')
            ->with('success', '2FA has been enabled successfully!');
    }

    /**
     * Enable 2FA from settings
     */
    public function enable()
    {
        return redirect()->route('2fa.setup');
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required',
        ]);

        $this->authService->disable2FA(
            Auth::user(),
            $validated['password']
        );

        return back()->with('success', '2FA has been disabled.');
    }
}
