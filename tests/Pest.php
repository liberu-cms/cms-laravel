<?php

use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Liberu\Cms\Core\CmsCoreServiceProvider;
use Liberu\Cms\Hello\HelloServiceProvider;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

/**
 * Boot the CMS kernel and the Hello module inside a fresh, bare Laravel
 * application that has none of this project's feature providers (Jetstream,
 * Filament, Socialstream, …). This is the embeddability harness: if the CMS
 * boots and serves here, it can be embedded inside any Laravel host.
 *
 * The facade application is repointed to the bare app only while its providers
 * boot, so module routes register into the bare app's router, then restored.
 *
 * @param  array<int, string>  $disabledModules
 */
function makeBareCmsApp(array $disabledModules = []): Application
{
    $original = Facade::getFacadeApplication();

    $app = new Application(sys_get_temp_dir().'/cms-bare-'.uniqid());
    $app->instance('files', new Filesystem);
    $app->instance('config', new Repository([
        'cms' => [
            'modules_enabled_by_default' => true,
            'disabled_modules' => $disabledModules,
        ],
    ]));

    $capsule = new Capsule;
    $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $app->instance(ConnectionResolverInterface::class, $capsule->getDatabaseManager());

    Facade::clearResolvedInstances();
    Facade::setFacadeApplication($app);

    try {
        $app->register(CmsCoreServiceProvider::class);
        $app->register(HelloServiceProvider::class);
        $app->boot();
    } finally {
        Facade::setFacadeApplication($original);
        Facade::clearResolvedInstances();
    }

    $app['router']->getRoutes()->refreshNameLookups();

    return $app;
}
