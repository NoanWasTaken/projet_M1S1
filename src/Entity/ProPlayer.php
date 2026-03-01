<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProPlayer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $role;

    #[ORM\Column(type: 'string', length: 100)]
    private string $team;

    #[ORM\Column(type: 'string', length: 100)]
    private string $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $game;

    #[ORM\Column(type: 'string', length: 100)]
    private string $mouse;

    #[ORM\Column(type: 'string', length: 100)]
    private string $keyboard;

    #[ORM\Column(type: 'string', length: 100)]
    private string $headset;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // Getters & setters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function getTeam(): string { return $this->team; }
    public function setTeam(string $team): self { $this->team = $team; return $this; }
    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): self { $this->country = $country; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): self { $this->photo = $photo; return $this; }
    public function getGame(): string { return $this->game; }
    public function setGame(string $game): self { $this->game = $game; return $this; }
    public function getMouse(): string { return $this->mouse; }
    public function setMouse(string $mouse): self { $this->mouse = $mouse; return $this; }
    public function getKeyboard(): string { return $this->keyboard; }
    public function setKeyboard(string $keyboard): self { $this->keyboard = $keyboard; return $this; }
    public function getHeadset(): string { return $this->headset; }
    public function setHeadset(string $headset): self { $this->headset = $headset; return $this; }
}
