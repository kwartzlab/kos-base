<?php

namespace Tests\Fakes;

use Illuminate\Support\Collection;

class Event
{
    public static int $callCount = 0;

    public static function get(): Collection
    {
        self::$callCount += 1;

        return collect();
    }
}
