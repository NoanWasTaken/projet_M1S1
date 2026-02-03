<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $photos = null;

    #[ORM\Column]
    private ?float $price = null;

    /**
     * @var Collection<int, GameTypes>
     */
    #[ORM\ManyToMany(targetEntity: GameTypes::class, inversedBy: 'products')]
    private Collection $game_types;

    public function __construct()
    {
        $this->game_types = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): static
    {
        $this->photos = $photos;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, GameTypes>
     */
    public function getGameTypes(): Collection
    {
        return $this->game_types;
    }

    public function addGameType(GameTypes $gameType): static
    {
        if (!$this->game_types->contains($gameType)) {
            $this->game_types->add($gameType);
        }

        return $this;
    }

    public function removeGameType(GameTypes $gameType): static
    {
        $this->game_types->removeElement($gameType);

        return $this;
    }
}
