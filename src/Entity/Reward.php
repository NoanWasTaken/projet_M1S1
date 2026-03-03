<?php

namespace App\Entity;

use App\Repository\RewardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $code = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    private ?string $ruletype = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $ruleValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $unlocks = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, UserReward>
     */
    #[ORM\OneToMany(targetEntity: UserReward::class, mappedBy: 'reward', orphanRemoval: true)]
    private Collection $userRewards;

    public function __construct()
    {
        $this->userRewards = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? $this->code ?? 'Récompense #' . $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRuletype(): ?string
    {
        return $this->ruletype;
    }

    public function setRuletype(string $ruletype): static
    {
        $this->ruletype = $ruletype;

        return $this;
    }

    public function getRuleValue(): ?string
    {
        return $this->ruleValue;
    }

    public function setRuleValue(?string $ruleValue): static
    {
        $this->ruleValue = $ruleValue;

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

    public function getUnlocks(): ?array
    {
        return $this->unlocks;
    }

    public function setUnlocks(?array $unlocks): static
    {
        $this->unlocks = $unlocks;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, UserReward>
     */
    public function getUserRewards(): Collection
    {
        return $this->userRewards;
    }

    public function addUserReward(UserReward $userReward): static
    {
        if (!$this->userRewards->contains($userReward)) {
            $this->userRewards->add($userReward);
            $userReward->setReward($this);
        }

        return $this;
    }

    public function removeUserReward(UserReward $userReward): static
    {
        if ($this->userRewards->removeElement($userReward)) {
            if ($userReward->getReward() === $this) {
                $userReward->setReward(null);
            }
        }

        return $this;
    }
}
