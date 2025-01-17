<?php

declare(strict_types=1);

namespace Thesis\Cursor;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cursor::class)]
#[CoversClass(Seek::class)]
#[CoversClass(SeekType::class)]
final class CursorTest extends TestCase
{
    /**
     * @param non-empty-string $v
     */
    #[TestWith([SeekType::start, 1, 1, 'x', 'txst'])]
    #[TestWith([SeekType::current, 1, 2, 'x', 'text'])]
    #[TestWith([SeekType::end, 1, 5, 'x', 'test x'])]
    public function testSeek(SeekType $seek, int $offset, int $position, string $v, string $expected): void
    {
        $cursor = Cursor::new('test');
        $cursor->seek(Seek::start(1));
        $cursor->seek($seek->to($offset));
        self::assertSame($position, $cursor->position());
        $cursor->write($v);
        self::assertSame($expected, $cursor->reset());
    }

    public function testPeek(): void
    {
        $cursor = Cursor::new('test');
        self::assertSame($cursor->peek(2), $cursor->peek(2));
        self::assertCount(4, $cursor);
    }

    public function testRead(): void
    {
        $cursor = Cursor::new('test');
        self::assertSame('test', $cursor->read(4));
        self::assertCount(0, $cursor);
    }

    public function testWriteAt(): void
    {
        $cursor = Cursor::new('test');
        $cursor->writeAt(1, static function (Cursor $cursor): void {
            $cursor->write('x');
        });
        self::assertSame('txst', $cursor->reset());
    }

    public function testReset(): void
    {
        $cursor = Cursor::new('test');
        self::assertSame('test', $cursor->reset());
        self::assertCount(0, $cursor);
        self::assertSame(0, $cursor->position());
    }
}
