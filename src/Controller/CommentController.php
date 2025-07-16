<?php

namespace App\Controller;

use App\DTO\CommentDTO;
use App\DTO\CommentEditDTO;
use App\Entity\Comment;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\Voter\CommentVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'app_')]
final class CommentController extends AbstractController
{
    #[Route('/add-comment', name: 'add_comment')]
    public function addComment(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CommentService $commentService): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CommentDTO::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        try {
            $comment =  $commentService->addComment($dto);

            return $this->json([
                'comment' => $comment,
                'message' => 'Commentaire ajouté avec succès'
            ]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/conversations/{id}/comments', name: 'get_conversation_comments')]
    public function getCommentsByConversation(CommentService $commentService, $id): JsonResponse
    {
        try {
            $comments = $commentService->getCommentsByConversation($id);

            return $this->json($comments);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/edit-comment/{comment}', name: 'edit_comment')]
    #[IsGranted(CommentVoter::EDIT, subject: 'comment')]
    public function editComment(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CommentService $commentService, Comment $comment): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CommentEditDTO::class, 'json');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors]);
        }

        try {
            $comment = $commentService->editComment($comment->getId(), $dto);
            return $this->json([
                'comment' => $comment,
                'message' => 'Commentaire ajouté avec succès'
            ]);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/delete-comment/{comment}', name: 'delete_comment')]
    #[IsGranted(CommentVoter::DELETE, subject: 'comment')]
    public function deleteComment(
        CommentService $commentService,
        Comment $comment
    ): JsonResponse {
        try {
            $commentService->deleteComment($comment->getId());
            return $this->json(['message' => 'Conversation supprimé avec succès']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/report-comment/{commentId}', name: 'report_comment')]
    public function reportComment(CommentService $commentService, $commentId): JsonResponse
    {

        try {
            $commentService->reportComment($commentId);
            return $this->json(['message' => 'Vous avez signlez un message, un administrauteur va analyser votre demande'], 200);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/pending-comment', name: 'pending_comment')]
    #[IsGranted('ROLE_ADMIN')]
    public function getPendingCommentReport(CommentService $commentService): JsonResponse
    {

        try {
            return $this->json($commentService->getPendingComments(), Response::HTTP_OK);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/validate-comment/{commentId}', name: 'validate')]
    #[IsGranted('ROLE_ADMIN')]
    public function validateComment(CommentService $commentService, $commentId): JsonResponse
    {
        try {
            $commentService->validateComment($commentId);
            return $this->json(['message' => 'Commentaire validée']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/reject-comment/{commentId}', name: 'reject')]
    #[IsGranted('ROLE_ADMIN')]
    public function rejectComment(CommentService $commentService, $commentId): JsonResponse
    {

        try {
            $commentService->rejectComment($commentId);
            return $this->json(['message' => 'Commentaire rejete']);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
