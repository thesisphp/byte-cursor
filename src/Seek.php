<?php

declare(strict_types=1);

namespace Thesis\Cursor;

/**
 * @api
 */
final class Seek
{
    /**
     * @param non-negative-int $position
     */
    public static function start(int $position): self
    {
        return new self(SeekType::start, $position);
    }

    public static function current(int $position): self
    {
        return new self(SeekType::current, $position);
    }

    public static function end(int $position): self
    {
        return new self(SeekType::end, $position);
    }

    private function __construct(
        public readonly SeekType $type,
        public readonly int $position,
    ) {}
}
