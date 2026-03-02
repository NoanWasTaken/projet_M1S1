<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Products::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextEditorField::new('description', 'Description')->hideOnIndex();
        yield MoneyField::new('price', 'Prix')->setCurrency('EUR')->setStoredAsCents(false);
        yield TextField::new('category', 'Catégorie');
        yield IntegerField::new('stock', 'Stock');
        yield TextField::new('brand', 'Marque');
        yield BooleanField::new('isAvailable', 'Disponible');
        yield ArrayField::new('photos', 'Photos')->hideOnIndex()->setHelp('Ajoutez des URLs d\'images');
        yield AssociationField::new('game_types', 'Types de jeux');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }
}
