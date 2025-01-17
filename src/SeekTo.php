<?php

declare(strict_types=1);

namespace Thesis\Cursor;

/**
 * @api
 */
interface SeekTo
{
    /**
     * @throws \OutOfBoundsException
     */
    public function seek(Seek $seek): void;
}
