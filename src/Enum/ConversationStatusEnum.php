<?php

declare(strict_types=1);

namespace App\Enum;

enum ConversationStatusEnum : string {
    case DRAFT = "draft";
    case PUBLISHED = "published";
}

?>