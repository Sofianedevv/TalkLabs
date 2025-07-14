<?php

namespace App\Service;

use App\DTO\ConversationDTO;

use App\DTO\ConversationEditDTO;
use App\DTO\ConversationReadDTO;
use App\Entity\Conversation;
use App\Entity\Accounts;
use App\Enum\ConversationStatusEnum;
use App\Event\Conversation\ConversationCreatedEvent;
use App\Event\Conversation\ConversationDeletedEvent;
use App\Event\Conversation\ConversationEditedEvent;
use App\Mapper\ConversationMapper;
use App\Repository\AccountsRepository;
use App\Repository\CategoryRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConversationService
{

    private EntityManagerInterface $em;
    private CategoryRepository $categoryRepository;
    private AccountsRepository $accountsRepository;
    private ConversationMapper $conversationMapper;
    private ConversationRepository $conversationRepository;
    private Security $security;
    private SluggerInterface $slugger;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        AccountsRepository $accountsRepository,
        ConversationMapper $conversationMapper,
        ConversationRepository $conversationRepository,
        Security $security, 
        SluggerInterface $slugger,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->em = $em;
        $this->categoryRepository = $categoryRepository;
        $this->accountsRepository = $accountsRepository;
        $this->conversationMapper = $conversationMapper;
        $this->conversationRepository = $conversationRepository;
        $this->security = $security;
        $this->slugger = $slugger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createConversation(ConversationDTO $dto): void
    {
        
        $creator = $this->security->getUser();
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        
        $categories = $this->categoryRepository->findBy(['id' => $dto->getCategoriesId()]);

        if(count($categories) === 0) {
            throw new \RuntimeException('Aucune catégorie trouvé');
        }

        $conversation = $this->conversationMapper->dtoToConversation($dto, $creator, $categories);
        $this->em->persist($conversation);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new ConversationCreatedEvent($conversation), ConversationCreatedEvent::NAME);
    }

    public function getAllPublicConversations(): array
    {
            $conversations = $this->conversationRepository->findAllPublicConversations();
            return $this->conversationToDTO($conversations);

    }

    public function editMessage($id, ConversationDTO $dto): bool
    {
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new NotFoundHttpException('Conversation non trouvée');
        }
        
        /** @var Accounts|null $creator */
        $creator = $this->security->getUser();
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        if ($conversation->getCreator()->getId() !== $creator->getId()) {
            throw new \RuntimeException('Vous n\'êtes pas autorisé à modifier cette conversaiton');
        }

        $categories = $this->categoryRepository->findBy(['id' => $dto->getCategoriesId()]);

        if(count($categories) === 0) {
            throw new \RuntimeException('Aucune catégorie trouvé');
        }
        $conversation = $this->conversationMapper->dtoToConversationEdit($dto, $conversation, $creator, $categories);

        $this->em->persist($conversation);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new ConversationEditedEvent($conversation), ConversationEditedEvent::NAME);

        return true;
    }

    public function deleteConversation($id){
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        /** @var Accounts|null $creator */
        $creator = $this->security->getUser(); 
        if (!$creator) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }
        if ($conversation->getCreator()->getId() !== $creator->getId()) {
            throw new \RuntimeException('Vous n\'êtes pas autorisé à modifier cette conversaiton');
        }

        $conversationForEvent = clone $conversation;

        $this->em->remove($conversation);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new ConversationDeletedEvent($conversationForEvent), ConversationDeletedEvent::NAME);
        error_log('ConversationCreatedEvent dispatché');

    }

    public function getConversationById($id) {
        /** @var Conversation|null $conversation */
        $conversation = $this->conversationRepository->find($id);

        if (!$conversation) {
            throw new \RuntimeException('Conversation non trouvée');
        }

        return $this->conversationMapper->conversationToReadDTO($conversation);
    }

    public function getConversationsByUser(): array {
        /** @var Accounts|null $user */
        $user = $this->security->getUser(); 
        if (!$user) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        $conversations = $this->conversationRepository->findBy(['creator' => $user]);
        return $this->conversationToDTO($conversations);
    }

    public function uploadMedia(Request $request) : array {

        $res = [];

        $image = $request->files->get('image');
        $audio = $request->files->get('audio');

        $uploadDir = '/uploads';

        if($image) {
            $filename = $this->generateUniqueFilename($image);
            $image->move(__DIR__ . '/../../public' . $uploadDir . '/images/', $filename);
            $res['imageUrl'] = $uploadDir . '/images/' . $filename;
        }

        if($audio) {
            $filename = $this->generateUniqueFilename($audio);
            $audio->move(__DIR__ . '/../../public' . $uploadDir . '/audios/', $filename);
            $res['audioUrl'] = $uploadDir . '/audios/' . $filename;
        }

        return $res;
    }

    public function getPublicConversationsByCategory(int $categoryId) : array {
        $conversations = $this->conversationRepository->findPublicConversationsByCategory($categoryId);
        return $this->conversationToDTO($conversations);
    }

     
    public function searchPublicConversation(string $title, array $categoryNames) : array {
        $title = trim($title);

        if(empty($categoryNames)) {
            return $this->searchConversation($title);
        }

        $conversations = $this->conversationRepository->findPublicConversationsByTitleAndCategoryNames($title, $categoryNames);
        return $this->conversationToDTO($conversations);
    }

    public function searchPublicConversationsPaginated(string $title,array $categoryNames, int $page, int $limit): array {
            $res = $this->conversationRepository->findPublicConversationsPaginated($title, $categoryNames, $page, $limit);

            return [
                'total' => $res['total'],
                'pages' => $res['pages'],
                'currentPage' => $res['currentPage'],
                'limit' => $res['limit'],
                'conversations' => $this->conversationToDTO($res['conversations'])
            ];
    }


    private function generateUniqueFilename(UploadedFile $file) : string {
        $initialFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugFilename = $this->slugger->slug($initialFilename);
        $extension = $file->guessExtension();

        return $slugFilename . '-' . uniqid() . '_temp' . '.' . $extension;
    }

    private function  conversationToDTO(array $conversations) : array {

        if(!$conversations) {
            return [];
        }

        $convs = [];
        foreach($conversations as $conversation) {
            $convs[] = $this->conversationMapper->conversationToReadDTO($conversation);
        }

        return $convs;
    }

    private function searchConversation(string $title): array {
        if (empty($title)) {
            $conversations = $this->conversationRepository->findAllPublicConversations();
        } else {
            $conversations = $this->conversationRepository->findPublicConversationsByTitle($title);
        }

        return $this->conversationToDTO($conversations);
    }

}