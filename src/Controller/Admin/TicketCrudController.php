<?php

namespace App\Controller\Admin;

use App\Entity\Ticket;
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
            TextField::new('movie', 'Film'),
            AssociationField::new('category', 'Tarif'),
            DateTimeField::new('projectionEventDateStart', 'Date')->setFormat('short'),
            DateTimeField::new('projectionEventDateStart', 'Heure')->setFormat('HH:mm'),
            TextField::new('movieTheater', 'Cinéma'),
            TextField::new('projectionRoom', 'Salle'),
            TextField::new('projectionFormat', 'Projection format'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            AssociationField::new('reservation', 'Réservation liée')->hideOnForm(),
            // DateTimeField::new('createdAt', 'Émis le'),
        ];
    }
    
}
