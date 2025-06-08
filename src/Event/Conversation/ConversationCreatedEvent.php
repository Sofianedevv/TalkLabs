<?php

namespace App\Event\Conversation;

use App\Entity\Conversation;
use Symfony\Contracts\EventDispatcher\Event;

class ConversationCreatedEvent extends Event {

    public const NAME = 'conversation.created';

    private Conversation $conversation;

    public function __construct(Conversation $conversation) {
        $this->conversation = $conversation;
    }
    
    public function getConversation(): Conversation {
        return $this->conversation;
    }

}