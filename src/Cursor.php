<?php

declare(strict_types=1);

namespace Thesis\ByteCursor;

use Thesis\ByteOrder\ReadFrom;
use Thesis\ByteOrder\WriteTo;
use Thesis\ByteReader\UnexpectedEof;
use Thesis\ByteWriter\Writer;
use Thesis\Endian\endian;

/**
 * @api
 */
final class Cursor implements
    ReadFrom,
    WriteTo,
    SeekTo,
    WriteAt,
    Resettable,
    Peekable,
    \Countable,
    \Stringable
{
    public static function empty(): self
    {
        return new self();
    }

    public static function new(string $buffer = ''): self
    {
        return new self($buffer);
    }

    /** @var non-negative-int */
    private int $position;

    private function __construct(
        private string $buffer = '',
    ) {
        $this->position = \strlen($this->buffer);
    }

    public function readInt8(endian $endian = endian::network): int
    {
        return $endian->unpackInt8($this->read(1));
    }

    public function readUint8(endian $endian = endian::network): int
    {
        return $endian->unpackUint8($this->read(1));
    }

    public function readInt16(endian $endian = endian::network): int
    {
        return $endian->unpackInt16($this->read(2));
    }

    public function readUint16(endian $endian = endian::network): int
    {
        return $endian->unpackUint16($this->read(2));
    }

    public function readInt32(endian $endian = endian::network): int
    {
        return $endian->unpackInt32($this->read(4));
    }

    public function readUint32(endian $endian = endian::network): int
    {
        return $endian->unpackUint32($this->read(4));
    }

    public function readInt64(endian $endian = endian::network): int
    {
        return $endian->unpackInt64($this->read(8));
    }

    public function readUint64(endian $endian = endian::network): int
    {
        return $endian->unpackUint64($this->read(8));
    }

    public function readFloat(endian $endian = endian::network): float
    {
        return $endian->unpackFloat($this->read(4));
    }

    public function readDouble(endian $endian = endian::network): float
    {
        return $endian->unpackDouble($this->read(8));
    }

    public function read(int $limit): string
    {
        $value = $this->peek($limit);
        $this->buffer = substr($this->buffer, $limit);

        return $value;
    }

    public function peek(int $limit): string
    {
        if ($limit > \strlen($this->buffer)) {
            throw new UnexpectedEof(\sprintf('Not enough data to read "%d" bytes of data.', $limit));
        }

        return substr($this->buffer, 0, $limit);
    }

    public function writeInt8(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packInt8(...), $v);
    }

    public function writeUint8(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packUint8(...), $v);
    }

    public function writeInt16(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packInt16(...), $v);
    }

    public function writeUint16(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packUint16(...), $v);
    }

    public function writeInt32(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packInt32(...), $v);
    }

    public function writeUint32(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packUint32(...), $v);
    }

    public function writeInt64(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packInt64(...), $v);
    }

    public function writeUint64(int $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packUint64(...), $v);
    }

    public function writeFloat(float $v, endian $endian = endian::network): WriteTo
    {
        return $this->doWrite($endian->packFloat(...), $v);
    }

    public function writeDouble(float $v, endian $endian = endian::network): self
    {
        return $this->doWrite($endian->packDouble(...), $v);
    }

    public function write(string $bytes): void
    {
        if (\strlen($this->buffer) === $this->position) {
            $this->buffer .= $bytes;
            $this->position += \strlen($bytes);
        } else {
            for ($i = 0; $i < \strlen($bytes); ++$i) {
                $this->buffer[$this->position++] = $bytes[$i];
            }
        }
    }

    public function writeAt(int $offset, callable $write): void
    {
        $current = $this->position;
        $this->position = $offset;
        $write($this);
        $this->position = $current;
    }

    public function seek(Seek $seek): void
    {
        [$position, $offset] = match ($seek->type) {
            SeekType::start => [$seek->position, 0],
            SeekType::current => [$this->position, $seek->position],
            SeekType::end => [\strlen($this->buffer), $seek->position],
        };

        $position += $offset;
        if ($position < 0) {
            throw new \OutOfBoundsException(\sprintf('Impossible to seek to a negative position "%d".', $position));
        }

        $this->position = $position;
    }

    public function reset(): string
    {
        [$buffer, $this->buffer, $this->position] = [$this->buffer, '', 0];

        return $buffer;
    }

    public function count(): int
    {
        return \strlen($this->buffer);
    }

    public function __toString(): string
    {
        return $this->buffer;
    }

    /**
     * @param callable(non-empty-string): non-empty-string $fix
     * @param ?positive-int $len
     */
    public function amend(callable $fix, ?int $len = null): void
    {
        $len ??= \strlen($this->buffer);
        if ($len > \strlen($this->buffer)) {
            throw new \UnexpectedValueException(\sprintf('Len of amend "%d" is longer than available buffer.', $len));
        }

        $v = substr($this->buffer, $this->position, $len);
        if ($v !== '') {
            $v = $fix($v);

            $this->buffer = substr($this->buffer, 0, $this->position);
            $this->buffer .= $v;
            $this->buffer .= substr($this->buffer, $this->position + \strlen($v), \strlen($this->buffer));
            $this->position = \strlen($this->buffer);
        }
    }

    /**
     * @return non-negative-int
     */
    public function position(): int
    {
        return $this->position;
    }

    /**
     * Resets the in-memory buffer to the Writer.
     */
    public function writeTo(Writer $writer): void
    {
        if (($bytes = $this->reset()) !== '') {
            $writer->write($bytes);
        }
    }

    /**
     * @template T
     * @param callable(T): non-empty-string $write
     * @param T $v
     */
    private function doWrite(callable $write, mixed $v): self
    {
        $this->write($write($v));

        return $this;
    }
}
