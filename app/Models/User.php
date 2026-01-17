<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google2fa_secret',
        'google2fa_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'google2fa_enabled' => 'boolean',
        ];
    }

    /**
     * Set the 2FA secret (encrypted).
     */
    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the 2FA secret (decrypted).
     */
    public function getGoogle2faSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function has2FA(): bool
    {
        return $this->google2fa_enabled && !empty($this->google2fa_secret);
    }

    /**
     * Generate 2FA secret.
     */
    public function generate2FASecret(): string
    {
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        $this->google2fa_secret = $secret;
        $this->save();

        return $secret;
    }

    /**
     * Get QR code URL for 2FA.
     */
    public function get2FAQRCode(): string
    {
        $google2fa = app('pragmarx.google2fa');

        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->google2fa_secret
        );
    }

    /**
     * Verify 2FA code.
     */
    public function verify2FACode(string $code): bool
    {
        $google2fa = app('pragmarx.google2fa');

        return $google2fa->verifyKey($this->google2fa_secret, $code);
    }

    /**
     * Enable 2FA.
     */
    public function enable2FA(): void
    {
        $this->google2fa_enabled = true;
        $this->save();
    }

    /**
     * Disable 2FA.
     */
    public function disable2FA(): void
    {
        $this->google2fa_enabled = false;
        $this->google2fa_secret = null;
        $this->save();
    }
}
