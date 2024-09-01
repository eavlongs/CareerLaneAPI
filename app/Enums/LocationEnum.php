<?php

namespace App\Enums;

enum LocationEnum: int
{
    case REMOTE = 0;
    case ONSITE = 1;
    case HYBRID = 2;

    public static function toArray(): array
    {
        return [
            self::REMOTE,
            self::ONSITE,
            self::HYBRID
        ];
    }
};
