<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Menu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use App\Validator as AppAssert;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['modifier']) {
            $builder->add('menu', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => 'titre',
                'choices' => $options['menus'] ?? null,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un menu.']),
                ],
            ]);
        }

        $builder
            ->add('date_livraison', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer une date de livraison.']),
                    new GreaterThan([
                        'value' => 'today',
                        'message' => 'La date de livraison doit être dans le futur.',
                    ]),
                ],
            ])
            ->add('nombre_personne', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer le nombre de personnes.']),
                    new Positive(['message' => 'Le nombre de personnes doit être supérieur à 0.']),
                ],
            ])
            ->add('adresse', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez indiquer une adresse de livraison.']),
                    new AppAssert\AdresseValide(),
                ],
            ])
            ->add('pret_materiel', CheckboxType::class, [
                'required' => false,
                'label' => 'Prêt de matériel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'modifier' => false,
            'menus' => null,
        ]);
    }
}