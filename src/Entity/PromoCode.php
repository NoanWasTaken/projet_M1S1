<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PromoCodeRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PromoCodeRepository::class)]
class PromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxUsage = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'promoCodes')]
    private Collection $users;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $unlockCondition = null; // e.g. 'CPS_100CLICKS', 'MINIGAME_HOME'

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function isActive(): bool
    {
        return $this->active;
    }
    
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    public function getUnlockCondition(): ?string
    {
        return $this->unlockCondition;
    }
    
    public function setUnlockCondition(?string $unlockCondition): self
    {
        $this->unlockCondition = $unlockCondition;
        return $this;
    }
    
    public function getUsers(): Collection
    {
        return $this->users;
    }
    
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
        return $this;
    }
    
    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);
        return $this;
    }
}
