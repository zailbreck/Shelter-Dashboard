<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Authentication service
 * 
 * Handles authentication, 2FA, and password management business logic
 * 
 * @package App\Services
 */
class AuthService
{
    /**
     * @param UserRepositoryInterface $userRepo User repository
     */
    public function __construct(
        private UserRepositoryInterface $userRepo
    ) {
    }

    /**
     * Attempt login with credentials
     * 
     * @param array $credentials Email and password
     * @param bool $remember Remember me flag
     * @return array Login result with user and redirect info
     * @throws ValidationException
     */
    public function attemptLogin(array $credentials, bool $remember = false): array
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        // Check if 2FA is enabled
        if ($user->has2FA()) {
            session(['2fa:user:id' => $user->id, '2fa:remember' => $remember]);
            Auth::logout();

            return [
                'requires_2fa' => true,
                'redirect' => route('2fa.verify'),
            ];
        }

        // First time login - redirect to 2FA setup (keep user authenticated)
        if (!$user->google2fa_enabled) {
            return [
                'requires_setup' => true,
                'redirect' => route('2fa.setup'),
            ];
        }

        return [
            'success' => true,
            'redirect' => route('dashboard'),
        ];
    }

    /**
     * Verify 2FA code
     * 
     * @param string $code 6-digit OTP code
     * @return User Authenticated user
     * @throws ValidationException
     */
    public function verify2FACode(string $code): User
    {
        $userId = session('2fa:user:id');

        if (!$userId) {
            throw ValidationException::withMessages([
                'code' => 'Session expired. Please log in again.',
            ]);
        }

        $user = User::find($userId);

        if (!$user || !$user->verify2FACode($code)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid.',
            ]);
        }

        session()->forget('2fa:user:id');
        Auth::login($user, session('2fa:remember', false));
        session()->forget('2fa:remember');

        return $user;
    }

    /**
     * Generate 2FA secret for user
     * 
     * @param User $user User model
     * @return array QR code and secret
     */
    public function generate2FASecret(User $user): array
    {
        $secret = $user->generate2FASecret();
        $qrCodeUrl = $user->get2FAQRCode();

        // Generate QR code SVG
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(300),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        $qrCode = $writer->writeString($qrCodeUrl);

        return [
            'qrCode' => $qrCode,
            'secret' => $secret,
        ];
    }

    /**
     * Enable 2FA after verification
     * 
     * @param User $user User model
     * @param string $code Verification code
     * @return bool Success status
     * @throws ValidationException
     */
    public function enable2FA(User $user, string $code): bool
    {
        if (!$user->verify2FACode($code)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid. Please try again.',
            ]);
        }

        return $this->userRepo->enable2FA($user->id);
    }

    /**
     * Disable 2FA with password confirmation
     * 
     * @param User $user User model
     * @param string $password Current password
     * @return bool Success status
     * @throws ValidationException
     */
    public function disable2FA(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        return $this->userRepo->disable2FA($user->id);
    }

    /**
     * Update user password
     * 
     * @param User $user User model
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool Success status
     * @throws ValidationException
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        return $this->userRepo->updatePassword($user->id, $newPassword);
    }

    /**
     * Update user profile
     * 
     * @param User $user User model
     * @param array $data Profile data
     * @return bool Success status
     */
    public function updateProfile(User $user, array $data): bool
    {
        return $this->userRepo->updateProfile($user->id, $data);
    }
}
