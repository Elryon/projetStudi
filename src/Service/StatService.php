<?php

namespace App\Service;

use App\Document\CommandeStat;
use App\Entity\Commande;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatService
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function enregistrerCommande(Commande $commande): void
    {
        $stat = $this->dm->getRepository(CommandeStat::class)
            ->findOneBy(['commandeId' => (string) $commande->getId()]);

        if (!$stat) {
            $stat = new CommandeStat();
            $stat->setCommandeId((string) $commande->getId());
        }

        $stat->setMenuId((string) $commande->getMenu()->getId());
        $stat->setMenuTitre($commande->getMenu()->getTitre());
        $stat->setPrixTotal((float) $commande->getPrixTotal());
        $stat->setNombrePersonne($commande->getNombrePersonne());
        $stat->setDateCommande($commande->getDateCommande());
        $stat->setStatut($commande->getDernierStatut()->getStatut()->value);

        $this->dm->persist($stat);
        $this->dm->flush();
    }

    public function getStats(?string $menu = null, ?string $debut = null, ?string $fin = null): array
    {
        $qb = $this->dm->createQueryBuilder(CommandeStat::class);

        if ($menu) {
            $qb->field('menuTitre')->equals($menu);
        }
        if ($debut) {
            $qb->field('dateCommande')->gte(new \DateTime($debut));
        }
        if ($fin) {
            $qb->field('dateCommande')->lte(new \DateTime($fin . ' 23:59:59'));
        }

        $stats = $qb->getQuery()->execute();

        $parMenu = [];
        $caParMenu = [];
        $caTotal = 0;

        foreach ($stats as $stat) {
            $titre = $stat->getMenuTitre();
            if (!isset($parMenu[$titre])) {
                $parMenu[$titre] = 0;
                $caParMenu[$titre] = 0;
            }
            $parMenu[$titre]++;
            $caParMenu[$titre] += $stat->getPrixTotal();
            $caTotal += $stat->getPrixTotal();
        }

        return [
            'commandesParMenu' => $parMenu,
            'caParMenu' => $caParMenu,
            'caTotal' => round($caTotal, 2),
        ];
    }

    public function getMenuTitres(): array
    {
        $stats = $this->dm->getRepository(CommandeStat::class)->findAll();
        $titres = [];
        foreach ($stats as $stat) {
            $titres[] = $stat->getMenuTitre();
        }
        $titres = array_unique($titres);
        sort($titres);
        return $titres;
    }
}