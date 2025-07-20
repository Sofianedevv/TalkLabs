<?php

namespace App\Service;

use App\DTO\CommentDTO;
use App\DTO\CommentEditDTO;
use App\DTO\CommentReadDTO;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ConversationRepository;
use App\Enum\CommentStatusEnum;
use App\Mapper\CommentMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;



class CommentService
{


    private CommentRepository $commentRepository;
    private ConversationRepository $conversationRepository;
    private CommentMapper $commentMapper;
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(CommentRepository $commentRepository, ConversationRepository $conversationRepository, CommentMapper $commentMapper, EntityManagerInterface $em, Security $security)
    {
        $this->commentRepository = $commentRepository;
        $this->conversationRepository = $conversationRepository;
        $this->commentMapper = $commentMapper;
        $this->em = $em;
        $this->security = $security;
    }

    public function addComment(CommentDTO $dto): CommentReadDTO
    {

        $publisher = $this->security->getUser();
        if (!$publisher) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $comment = $this->commentMapper->dtoToComment($dto, $publisher, null);
        $this->em->persist($comment);
        $this->em->flush();

        return $this->commentMapper->commentToReadDTO($comment);
    }

    public function getCommentsByConversation(int $conversationId): array
    {
        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation) {
            throw new \Exception("Conversation introuvable");
        }

        $comments = $this->commentRepository->findAllComment($conversation);

        $result = [];
        foreach ($comments as $comment) {
            if ($comment->getStatus()->value !== CommentStatusEnum::REJECTED) {
                $result[] = $this->commentMapper->commentToReadDTO($comment);
            }
        }

        return $result;
    }


    public function editComment($id, CommentEditDTO $dto): CommentReadDTO
    {
        $publisher = $this->security->getUser();
        if (!$publisher) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            throw new \Exception("Commentaire introuvable");
        }
        $updatedComment = $this->commentMapper->editDtoToComment($dto, $comment);
        $this->em->persist($updatedComment);
        $this->em->flush();

        return $this->commentMapper->commentToReadDTO($updatedComment);
    }

    public function deleteComment($id): void
    {
        $publisher = $this->security->getUser();
        if (!$publisher) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $comment = $this->commentRepository->find($id);
        if (!$comment) {
            throw new \Exception("Commentaire introuvable");
        }
        $this->em->remove($comment);
        $this->em->flush();
    }

    public function reportComment($commentId): void
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \Exception("Commentaire introuvable");
        }

        $comment->setStatus(CommentStatusEnum::PENDING);
        $this->em->persist($comment);
        $this->em->flush();
    }

    public function getPendingComments(): array
    {
        $comments = $this->commentRepository->findPendingComments();
        $commentDTOs = [];

        foreach ($comments as $comment) {
            $commentDTOs[] = $this->commentMapper->commentToReadDTO($comment);
        }

        return $commentDTOs;
    }




    public function validateComment($commentId): void
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \Exception("Commentaire introuvable");
        }

        $comment->setStatus(CommentStatusEnum::VALIDATED);
        $this->em->persist($comment);
        $this->em->flush();
    }

    public function rejectComment($commentId): void
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            throw new \Exception("Commentaire introuvable");
        }

        $comment->setStatus(CommentStatusEnum::REJECTED);
        $this->em->persist($comment);
        $this->em->flush();
    }
}
