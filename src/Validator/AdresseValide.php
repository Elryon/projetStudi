<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AdresseValide extends Constraint
{
    public string $message = "L'adresse « {{ adresse }} » est introuvable.";
}