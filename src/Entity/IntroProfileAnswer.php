<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class IntroProfileAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $gameType;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $answeredAt;

    public function __construct()
    {
        $this->answeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getGameType(): string { return $this->gameType; }
    public function setGameType(string $type): self { $this->gameType = $type; return $this; }
    public function getAnsweredAt(): \DateTimeImmutable { return $this->answeredAt; }
}
