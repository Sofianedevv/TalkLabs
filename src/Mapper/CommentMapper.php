<?php


namespace App\Mapper;

use App\DTO\CommentDTO;
use App\DTO\CommentEditDTO;
use App\DTO\CommentReadDTO;
use App\Entity\Accounts;
use App\Entity\Comment;
use App\Enum\CommentStatusEnum;
use App\Repository\ConversationRepository;
use App\Repository\CommentRepository;

class CommentMapper {


    private ConversationRepository $conversationRepository;
    private CommentRepository $commentRepository;


    public function __construct(CommentRepository $commentRepository, ConversationRepository $conversationRepository) {
        $this->commentRepository = $commentRepository;
        $this->conversationRepository = $conversationRepository;
    }
 
    public function dtoToComment(CommentDTO $dto, Accounts $publisher, ?array $childsComment ) : Comment {
        $comment = new Comment();
        $comment->setContent($dto->getContent());
        $comment->setCreatedAt(new \DateTimeImmutable());
        if($dto->getConversationId() != null) {
            $conversation = $this->conversationRepository->findPublicConversationById($dto->getConversationId());
            $comment->setConversation($conversation);
        }
        $comment->setStatus(CommentStatusEnum::from($dto->getStatus()));
        $comment->setPublisher($publisher);
        if($dto->getParentCommentId() != null) {
            $parentComment = $this->commentRepository->find($dto->getParentCommentId());
            $comment->setParentComment($parentComment);
        }
        return $comment;
    }

     
    public function editdtoToComment($dto, Comment $comment) : Comment {
        $comment->setContent($dto->getContent());
        $comment->setStatus(CommentStatusEnum::VALIDATED);
        return $comment;
    }

    public function commentToEditDTO(Comment $comment) : CommentEditDTO {
        $dto = new CommentEditDTO();
        $dto->setId($comment->getId());
        $dto->setContent($comment->getContent());
        $dto->setStatus($comment->getStatus());
        return $dto;
    }

        public function commentToReadDTO(Comment $comment ) : CommentReadDTO {
        $dto = new CommentReadDTO();
        $dto->setId($comment->getId());
        $dto->setContent($comment->getContent());
        $dto->setCreatedAt($comment->getCreatedAt()->format('Y-m-d H:i:s'));
        $dto->setConversationId($comment->getConversation()->getId());
        $dto->setStatus($comment->getStatus()->value);
        $dto->setPublisher([
            'id' => $comment->getPublisher()->getId(),
            'name' => $comment->getPublisher()->getUsername(),
            'avatarUrl' => $comment->getPublisher()->getAvatarUrl(),
            'username' => $comment->getPublisher()->getusername(),
        ]);
        if ($comment->getParentComment()) {
            $dto->setParentCommentId($comment->getParentComment()->getId());
        }
        $childDTO = [];
        foreach($comment->getChildComments() as $child) {

        $childDTO[] = $this->childComment($child);
    }
    $dto->setChildComments($childDTO);
        return $dto;
    }
     private function childComment(Comment $child): CommentReadDTO
    {
        $dto = new CommentReadDTO();
        $dto->setId($child->getId());
        $dto->setContent($child->getContent());
        $dto->setCreatedAt($child->getCreatedAt()->format('Y-m-d H:i:s'));
        $dto->setStatus($child->getStatus()->value);
        $dto->setPublisher([
        'id' => $child->getPublisher()->getId(),
        'name' => $child->getPublisher()->getUsername(),
        'avatarUrl' => $child->getPublisher()->getAvatarUrl(),
    ]);

        if ($child->getParentComment()) {
            $dto->setParentCommentId($child->getParentComment()->getId());
        }

        $childDTOs = [];
        foreach ($child->getChildComments() as $grandChild) {
            $childDTOs[] = $this->childComment($grandChild);
        }
        $dto->setChildComments($childDTOs);

        return $dto;
    }




}