<?php

namespace App\Command;

use App\Entity\Accounts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new admin user',
)]

// docker compose exec php php bin/console app:create-admin admin@talklabs.com 'Admin User' 'password123'"
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email address')
            ->addArgument('name', InputArgument::REQUIRED, 'Admin full name')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $password = $input->getArgument('password');

        $existingUser = $this->entityManager->getRepository(Accounts::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error(sprintf('User with email "%s" already exists!', $email));
            return Command::FAILURE;
        }

        $admin = new Accounts();
        $admin->setEmail($email);
        $admin->setName($name);
        $admin->setRole(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setCreatedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setPassword($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user "%s" has been created successfully!', $email));

        return Command::SUCCESS;
    }
}