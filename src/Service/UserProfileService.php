<?php 


namespace App\Service;

use App\DTO\UserProfileEditDTO;
use App\Entity\Accounts;
use App\Mapper\UserProfileMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UserProfileService {

    private EntityManagerInterface $em;
    private SluggerInterface $slugger;
    private Security $security;
    private UserProfileMapper $mapper;

    

    public function __construct(EntityManagerInterface $em,SluggerInterface $slugger,Security $security, UserProfileMapper $mapper) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->security = $security;
        $this->mapper = $mapper;
    }


    public function updateUserProfile(UserProfileEditDTO $dto, Request $request): Accounts
 {

    $user = $this->security->getUser();

    if (!$user instanceof Accounts) {
        throw new \RuntimeException('Utilisateur non trouvé ou non authentifié');
    }
        
    $this->mapper->dtoToAccountProfile($dto, $user);

    $imageAvatar = $request->files->get('avatar');
    $uploadDir = '/uploads';

    if($imageAvatar) {
        $filename = $this->generateUniqueFilename($imageAvatar);
        $imageAvatar->move(__DIR__ . '/../../public' . $uploadDir . '/avatar/', $filename);
        $user->setAvatarUrl($uploadDir . '/avatar/' . $filename);
    }

    $this->em->flush();
    return $user;
 }

      private function generateUniqueFilename(UploadedFile $file): string
    {
        $initialFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugFilename = $this->slugger->slug($initialFilename);
        $extension = $file->guessExtension();

        return $slugFilename . '-' . uniqid() . '.' . $extension;
    }
}