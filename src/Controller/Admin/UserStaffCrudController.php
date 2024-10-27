<?php

namespace App\Controller\Admin;

use App\Entity\UserStaff;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserStaffCrudController extends AbstractCrudController
{
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
            ArrayField::new('roles', 'Rôles'),
            DateTimeField::new('createdAt', 'inscrit le'),
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
        ->setPermission(Action::EDIT, 'ROLE_ADMIN')
        ;
    }
}
