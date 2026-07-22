<?php

namespace App\Traits;

use App\Models\Membership;
use App\Models\Role;
use App\Models\Team;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasTeams
{
    /**
     * Create a personal team for the user and make it their current tenant.
     *
     * Registration creates a user before it is authenticated, so the Team
     * model's created hook cannot attach them; this does it explicitly and
     * assigns the team's super_admin role within that team's permission scope.
     */
    public function createPersonalTeam(): Team
    {
        /** @var Team $team */
        $team = $this->ownedTeams()->create([
            'name' => Str::of($this->name)->explode(' ')->first().'\'s Team',
            'personal_team' => true,
        ]);

        $this->forceFill(['current_team_id' => $team->id])->save();
        $this->teams()->syncWithoutDetaching([$team->id]);

        $superAdmin = Role::query()
            ->where('name', 'super_admin')
            ->where('team_id', $team->id)
            ->first();

        if ($superAdmin !== null) {
            $previousTeamId = getPermissionsTeamId();
            setPermissionsTeamId($team->id);
            $this->assignRole($superAdmin);
            setPermissionsTeamId($previousTeamId);
        }

        return $team;
    }

    public function isCurrentTeam(Team $team): bool
    {
        return $team->id === $this->currentTeam->id;
    }

    public function currentTeam(): BelongsTo
    {
        if (is_null($this->current_team_id) && $this->id) {
            $this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function switchTeam(Team $team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill(['current_team_id' => $team->id])->save();
        $this->setRelation('currentTeam', $team);

        return true;
    }

    public function allTeams(): Collection
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->using(Membership::class)
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    public function personalTeam(): ?Team
    {
        return $this->ownedTeams->where('personal_team', true)->first();
    }

    public function ownsTeam(Team $team): bool
    {
        return $this->id == $team->{$this->getForeignKey()};
    }

    public function belongsToTeam(Team $team): bool
    {
        return $this->ownsTeam($team) || $this->teams->contains(fn ($t): bool => $t->id === $team->id);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->allTeams();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant instanceof Team && $this->belongsToTeam($tenant);
    }
}
