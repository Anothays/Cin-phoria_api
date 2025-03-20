<?php

namespace App\Controller\Admin;

use App\Entity\ProjectionRoom;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectionRoomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectionRoom::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('titleRoom', 'Nom de Salle'),
            AssociationField::new('projectionRoomSeats', 'Sièges'),
            AssociationField::new('movieTheater', 'Cinéma'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Salle')
        ->setEntityLabelInPlural('Salles')
        ->setPageTitle('index', 'Les salles')
        ->setPaginatorPageSize('100')
        ->showEntityActionsInlined()
        ;
    }

}
