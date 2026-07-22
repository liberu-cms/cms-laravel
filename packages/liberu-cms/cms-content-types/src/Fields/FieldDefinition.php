<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes\Fields;

/**
 * One field in a content type's schema.
 */
final readonly class FieldDefinition
{
    /**
     * @param  array<int, string>  $options  Choices for a Select field.
     */
    public function __construct(
        public string $name,
        public string $label,
        public FieldType $type,
        public bool $required = false,
        public array $options = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? null;
        $options = $data['options'] ?? null;

        return new self(
            name: is_string($data['name'] ?? null) ? $data['name'] : '',
            label: is_string($data['label'] ?? null) ? $data['label'] : '',
            type: FieldType::tryFrom(is_string($type) ? $type : 'text') ?? FieldType::Text,
            required: (bool) ($data['required'] ?? false),
            options: is_array($options) ? array_values(array_filter($options, is_string(...))) : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type->value,
            'required' => $this->required,
            'options' => $this->options,
        ];
    }
}
