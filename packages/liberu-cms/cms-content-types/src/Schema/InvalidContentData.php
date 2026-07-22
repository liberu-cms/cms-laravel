<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes\Schema;

use Liberu\Cms\ContentTypes\Fields\FieldType;
use RuntimeException;

final class InvalidContentData extends RuntimeException
{
    public static function missingRequired(string $field): self
    {
        return new self("The field [{$field}] is required.");
    }

    public static function wrongType(string $field, FieldType $type): self
    {
        return new self("The field [{$field}] must be of type [{$type->value}].");
    }
}
