# Cursor

## Installation

```shell
composer require thesis/cursor
```

## Basic usage

```php
<?php

declare(strict_types=1);

use Thesis\Cursor\Cursor;
use Thesis\Cursor\Seek;

require_once __DIR__.'/vendor/autoload.php';

/**
 * @param list<non-empty-string> $items
 */
function writeArray(Cursor $cursor, array $items): void
{
    // Remember the current position to write array len later.
    $pos = $cursor->position();

    // Reserve bytes for array len.
    $cursor->writeUint32(0);

    foreach ($items as $item) {
        $cursor->writeUint16(\strlen($item));
        $cursor->write($item);
    }

    // Now we can write to this position the actual length of the array in bytes by subtracting the position ($pos) and the position size (4 bytes) from the cursor length.
    // At the end, the cursor position will reset to the end on its own.
    $cursor->writeAt($pos, static function (Cursor $cursor) use ($pos): void {
        $cursor->writeUint32(\count($cursor) - $pos - 4);
    });
}

/**
 * @param Cursor $cursor
 * @return list<non-empty-string>
 */
function readArray(Cursor $cursor): array
{
    $size = $cursor->readUint32();

    $items = [];
    while ($size > 0) {
        $items[] = $v = $cursor->read($cursor->readUint16());
        $size -= \strlen($v) + 2;
    }

    return $items;
}

$cursor = Cursor::new();
writeArray($cursor, ['x', 'y']);
$items = readArray($cursor);
var_dump($items); // [x, y]

// You must reset the position before you can use the cursor again.
$cursor->reset(); // by resetting it.
$cursor->seek(Seek::start(0)); // or seeking.
```
