<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class OrderCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $em) {}

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('user', 'Utilisateur');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => OrderStatus::PENDING,
                'Validée'    => OrderStatus::VALIDATED,
                'Annulée'    => OrderStatus::CANCELLED,
            ])
            ->renderAsBadges([
                'En attente' => 'warning',
                'Validée'    => 'success',
                'Annulée'    => 'danger',
            ]);
        yield MoneyField::new('total', 'Total')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
        yield DateTimeField::new('createdAt', 'Date')->hideOnForm();
        yield CollectionField::new('items', 'Articles')
            ->hideOnIndex()
            ->hideOnForm();
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $previousStatus = $originalData['status'] ?? null;
        $newStatus = $entityInstance->getStatus();

        $wasCancelled = $previousStatus !== OrderStatus::CANCELLED
            && $newStatus === OrderStatus::CANCELLED;

        if ($wasCancelled) {
            foreach ($entityInstance->getItems() as $item) {
                $product = $item->getProduct();
                if ($product !== null) {
                    $product->setStock($product->getStock() + $item->getQuantity());
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
