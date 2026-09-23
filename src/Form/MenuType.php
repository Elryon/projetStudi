<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Plats;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Enum\Regime as EnumRegime;
use App\Repository\PlatsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                'trim' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez donner un nom au menu.'])
                ]
            ])
            ->add('personne_min')
            ->add('prix_personne')
            ->add('description', TextType::class, [
                'required' => false,
                'trim' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez donner une description du menu.'])
                ]
            ])
            ->add('conditions', TextType::class, [
                'required' => false,
                'trim' => true,
            ])
            ->add('quantite_restante')
            ->add('regime', EnumType::class, [
                'class' => EnumRegime::class,
                'choice_label' => 'getLabel',
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'theme',
            ])
            ->add('plats', EntityType::class, [
                'class' => Plats::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (PlatsRepository $repo) {
                    return $repo->createQueryBuilder('plats')
                        ->where('plats.disponible = true')
                        ->orderBy('plats.nom', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
