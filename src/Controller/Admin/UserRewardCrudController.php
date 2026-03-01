<?php

namespace App\Controller\Admin;

use App\Entity\UserReward;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserRewardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserReward::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('profile', 'Profil joueur');
        yield AssociationField::new('reward', 'Récompense');
        yield TextField::new('source', 'Source')->setRequired(false);
        yield DateTimeField::new('unlockedAt', 'Débloqué le')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): UserReward
    {
        $userReward = new UserReward();
        $userReward->setUnlockedAt(new \DateTimeImmutable());

        return $userReward;
    }
}
