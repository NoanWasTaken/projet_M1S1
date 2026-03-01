<?php

namespace App\Controller\Admin;

use App\Entity\ChatConversation;
use App\Repository\ChatConversationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ChatConversationCrudController extends AbstractCrudController
{
    public function __construct(
        private ChatConversationRepository $conversationRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ChatConversation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Conversation')
            ->setEntityLabelInPlural('Conversations')
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('user', 'Utilisateur');
        yield IntegerField::new('messageCount', 'Nb messages')
            ->hideOnForm()
            ->setSortable(false);
        yield TextField::new('preview', 'Aperçu')
            ->formatValue(fn ($value, $entity) => $entity->getPreview())
            ->hideOnForm()
            ->setSortable(false);
        yield DateTimeField::new('createdAt', 'Créée le')->hideOnForm();
        yield DateTimeField::new('updatedAt', 'Mise à jour')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewAction = Action::new('viewConversation', 'Voir le chat', 'fa fa-eye')
            ->linkToRoute('admin_chat_conversation_detail', fn (ChatConversation $c) => ['id' => $c->getId()])
            ->addCssClass('btn btn-sm btn-info');

        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $viewAction);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('user', 'Utilisateur'));
    }
}
