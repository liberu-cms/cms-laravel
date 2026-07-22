<?php

declare(strict_types=1);

namespace Liberu\Cms\ContentTypes\Fields;

/**
 * The primitive field kinds a custom content type's schema can declare.
 */
enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case RichText = 'richtext';
    case Number = 'number';
    case Boolean = 'boolean';
    case Date = 'date';
    case Select = 'select';
    case Media = 'media';
}
