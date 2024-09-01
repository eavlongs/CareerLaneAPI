<?php

namespace App\Enums;

enum JobTypeEnum: int
{
    case FULL_TIME = 0;
    case PART_TIME = 1;
    case INTERNSHIP = 2;
    case CONTRACT = 3;
    case TEMPORARY = 4;

    public static function toArray(): array
    {
        return [
            self::FULL_TIME,
            self::PART_TIME,
            self::INTERNSHIP,
            self::CONTRACT,
            self::TEMPORARY
        ];
    }
}
