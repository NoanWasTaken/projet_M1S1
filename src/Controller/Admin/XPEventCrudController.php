<?php

namespace App\Controller\Admin;

use App\Entity\XPEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class XPEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return XPEvent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('profile', 'Profil joueur');
        yield IntegerField::new('amount', 'Montant XP');
        yield TextField::new('reason', 'Raison');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): XPEvent
    {
        $event = new XPEvent();
        $event->setCreatedAt(new \DateTimeImmutable());

        return $event;
    }
}
