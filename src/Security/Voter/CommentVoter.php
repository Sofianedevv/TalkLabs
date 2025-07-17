<?php 

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\Accounts;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter {

    public const EDIT = 'COMMENT_EDIT';
    public const DELETE = 'COMMENT_DELETE';

    protected function supports(string $attribute, $subject) : bool {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, $comment, TokenInterface $token) : bool {

        $user = $token->getUser();

        if (!$user instanceof Accounts) {
            return false;
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);
        $isPublisher = $comment->getPublisher()->getId() === $user->getId();
        switch($attribute) {
            case self::EDIT:
            case self::DELETE:
                return $isPublisher || $isAdmin;
        }
        return false;

    }

}