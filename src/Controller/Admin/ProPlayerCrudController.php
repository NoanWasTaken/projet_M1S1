<?php

namespace App\Controller\Admin;

use App\Entity\ProPlayer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ProPlayerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProPlayer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('role', 'Rôle');
        yield TextField::new('team', 'Équipe');
        yield TextField::new('country', 'Pays');
        yield TextField::new('game', 'Jeu');
        yield TextField::new('mouse', 'Souris');
        yield TextField::new('keyboard', 'Clavier');
        yield TextField::new('headset', 'Casque');
        yield TextField::new('photo', 'Photo (URL)')->setRequired(false)->hideOnIndex();
        yield TextareaField::new('description', 'Description')->setRequired(false)->hideOnIndex();
    }
}
