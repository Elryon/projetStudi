<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContactRequest
{
    #[Assert\NotBlank(message: 'Veuillez indiquer un titre.')]
    #[Assert\Length(max: 255)]
    public ?string $titre = null;

    #[Assert\NotBlank(message: 'Veuillez indiquer une description.')]
    public ?string $description = null;

    #[Assert\NotBlank(message: 'Veuillez indiquer votre email.')]
    #[Assert\Email(message: 'Cet email n\'est pas valide.')]
    public ?string $email = null;
}