<?php

declare(strict_types=1);

use Liberu\Cms\Content\Support\HtmlSanitizer;

it('strips script tags while keeping surrounding content', function (): void {
    $result = (new HtmlSanitizer)->sanitize('<p>Hello</p><script>alert(1)</script>');

    expect($result)->toContain('Hello')
        ->not->toContain('<script')
        ->not->toContain('alert(1)');
});

it('keeps safe formatting elements', function (): void {
    $result = (new HtmlSanitizer)->sanitize('<p>A <strong>bold</strong> <a href="/about">link</a></p>');

    expect($result)->toContain('<strong>')
        ->toContain('bold')
        ->toContain('href="/about"');
});

it('strips inline event handler attributes', function (): void {
    $result = (new HtmlSanitizer)->sanitize('<p onclick="steal()">Hi</p>');

    expect($result)->toContain('Hi')
        ->not->toContain('onclick');
});

it('strips the javascript url scheme from links', function (): void {
    $result = (new HtmlSanitizer)->sanitize('<a href="javascript:alert(1)">x</a>');

    expect($result)->not->toContain('javascript:');
});

it('strips iframes', function (): void {
    $result = (new HtmlSanitizer)->sanitize('<iframe src="https://evil.test"></iframe>');

    expect($result)->not->toContain('<iframe');
});

it('returns an empty string for null or empty input', function (): void {
    $sanitizer = new HtmlSanitizer;

    expect($sanitizer->sanitize(null))->toBe('')
        ->and($sanitizer->sanitize(''))->toBe('');
});

it('resolves as a singleton from the container', function (): void {
    expect(app(HtmlSanitizer::class))->toBe(app(HtmlSanitizer::class));
});
