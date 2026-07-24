<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Liberu\Cms\Contracts\Tenancy\TenantModelResolverInterface;

/**
 * Host binding for the CMS tenancy contract: the tenant is a Team, active only
 * when multi-tenancy (Spatie teams) is enabled. Lets CMS module models scope to
 * the current team without importing this class.
 */
final class FilamentTenantResolver implements TenantModelResolverInterface
{
    public function tenantModel(): ?string
    {
        return config('permission.teams') ? Team::class : null;
    }

    public function currentTenantId(): int|string|null
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Model ? $tenant->getKey() : null;
    }
}
