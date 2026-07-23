<?php

declare(strict_types=1);

namespace Liberu\Cms\Admin\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Cms\Admin\Filament\Pages\ModuleManagement;

/**
 * Registers the CMS admin surfaces onto a Filament panel. A host opts in with
 * `->plugin(CmsAdminPlugin::make())`; removing that one line (or the package)
 * takes the whole admin surface with it, per the removable-module rule.
 */
final class CmsAdminPlugin implements Plugin
{
    public static function make(): static
    {
        return app(self::class);
    }

    public function getId(): string
    {
        return 'cms-admin';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ModuleManagement::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
