<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes\Schema;

use Liberu\Cms\ContentTypes\Fields\FieldType;
use Liberu\Cms\ContentTypes\Models\ContentType;

/**
 * Validates a content entry's data against its type's field schema: required
 * fields must be present, values must roughly match their declared type, and
 * fields not in the schema are dropped.
 */
final class SchemaValidator
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed> The data limited to the schema's fields.
     */
    public function validate(ContentType $type, array $data): array
    {
        $names = [];

        foreach ($type->fieldDefinitions() as $field) {
            $names[] = $field->name;
            $value = $data[$field->name] ?? null;

            if ($field->required && ($value === null || $value === '')) {
                throw InvalidContentData::missingRequired($field->name);
            }

            if ($value !== null && ! $this->matchesType($field->type, $value)) {
                throw InvalidContentData::wrongType($field->name, $field->type);
            }
        }

        return array_intersect_key($data, array_flip($names));
    }

    private function matchesType(FieldType $type, mixed $value): bool
    {
        return match ($type) {
            FieldType::Number => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
            FieldType::Boolean => is_bool($value),
            FieldType::Media => is_int($value),
            default => is_string($value),
        };
    }
}
