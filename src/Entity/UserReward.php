<?php

namespace App\Entity;

use App\Repository\UserRewardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRewardRepository::class)]
#[ORM\Table(name: 'user_reward')]
#[ORM\UniqueConstraint(name: 'uniq_profile_reward', columns: ['profile_id', 'reward_id'])]
class UserReward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $unlockedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(nullable: true)]
    private ?array $meta = null;

    #[ORM\ManyToOne(inversedBy: 'userRewards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlayerProfile $profile = null;

    #[ORM\ManyToOne(inversedBy: 'userRewards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reward $reward = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnlockedAt(): ?\DateTimeImmutable
    {
        return $this->unlockedAt;
    }

    public function setUnlockedAt(\DateTimeImmutable $unlockedAt): static
    {
        $this->unlockedAt = $unlockedAt;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function setMeta(?array $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getProfile(): ?PlayerProfile
    {
        return $this->profile;
    }

    public function setProfile(?PlayerProfile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): static
    {
        $this->reward = $reward;

        return $this;
    }
}
