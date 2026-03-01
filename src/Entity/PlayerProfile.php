<?php

namespace App\Entity;

use App\Repository\PlayerProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerProfileRepository::class)]
class PlayerProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $xpTotal = 0;

    #[ORM\Column]
    private ?int $level = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private string $hairSkin = 'bald_head.webp';

    #[ORM\Column(length: 255)]
    private string $bodySkin = 'normal_body.webp';

    #[ORM\OneToOne(inversedBy: 'playerProfile', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, XPEvent>
     */
    #[ORM\OneToMany(targetEntity: XPEvent::class, mappedBy: 'profile')]
    private Collection $xPEvents;

    /**
     * @var Collection<int, UserReward>
     */
    #[ORM\OneToMany(targetEntity: UserReward::class, mappedBy: 'profile', orphanRemoval: true)]
    private Collection $userRewards;

    public function __construct()
    {
        $this->xPEvents = new ArrayCollection();
        $this->userRewards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getXpTotal(): ?int
    {
        return $this->xpTotal;
    }

    public function setXpTotal(int $xpTotal): static
    {
        $this->xpTotal = $xpTotal;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, XPEvent>
     */
    public function getXPEvents(): Collection
    {
        return $this->xPEvents;
    }

    public function addXPEvent(XPEvent $xPEvent): static
    {
        if (!$this->xPEvents->contains($xPEvent)) {
            $this->xPEvents->add($xPEvent);
            $xPEvent->setProfile($this);
        }

        return $this;
    }

    public function removeXPEvent(XPEvent $xPEvent): static
    {
        if ($this->xPEvents->removeElement($xPEvent)) {
            // set the owning side to null (unless already changed)
            if ($xPEvent->getProfile() === $this) {
                $xPEvent->setProfile(null);
            }
        }

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
            $userReward->setProfile($this);
        }

        return $this;
    }

    public function removeUserReward(UserReward $userReward): static
    {
        if ($this->userRewards->removeElement($userReward)) {
            // set the owning side to null (unless already changed)
            if ($userReward->getProfile() === $this) {
                $userReward->setProfile(null);
            }
        }

        return $this;
    }

    public function getHairSkin(): string
    {
        return $this->hairSkin;
    }

    public function setHairSkin(string $hairSkin): static
    {
        $this->hairSkin = $hairSkin;
        return $this;
    }

    public function getBodySkin(): string
    {
        return $this->bodySkin;
    }

    public function setBodySkin(string $bodySkin): static
    {
        $this->bodySkin = $bodySkin;
        return $this;
    }
}
