<?php

declare(strict_types=1);

use Liberu\Cms\Blocks\BlockTypeRegistry;
use Liberu\Cms\Blocks\Types\AbstractBlockType;
use Liberu\Cms\Contracts\Block\BlockRendererInterface;

beforeEach(function (): void {
    $this->renderer = app(BlockRendererInterface::class);
});

it('registers the prebuilt block types', function (): void {
    $registry = app(BlockTypeRegistry::class);

    expect(array_keys($registry->all()))
        ->toContain('text', 'heading', 'image', 'code', 'cta', 'columns');
});

it('renders a text block and escapes its content (XSS-safe)', function (): void {
    $html = $this->renderer->render([
        'type' => 'text',
        'data' => ['text' => '<script>alert(1)</script>'],
    ]);

    expect($html)->toContain('cms-block-text')
        ->and($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;');
});

it('renders a heading with a clamped level', function (): void {
    expect($this->renderer->render(['type' => 'heading', 'data' => ['level' => 9, 'text' => 'Hi']]))
        ->toContain('<h6 class="cms-block-heading">Hi</h6>');
});

it('escapes attributes in image and cta blocks', function (): void {
    $image = $this->renderer->render(['type' => 'image', 'data' => ['src' => 'x" onerror="alert(1)', 'alt' => 'a']]);
    $cta = $this->renderer->render(['type' => 'cta', 'data' => ['url' => '/go', 'label' => 'Go']]);

    expect($image)->not->toContain('onerror="alert(1)"')
        ->and($cta)->toContain('href="/go"')
        ->and($cta)->toContain('>Go</a>');
});

it('renders nested children inside a columns block', function (): void {
    $html = $this->renderer->render([
        'type' => 'columns',
        'data' => ['columns' => 2],
        'children' => [
            ['type' => 'heading', 'data' => ['level' => 2, 'text' => 'Title']],
            ['type' => 'text', 'data' => ['text' => 'Body']],
        ],
    ]);

    expect($html)->toContain('cms-columns-2')
        ->and($html)->toContain('<h2 class="cms-block-heading">Title</h2>')
        ->and($html)->toContain('Body');
});

it('renders many blocks in order', function (): void {
    $html = $this->renderer->renderMany([
        ['type' => 'heading', 'data' => ['text' => 'One']],
        ['type' => 'text', 'data' => ['text' => 'Two']],
    ]);

    expect(strpos($html, 'One'))->toBeLessThan(strpos($html, 'Two'));
});

it('renders an unknown block type as empty', function (): void {
    expect($this->renderer->render(['type' => 'nope', 'data' => []]))->toBe('');
});

it('supports registering a custom block type', function (): void {
    app(BlockTypeRegistry::class)->register(new class extends AbstractBlockType
    {
        public function key(): string
        {
            return 'quote';
        }

        public function render(array $data, string $childrenHtml = ''): string
        {
            return '<blockquote>'.$this->e($this->str($data, 'text')).'</blockquote>';
        }
    });

    expect($this->renderer->render(['type' => 'quote', 'data' => ['text' => 'Wisdom']]))
        ->toBe('<blockquote>Wisdom</blockquote>');
});
