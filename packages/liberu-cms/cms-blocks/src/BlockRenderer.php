<?php

declare(strict_types=1);

namespace Liberu\Cms\Blocks;

use Liberu\Cms\Contracts\Block\BlockRendererInterface;
use Liberu\Cms\Contracts\Block\BlockTypeInterface;

/**
 * Recursively renders a block tree. An unknown block type renders to an empty
 * string rather than throwing, so a removed block type never breaks a page.
 */
final readonly class BlockRenderer implements BlockRendererInterface
{
    public function __construct(private BlockTypeRegistry $registry) {}

    public function render(array $block): string
    {
        $key = is_string($block['type'] ?? null) ? $block['type'] : '';
        $type = $this->registry->get($key);

        if (! $type instanceof BlockTypeInterface) {
            return '';
        }

        $data = is_array($block['data'] ?? null) ? $block['data'] : [];
        $children = is_array($block['children'] ?? null) ? $block['children'] : [];

        return $type->render($data, $this->renderMany($children));
    }

    public function renderMany(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            if (is_array($block)) {
                $html .= $this->render($block);
            }
        }

        return $html;
    }
}
