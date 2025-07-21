<?php
namespace App\Event\Favorite;

use App\Entity\Conversation;
use App\Entity\Favorite;
use Symfony\Contracts\EventDispatcher\Event;

class FavoriteCreatedEvent extends Event
{
    public const NAME = 'favorite.created';

    private Favorite $favorite;
    private Conversation $conversation;

    public function __construct(Favorite $favorite, Conversation $conversation)
    {
        $this->favorite = $favorite;
        $this->conversation = $conversation;
    }


    public function getFavorite(): Favorite
    {
        return $this->favorite;
    }
    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

}