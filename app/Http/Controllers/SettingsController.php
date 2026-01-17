<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/**
 * Settings controller
 * 
 * Handles user settings and account management
 * 
 * @package App\Http\Controllers
 */
class SettingsController extends Controller
{
    /**
     * @param AuthService $authService Authentication service
     */
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Show settings page
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $this->authService->updateProfile($user, $validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $this->authService->updatePassword(
            Auth::user(),
            $validated['current_password'],
            $validated['password']
        );

        return back()->with('success', 'Password changed successfully!');
    }
}
