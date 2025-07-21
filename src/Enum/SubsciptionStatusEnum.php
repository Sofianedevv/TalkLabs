<?php

declare(strict_types=1);

namespace App\Enum;

enum SubsciptionStatusEnum : string {
    case ACTIVE = "active";
    case EXPIRED = "expired";
    case CANCELED = "canceled";
}

?>