<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case ACTIVE = 'ACTIVE';
    case ON_LEAVE = 'ON_LEAVE';
    case TERMINATED = 'TERMINATED';
    case RESIGNED = 'RESIGNED';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::ON_LEAVE => 'On Leave',
            self::TERMINATED => 'Terminated',
            self::RESIGNED => 'Resigned',
        };
    }
}
