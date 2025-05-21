<?php

namespace App\Controller;

use App\Entity\Accounts;
use App\Form\RegistrationFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Accounts();
        $user->setRole(['ROLE_USER']);
        $user->setIsVerified(false);
        $user->setCreatedAt(new \DateTimeImmutable());
        
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                
                $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
                $user->setPassword($hashedPassword);
                
                
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
                
                return $this->redirectToRoute('app_login');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée. Veuillez en utiliser une autre ou vous connecter.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
} 