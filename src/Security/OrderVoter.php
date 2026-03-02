<?php

namespace App\Security;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrderVoter extends Voter
{
    public const VIEW   = 'ORDER_VIEW';
    public const CANCEL = 'ORDER_CANCEL';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CANCEL], true)
            && $subject instanceof Order;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        $order = $subject;

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)
            || in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW   => $this->canView($order, $user),
            self::CANCEL => $this->canCancel($order, $user),
            default      => false,
        };
    }

    private function canView(Order $order, User $user): bool
    {
        return $order->getUser()?->getId() === $user->getId();
    }

    private function canCancel(Order $order, User $user): bool
    {
        if ($order->getUser()?->getId() !== $user->getId()) {
            return false;
        }

        return $order->getStatus() === OrderStatus::PENDING
            || $order->getStatus() === OrderStatus::VALIDATED;
    }
}
