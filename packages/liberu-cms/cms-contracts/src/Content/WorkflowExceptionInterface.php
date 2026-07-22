<?php

declare(strict_types=1);

namespace Liberu\Cms\Contracts\Content;

use Throwable;

/**
 * Marks an illegal editorial state transition, so callers can catch a stable
 * contract type rather than a concrete class.
 */
interface WorkflowExceptionInterface extends Throwable {}
