<?php

namespace App\Controller\Admin;

use App\Entity\GameTypes;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GameTypesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GameTypes::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('type');
    }
}
