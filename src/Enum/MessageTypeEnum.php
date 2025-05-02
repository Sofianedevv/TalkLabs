<?php

declare(strict_types=1);

namespace App\Enum;

enum MessageTypeEnum : string {
    case TEXT = "text";
    case IMAGE = "image";
    case VIDEO = "video";
}

?>