<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'commandes_stats')]
class CommandeStat
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $commandeId = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $menuId = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $menuTitre = null;

    #[MongoDB\Field(type: 'float')]
    private ?float $prixTotal = null;

    #[MongoDB\Field(type: 'int')]
    private ?int $nombrePersonne = null;

    #[MongoDB\Field(type: 'date')]
    private ?\DateTime $dateCommande = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $statut = null;

    public function getId(): ?string { return $this->id; }
    public function getCommandeId(): ?string { return $this->commandeId; }
    public function setCommandeId(string $commandeId): static { $this->commandeId = $commandeId; return $this; }
    public function getMenuId(): ?string { return $this->menuId; }
    public function setMenuId(string $menuId): static { $this->menuId = $menuId; return $this; }
    public function getMenuTitre(): ?string { return $this->menuTitre; }
    public function setMenuTitre(string $menuTitre): static { $this->menuTitre = $menuTitre; return $this; }
    public function getPrixTotal(): ?float { return $this->prixTotal; }
    public function setPrixTotal(float $prixTotal): static { $this->prixTotal = $prixTotal; return $this; }
    public function getNombrePersonne(): ?int { return $this->nombrePersonne; }
    public function setNombrePersonne(int $nombrePersonne): static { $this->nombrePersonne = $nombrePersonne; return $this; }
    public function getDateCommande(): ?\DateTime { return $this->dateCommande; }
    public function setDateCommande(\DateTime $dateCommande): static { $this->dateCommande = $dateCommande; return $this; }
    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
}