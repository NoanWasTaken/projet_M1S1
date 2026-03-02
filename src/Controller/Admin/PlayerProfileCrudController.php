<?php

namespace App\Controller\Admin;

use App\Entity\PlayerProfile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class PlayerProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayerProfile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('owner', 'Propriétaire');
        yield IntegerField::new('xpTotal', 'XP total');
        yield IntegerField::new('level', 'Niveau');
        yield TextField::new('hairSkin', 'Skin cheveux')->setHelp('Nom du fichier image (ex: bald_head.webp)');
        yield TextField::new('bodySkin', 'Skin corps')->setHelp('Nom du fichier image (ex: normal_body.webp)');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): PlayerProfile
    {
        $profile = new PlayerProfile();
        $profile->setCreatedAt(new \DateTimeImmutable());
        $profile->setUpdatedAt(new \DateTimeImmutable());

        return $profile;
    }
}
