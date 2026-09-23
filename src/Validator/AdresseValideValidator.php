<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdresseValideValidator extends ConstraintValidator
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $response = $this->httpClient->request('GET', 'https://api-adresse.data.gouv.fr/search/', [
            'query' => [
                'q' => $value,
                'limit' => 1,
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['features'])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ adresse }}', $value)
                ->addViolation();
            return;
        }

        $properties = $data['features'][0]['properties'];

        if ($properties['score'] < 0.5) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ adresse }}', $value)
                ->addViolation();
            return;
        }

        // Vérifier que la ville est présente dans la saisie
        $city = strtolower($properties['city']);
        $saisie = strtolower($value);

        if (!str_contains($saisie, $city)) {
            $this->context->buildViolation('Veuillez inclure la ville dans votre adresse (ex: {{ label }}).')
                ->setParameter('{{ label }}', $properties['label'])
                ->addViolation();
        }
    }
}