<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProduitRepository::class)
 */
class Produit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @ORM\Column(type="float")
     */
    private $commission;


    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class, inversedBy="produit")
     */
    private $categorie;

    public $imageFiles;

    /**
     * @ORM\ManyToMany(targetEntity=SousCategorie::class, inversedBy="produits")
     */
    private $sousCategories;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="produits")
     */
    private $cuisinier;






    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }


    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection|SousCategorie[]
     */
    public function getSousCategories(): Collection
    {
        return $this->sousCategories;
    }

    public function addSousCategory(SousCategorie $sousCategory): self
    {
        if (!$this->sousCategories->contains($sousCategory)) {
            $this->sousCategories[] = $sousCategory;
        }

        return $this;
    }

    public function removeSousCategory(SousCategorie $sousCategory): self
    {
        $this->sousCategories->removeElement($sousCategory);

        return $this;
    }

    public function getCuisinier(): ?User
    {
        return $this->cuisinier;
    }

    public function setCuisinier(?User $cuisinier): self
    {
        $this->cuisinier = $cuisinier;

        return $this;
    }

    public function setCommission(int $int)
    {
    }

}
