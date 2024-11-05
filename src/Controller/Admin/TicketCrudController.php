<?php

namespace App\Controller\Admin;

use App\Entity\Ticket;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ticket::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Billet')
        ->setEntityLabelInPlural('Billets')
        ->setPageTitle('index', 'Les billets')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('movie', 'Film')->hideOnForm(),
            AssociationField::new('category', 'Tarif'),
            DateTimeField::new('projectionEventDateStart', 'Date')->setFormat('short')->hideOnForm(),
            DateTimeField::new('projectionEventDateStart', 'Heure')->setFormat('HH:mm')->hideOnForm(),
            TextField::new('movieTheater', 'Cinéma')->hideOnForm(),
            TextField::new('projectionRoom', 'Salle')->hideOnForm(),
            TextField::new('projectionFormat', 'Projection format')->hideOnForm(),
            MoneyField::new('price', 'Prix')->setCurrency('EUR')->hideOnForm(),
            AssociationField::new('reservation', 'Réservation liée'),
            // DateTimeField::new('createdAt', 'Émis le'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->setPermissions([
            Action::DELETE, 'ROLE_ADMIN',
            Action::EDIT, 'ROLE_ADMIN'
        ])
        ->remove(Crud::PAGE_INDEX, Action::NEW)
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->disable(Action::DELETE)
        ->disable(Action::EDIT)
        ;
    }
    
}
