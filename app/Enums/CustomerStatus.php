<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case PROSPECT = 'PROSPECT';
    case PENDING = 'PENDING';
    case ACTIVE = 'ACTIVE';
    case GRACE_PERIOD = 'GRACE_PERIOD';
    case OVERDUE = 'OVERDUE';
    case SUSPENDED = 'SUSPENDED';
    case CANCELLED = 'CANCELLED';
    case ARCHIVED = 'ARCHIVED';

    public function label(): string
    {
        return match($this) {
            self::PROSPECT => 'Prospect',
            self::PENDING => 'Pending Verification',
            self::ACTIVE => 'Active Subscriber',
            self::GRACE_PERIOD => 'Grace Period',
            self::OVERDUE => 'Overdue',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
            self::ARCHIVED => 'Archived',
        };
    }
}
