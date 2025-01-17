<?php

declare(strict_types=1);

namespace Thesis\Cursor;

/**
 * @api
 */
interface WriteAt
{
    /**
     * @param non-negative-int $offset
     * @param callable(static): void $write
     */
    public function writeAt(int $offset, callable $write): void;
}
