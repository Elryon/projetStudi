<?php

namespace App\Form;

use App\Entity\Plats;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class PlatsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'trim' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez donner un nom au plat.'])
                ]
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Photo du plat',
                'help' => 'Votre photo de profil de doit pas dépasser les 1Mo et doit être de type: PNG, JPEG, JPG ou WEBP.',
                'constraints' => [
                    new File([
                        'extensions' =>['png', 'jpeg', 'jpg', 'webp'],
                        'extensionsMessage' => "Votre fichier n'est pas une image au format accepté.",
                        'maxSize' => '1M',
                        'maxSizeMessage' => "L'image de doit pas dépasser {{ limit }} en poids."
                    ])
                ]
            ])
            ->add('allergenes', TextType::class, [
                'required' => false,
                'trim' => true
            ]);

            if($options['exist']){
                $builder
                    ->add('disponible', CheckboxType::class,[
                        'label' => 'disponible',
                        'required' => false
                    ]);
            };
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plats::class,
            'exist' => false
        ]);
    }
}
