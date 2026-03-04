<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use App\Entity\IntroProfileAnswer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        public UserPasswordHasherInterface $userPasswordHasher,
        private AdminUrlGenerator $adminUrlGenerator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::deleteEntity($entityManager, $entityInstance);
            return;
        }

        foreach ($entityInstance->getOrders() as $order) {
            $order->setUser(null);
            $entityManager->persist($order);
        }

        $entityManager->createQuery(
            'UPDATE ' . IntroProfileAnswer::class . ' a SET a.user = NULL WHERE a.user = :user'
        )->setParameter('user', $entityInstance)->execute();

        $entityManager->flush();
        parent::deleteEntity($entityManager, $entityInstance);
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewConversations = Action::new('viewConversations', 'Voir les conversations', 'fa fa-comments')
            ->linkToRoute('admin_user_conversations', fn (User $user) => ['userId' => $user->getId()])
            ->addCssClass('btn btn-sm btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $viewConversations)
            ->add(Crud::PAGE_DETAIL, $viewConversations);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('email')
            ->formatValue(function ($value, $entity) {
                $url = $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($entity->getId())
                    ->generateUrl();
                return sprintf('<a href="%s">%s</a>', htmlspecialchars($url), htmlspecialchars($value ?? ''));
            })
            ->renderAsHtml()
            ->hideOnForm();
        yield TextField::new('email')
            ->setFormType(EmailType::class)
            ->onlyOnForms();
        yield TextField::new('name');
        yield TextField::new('surname');
        
        $roles = ['ROLE_USER', 'ROLE_MODERATOR', 'ROLE_ADMIN'];
        yield ChoiceField::new('roles')
            ->setChoices(array_combine($roles, $roles))
            ->allowMultipleChoices()
            ->renderAsBadges();

        yield TextField::new('password')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ->setHelp('Leave empty to keep current password');

        yield BooleanField::new('isVerified');
        yield AssociationField::new('game_types');
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function ($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }
            $password = $form->get('password')->getData();
            if ($password === null || $password === '') {
                return;
            }

            // I need the entity from the event data
            $entity = $event->getData();

            if (!$entity instanceof User) {
                return;
            }
            
            $hash = $this->userPasswordHasher->hashPassword($entity, $password);
            $entity->setPassword($hash);
        });
    }
}
