<?php

namespace App\Enum;

enum Regime: string
{
    case Aucun = 'aucun';
    case Vegetarien = 'vegetarien';
    case Vegan = 'vegan';
    case Poisson = 'poisson';
    
    public function getLabel(): string
    {
        return match ($this) {
            self::Aucun => 'Aucun',
            self::Vegetarien => 'Vegetarien',
            self::Vegan => 'Vegan',
            self::Poisson => 'Poisson',
        };
    }
}