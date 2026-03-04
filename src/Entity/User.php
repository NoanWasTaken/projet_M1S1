<?php

namespace App\Entity;

use App\Entity\ChatConversation;
use App\Entity\Order;
use App\Entity\SavedCart;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private ?PlayerProfile $playerProfile = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $surname = null;

    /**
     * @var Collection<int, GameTypes>
     */
    #[ORM\ManyToMany(targetEntity: GameTypes::class)]
    private Collection $game_types;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    private Collection $orders;

    /**
     * @var Collection<int, ChatConversation>
     */
    #[ORM\OneToMany(targetEntity: ChatConversation::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: false)]
    private Collection $chatConversations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SavedCart::class, cascade: ['persist', 'remove'])]
    private Collection $savedCarts;

    #[ORM\ManyToMany(targetEntity: PromoCode::class, mappedBy: 'users')]
    private Collection $promoCodes;

    public function __construct()
    {
        $this->game_types = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->chatConversations = new ArrayCollection();
        $this->savedCarts = new ArrayCollection();
        $this->promoCodes = new ArrayCollection();
    }

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Cart $cart = null;

    public function __toString(): string
    {
        return trim(($this->name ?? '') . ' ' . ($this->surname ?? '')) ?: ($this->email ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

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
    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        // set the owning side of the relation if necessary
        if ($cart->getUser() !== $this) {
            $cart->setUser($this);
        }

        $this->cart = $cart;

        return $this;
    }

    public function getPlayerProfile(): ?PlayerProfile
    {
        return $this->playerProfile;
    }

    public function setPlayerProfile(PlayerProfile $playerProfile): static
    {
        // set the owning side of the relation if necessary
        if ($playerProfile->getOwner() !== $this) {
            $playerProfile->setOwner($this);
        }

        $this->playerProfile = $playerProfile;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChatConversation>
     */
    public function getChatConversations(): Collection
    {
        return $this->chatConversations;
    }

    public function addChatConversation(ChatConversation $conversation): static
    {
        if (!$this->chatConversations->contains($conversation)) {
            $this->chatConversations->add($conversation);
            $conversation->setUser($this);
        }
        return $this;
    }

    public function getSavedCarts(): Collection
    {
        return $this->savedCarts;
    }
    public function getPromoCodes(): Collection
    {
        return $this->promoCodes;
    }
    public function addPromoCode(PromoCode $promoCode): static
    {
        if (!$this->promoCodes->contains($promoCode)) {
            $this->promoCodes->add($promoCode);
            $promoCode->addUser($this);
        }
        return $this;
    }
    public function removePromoCode(PromoCode $promoCode): static
    {
        $this->promoCodes->removeElement($promoCode);
        $promoCode->removeUser($this);
        return $this;
    }
}
