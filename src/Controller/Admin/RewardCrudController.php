<?php

namespace App\Controller\Admin;

use App\Entity\Reward;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RewardCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reward::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('code', 'Code');
        yield TextField::new('name', 'Nom');
        yield TextField::new('type', 'Type');
        yield TextField::new('ruletype', 'Type de règle');
        yield TextField::new('ruleValue', 'Valeur de règle')->setRequired(false);
        yield TextareaField::new('description', 'Description')->setRequired(false)->hideOnIndex();
        yield BooleanField::new('isActive', 'Actif');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): Reward
    {
        return new Reward();
    }
}
