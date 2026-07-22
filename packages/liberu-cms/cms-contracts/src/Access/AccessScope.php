<?php

declare(strict_types=1);

namespace Liberu\Cms\Contracts\Access;

/**
 * The layer a permission governs, per Part B §7: content-, media-, and
 * module-level permissions. Lets the platform group and reason about
 * permissions by what they protect.
 */
enum AccessScope: string
{
    case Content = 'content';
    case Media = 'media';
    case Module = 'module';
}
