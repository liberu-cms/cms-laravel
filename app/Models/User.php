<?php

namespace App\Models;

use App\Traits\HasProfilePhoto;
use App\Traits\HasTeams;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\Permission\Traits\HasRoles;
use Stephenjude\FilamentTwoFactorAuthentication\TwoFactorAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasPasskeys, HasTenants, MustVerifyEmail
{
    use HasApiTokens, HasRoles, HasTeams {
        HasTeams::teams insteadof HasRoles;
        HasRoles::teams as permissionTeams;
    }
    use HasFactory;
    use HasProfilePhoto;
    use InteractsWithPasskeys;
    use Notifiable;
    use TwoFactorAuthenticatable;

    #[\Override]
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    #[\Override]
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    #[\Override]
    protected $appends = [
        'profile_photo_url',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
