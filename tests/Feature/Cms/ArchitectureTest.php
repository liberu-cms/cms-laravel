<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * Enforces the §6 dependency direction:
 *
 *   host ─▶ modules ─▶ cms-core ─▶ cms-contracts
 *
 * Arrows never point sideways (module → sibling module) or backward
 * (core → module, contracts → anything). Communication across modules happens
 * only through contracts and events, so a module's source may reference the
 * Contracts and Core namespaces (and its own), never another module's.
 */
function cmsPackageNamespaces(): array
{
    $packages = [];

    foreach (glob(base_path('packages/liberu-cms/*'), GLOB_ONLYDIR) as $dir) {
        $composer = json_decode(file_get_contents("{$dir}/composer.json"), true);
        $roots = array_keys($composer['autoload']['psr-4'] ?? []);

        foreach ($roots as $root) {
            $packages[rtrim($root, '\\')] = basename($dir);
        }
    }

    return $packages;
}

/**
 * @return array<int, string> Fully-qualified Liberu\Cms imports in the file.
 */
function cmsImports(string $file): array
{
    preg_match_all('/^\s*use\s+(Liberu\\\\Cms\\\\[A-Za-z0-9_\\\\]+)/m', file_get_contents($file), $matches);

    return $matches[1] ?? [];
}

function cmsRootOf(string $fqcn, array $namespaces): ?string
{
    $best = null;

    foreach (array_keys($namespaces) as $root) {
        if (str_starts_with($fqcn, $root.'\\') && ($best === null || strlen($root) > strlen($best))) {
            $best = $root;
        }
    }

    return $best;
}

it('keeps module dependencies pointing only inward (no sideways or backward imports)', function (): void {
    $namespaces = cmsPackageNamespaces();
    $contracts = 'Liberu\Cms\Contracts';
    $core = 'Liberu\Cms\Core';
    $content = 'Liberu\Cms\Content';
    $violations = [];

    foreach ($namespaces as $ownRoot => $package) {
        $src = base_path("packages/liberu-cms/{$package}/src");

        if (! is_dir($src)) {
            continue;
        }

        // Foundation packages (contracts, core, content) may be imported by any
        // module; foundations themselves only point further inward.
        $allowed = match (true) {
            $ownRoot === $contracts => [$contracts],
            $ownRoot === $core => [$contracts, $core],
            $ownRoot === $content => [$contracts, $content],
            default => [$contracts, $core, $content, $ownRoot],
        };

        foreach (Finder::create()->files()->in($src)->name('*.php') as $file) {
            foreach (cmsImports($file->getRealPath()) as $import) {
                $importedRoot = cmsRootOf($import, $namespaces);

                if ($importedRoot !== null && ! in_array($importedRoot, $allowed, true)) {
                    $violations[] = "{$package} imports {$import}";
                }
            }
        }
    }

    expect($violations)->toBe([]);
});

it('never lets a module reach into the host application namespace', function (): void {
    $violations = [];

    foreach (cmsPackageNamespaces() as $package) {
        $src = base_path("packages/liberu-cms/{$package}/src");

        if (! is_dir($src)) {
            continue;
        }

        foreach (Finder::create()->files()->in($src)->name('*.php') as $file) {
            if (preg_match('/^\s*use\s+App\\\\/m', (string) file_get_contents($file->getRealPath()))) {
                $violations[] = "{$package} imports the host App\\ namespace";
            }
        }
    }

    expect($violations)->toBe([]);
});

it('discovers the foundational CMS packages', function (): void {
    expect(cmsPackageNamespaces())
        ->toHaveKeys([
            'Liberu\Cms\Contracts',
            'Liberu\Cms\Core',
            'Liberu\Cms\Hello',
            'Liberu\Cms\Users',
        ]);
});
