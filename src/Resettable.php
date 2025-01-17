<?php

declare(strict_types=1);

namespace Thesis\Cursor;

/**
 * @api
 */
interface Resettable
{
    public function reset(): string;
}
