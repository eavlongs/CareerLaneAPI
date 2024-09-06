<?php

namespace App\Enums;

enum ProviderEnum: int
{
    case GOOGLE = 0;
    case GITHUB = 1;
    case LINKEDIN = 2;
    case _LENGTH = 3;

    public static function toArray(): array
    {
        return [
            self::GOOGLE,
            self::GITHUB,
            self::LINKEDIN,
        ];
    }
};
