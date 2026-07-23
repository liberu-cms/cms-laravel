<?php

declare(strict_types=1);

use Liberu\Cms\Contracts\Widget\WidgetArea;
use Liberu\Cms\Contracts\Widget\WidgetInterface;
use Liberu\Cms\Widgets\WidgetRegistry;
use Liberu\Cms\Widgets\Widgets\SearchWidget;
use Liberu\Cms\Widgets\Widgets\SocialLinksWidget;

beforeEach(function (): void {
    $this->registry = app(WidgetRegistry::class);
});

it('registers the prebuilt search widget in the sidebar', function (): void {
    $sidebar = $this->registry->forArea(WidgetArea::Sidebar);

    expect(array_map(fn ($w): string => $w->key(), $sidebar))->toContain('search');
});

it('returns widgets for an area ordered ascending', function (): void {
    $this->registry->register(new class implements WidgetInterface
    {
        public function key(): string
        {
            return 'late';
        }

        public function title(): string
        {
            return 'Late';
        }

        public function area(): WidgetArea
        {
            return WidgetArea::Dashboard;
        }

        public function order(): int
        {
            return 10;
        }

        public function render(): string
        {
            return 'LATE';
        }
    });
    $this->registry->register(new class implements WidgetInterface
    {
        public function key(): string
        {
            return 'early';
        }

        public function title(): string
        {
            return 'Early';
        }

        public function area(): WidgetArea
        {
            return WidgetArea::Dashboard;
        }

        public function order(): int
        {
            return 1;
        }

        public function render(): string
        {
            return 'EARLY';
        }
    });

    expect($this->registry->renderArea(WidgetArea::Dashboard))->toBe('EARLYLATE');
});

it('escapes output in the social links widget', function (): void {
    $this->registry->register(new SocialLinksWidget(['Evil' => '"><script>alert(1)</script>']));

    $html = $this->registry->renderArea(WidgetArea::Footer);

    expect($html)->toContain('cms-widget-social')
        ->and($html)->not->toContain('<script>');
});

it('renders the search widget form', function (): void {
    expect((new SearchWidget)->render())->toContain('role="search"');
});

it('filters widgets strictly by area', function (): void {
    $this->registry->register(new SocialLinksWidget(['GitHub' => 'https://github.com']));

    $footerKeys = array_map(fn ($w): string => $w->key(), $this->registry->forArea(WidgetArea::Footer));
    $sidebarKeys = array_map(fn ($w): string => $w->key(), $this->registry->forArea(WidgetArea::Sidebar));

    expect($footerKeys)->toContain('social-links')
        ->and($footerKeys)->not->toContain('search')
        ->and($sidebarKeys)->toContain('search')
        ->and($sidebarKeys)->not->toContain('social-links');
});
