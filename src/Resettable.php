<?php

declare(strict_types=1);

namespace Thesis\ByteCursor;

/**
 * @api
 */
interface Resettable
{
    public function reset(): string;
}
