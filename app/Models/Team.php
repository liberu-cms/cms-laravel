<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Jetstream\Models\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    protected static array $defaultRoles = [
        'super_admin' => [],
        'editor'      => [],
        'viewer'      => [],
    ];
    
    protected static function booted()
    {
        static::created(function (Team $team) {
            foreach (self::$defaultRoles as $roleName => $permissions) {
                $role = Role::create([
                    'name' => $roleName,
                    'team_id' => $team->id,
                    'guard_name' => 'web',
                ]);
        
                $role->syncPermissions($permissions);
            }
        
            // Assign super_admin to the creator if there is one
            if (auth()->check()) {
                $user = auth()->user();
                $user->teams()->syncWithoutDetaching([$team->id]);

                $oldteam = getPermissionsTeamId();
                setPermissionsTeamId($team->id);
        
                $superAdminRole = Role::where('name', 'super_admin')
                    ->where('team_id', $team->id)
                    ->first();
        
                $user->assignRole($superAdminRole);

                setPermissionsTeamId($oldteam);
            }
        });
        
    }
}
