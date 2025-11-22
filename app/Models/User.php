<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasDefaultTenant, HasTenants, FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasProfilePhoto {
        HasProfilePhoto::profilePhotoUrl as getPhotoUrl;
    }
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use HasTeams;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];


    public function getHighestRole()
    {
        return $this->roles()->orderBy('level', 'desc')->first();
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isAdmin()
    {
        return $this->hasRole(Role::ADMIN) || $this->isSuperAdmin();
    }

    public function isEditor()
    {
        return $this->hasRole(Role::EDITOR) || $this->isAdmin();
    }

    public function isAuthor()
    {
        return $this->hasRole(Role::AUTHOR) || $this->isEditor();
    }

    public function canManageUser(User $user)
    {
        // Super admin can manage all users
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cannot manage users with higher role level
        $myHighestRole = $this->getHighestRole();
        $userHighestRole = $user->getHighestRole();

        if (!$myHighestRole || !$userHighestRole) {
            return false;
        }

        return $myHighestRole->level > $userHighestRole->level;
    }

    public function content()
    {
        return $this->hasMany(Content::class, 'author_id');
    }

    public function mediaUploads()
    {
        return $this->hasMany(MediaLibrary::class, 'uploaded_by');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getTenants(Panel $panel): array | Collection
    {
        return $this->teams;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === "admin") {
            return $this->hasRole(['admin', 'super_admin']);
        }

        if ($panel->getId() === "app") {
            return $this->hasAnyRole(['admin', 'editor', 'author', 'viewer']);
        }

        return true;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams->contains($tenant);
    }

    public function canAccessFilament(): bool
    {
        //        return $this->hasVerifiedEmail();
        return true;
    }

    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->latestTeam;
    }

    public function latestTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }
}