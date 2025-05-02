<?php

declare(strict_types=1);

namespace App\Enum;

enum MessageSenderEnum : string {
    case USER = "user";
    case INTERLOCUTOR = "interlocutor";
}

?>