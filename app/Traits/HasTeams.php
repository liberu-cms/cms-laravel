<?php

namespace App\Traits;

use App\Models\Membership;
use App\Models\Team;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasTeams
{
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
