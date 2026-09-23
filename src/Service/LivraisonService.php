<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class LivraisonService
{
    private const ADRESSE_RESTAURANT = 'Place Pey-Berland , Bordeaux';
    private const FRAIS_BASE = 5.00;
    private const PRIX_KM = 0.59;

    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    private function geocode(string $adresse): ?array
    {
        $response = $this->httpClient->request('GET', 'https://api-adresse.data.gouv.fr/search/', [
            'query' => [
                'q' => $adresse,
                'limit' => 1,
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['features'])) {
            return null;
        }

        $coords = $data['features'][0]['geometry']['coordinates'];
        $city = $data['features'][0]['properties']['city'] ?? null;

        return [
            'lng' => $coords[0],
            'lat' => $coords[1],
            'city' => $city,
        ];
    }

    private function getDistanceRoute(array $point1, array $point2): ?float
    {
        $url = sprintf(
            'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s?overview=false',
            $point1['lng'], $point1['lat'],
            $point2['lng'], $point2['lat']
        );

        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray();

        if ($data['code'] !== 'Ok' || empty($data['routes'])) {
            return null;
        }

        // L'API retourne la distance en mètres, on convertit en km
        return round($data['routes'][0]['distance'] / 1000, 2);
    }

    public function calculerFraisLivraison(string $adresseLivraison): array
    {
        $point = $this->geocode($adresseLivraison);

        if (!$point) {
            throw new \RuntimeException('Adresse introuvable.');
        }

        if (strtolower($point['city']) === 'bordeaux') {
            return [
                'gratuit' => true,
                'frais' => 0,
                'distance' => 0,
                'ville' => $point['city'],
            ];
        }

        $restaurant = $this->geocode(self::ADRESSE_RESTAURANT);
        $distance = $this->getDistanceRoute($restaurant, $point);

        if ($distance === null) {
            throw new \RuntimeException('Impossible de calculer la distance routière.');
        }

        $frais = self::FRAIS_BASE + ($distance * self::PRIX_KM);

        return [
            'gratuit' => false,
            'frais' => round($frais, 2),
            'distance' => $distance,
            'ville' => $point['city'],
        ];
    }
}