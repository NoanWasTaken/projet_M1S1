<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReviewCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural('Avis')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('product', 'Produit');
        yield AssociationField::new('author', 'Auteur')->setRequired(false);
        yield TextField::new('authorName', 'Nom affiché')->onlyOnDetail();
        yield IntegerField::new('rating', 'Note');
        yield TextareaField::new('comment', 'Commentaire')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Date')->hideOnForm();
    }

    public function createEntity(string $entityFqcn): Review
    {
        $review = new Review();
        $review->setCreatedAt(new \DateTimeImmutable());
        return $review;
    }

    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        if ($entityInstance instanceof Review && $entityInstance->getProduct()) {
            $entityInstance->getProduct()->computeAverageRating();
            $entityManager->flush();
        }
    }

    public function deleteEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $product = null;
        if ($entityInstance instanceof Review) {
            $product = $entityInstance->getProduct();
        }

        parent::deleteEntity($entityManager, $entityInstance);

        if ($product) {
            $product->computeAverageRating();
            $entityManager->flush();
        }
    }
}
