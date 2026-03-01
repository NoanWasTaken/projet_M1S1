<?php

namespace App\Controller\Admin;

use App\Entity\CartItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class CartItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CartItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('cart', 'Panier');
        yield AssociationField::new('product', 'Produit');
        yield IntegerField::new('quantity', 'Quantité');
        yield DateTimeField::new('addedAt', 'Ajouté le')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): CartItem
    {
        return new CartItem();
    }
}
