<?php

namespace App\Form;

use App\Entity\Accounts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Entrez votre nom complet'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom',
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez votre nom d\'utilisateur (optionnel)'
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Entrez votre adresse email'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email',
                    ]),
                    new Email([
                        'message' => 'L\'adresse email n\'est pas valide',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'placeholder' => 'Entrez votre mot de passe'
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe',
                    'attr' => [
                        'placeholder' => 'Confirmez votre mot de passe'
                    ],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions d\'utilisation',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions d\'utilisation.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Accounts::class,
        ]);
    }
} 