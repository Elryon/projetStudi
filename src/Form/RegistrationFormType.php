<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre nom.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Votre nom ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ]
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre prénom.']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Votre prénom ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => "L'adresse mail est obligatoire."]),
                    new Email(['message' => "L'adresse mail est invalide."]),
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre numéro de téléphone.']),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ]
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre ville.']),
                ]
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez renseigner votre adresse.']),
                ]
            ]);

        if (!$options['is_profile']) {
            $builder
                ->add('plainPassword', PasswordType::class, [
                    'required' => false,
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'help' => 'Votre mot de passe doit faire au moins 10 caractères et contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                    'label' => 'Mot de passe',
                    'constraints' => [
                        new NotBlank(
                            message: 'Veuillez entrer un mot de passe.',
                        ),
                        new Length(
                            min: 10,
                            minMessage: 'Votre mot de passe doit faire au moins {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                            max: 4096,
                        ),
                        new Regex(
                            pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/',
                            message: 'Votre mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                        ),
                    ],
                ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Valider'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_profile' => false,
        ]);
    }
}