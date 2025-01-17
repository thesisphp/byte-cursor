<?php

declare(strict_types=1);

namespace Thesis\ByteCursor;

/**
 * @api
 */
enum SeekType
{
    case start;
    case current;
    case end;

    public function to(int $position): Seek
    {
        return match ($this) {
            self::start => Seek::start(max(0, $position)),
            self::current => Seek::current($position),
            self::end => Seek::end($position),
        };
    }
}
