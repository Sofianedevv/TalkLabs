<?php

declare(strict_types=1);

namespace App\Enum;

enum ReportStatusEnum: string
{
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';
}
