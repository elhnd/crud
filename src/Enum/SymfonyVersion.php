<?php

namespace App\Enum;

enum SymfonyVersion: string
{
    case V7_4_8_0 = '7.4/8.0';

    public function getLabel(): string
    {
        return match ($this) {
            self::V7_4_8_0 => 'Symfony 7.4 / 8.0',
        };
    }

    public function getBadgeColor(): string
    {
        return match ($this) {
            self::V7_4_8_0 => 'cyan',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::V7_4_8_0 => 'fa-bolt',
        };
    }
}
