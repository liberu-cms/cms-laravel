<?php

declare(strict_types=1);

namespace Liberu\Cms\Contracts\Content;

/**
 * The editorial lifecycle a piece of content moves through (Part B §14):
 * Draft → Review → Published → Archived. Scheduling is expressed as the
 * Published state with a future publish date, so it needs no separate case.
 */
enum WorkflowState: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';
}
