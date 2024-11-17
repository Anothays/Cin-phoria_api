<?php

namespace App\Controller\Admin;

use App\Entity\UserStaff;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStaffCrudController extends AbstractCrudController
{

    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}

    public static function getEntityFqcn(): string
    {
        return UserStaff::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('fullName', 'Nom')->hideOnForm(),
            TextField::new('lastname', 'Nom')->onlyOnForms(),
            TextField::new('firstname', 'Prénom')->onlyOnForms(),
            EmailField::new('email'),
            TextField::new('password', 'Mot de passe')
            ->onlyWhenCreating()
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez le mot de passe'],
            ]),
            ArrayField::new('roles', 'Rôles')->hideOnForm(),
            DateTimeField::new('createdAt', 'inscrit le')->hideOnForm(),
        ];
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Employé')
        ->setEntityLabelInPlural('Employés')
        ->setPageTitle('index', 'Les employés')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ->setPermission(Action::NEW, 'ROLE_ADMIN')
        ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setRoles(["ROLE_STAFF"]);

        /** @var UserStaff $entityInstance */
        $entityInstance->setPassword($this->userPasswordHasher->hashPassword($entityInstance, $entityInstance->getPassword()));
        parent::persistEntity($entityManager, $entityInstance);

    }
}
