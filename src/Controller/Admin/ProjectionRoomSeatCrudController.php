<?php

namespace App\Controller\Admin;

use App\Entity\ProjectionRoomSeat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectionRoomSeatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectionRoomSeat::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            NumberField::new('seatNumber', 'Numéro'),
            TextField::new('seatRow', 'Ligne'),
            BooleanField::new('isForReducedMobility', 'PMR'),
            AssociationField::new('projectionRoom', 'Salle'),
            // AssociationField::new('projectionRoom.getMovieTheater', 'Salle')->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Siège')
        ->setEntityLabelInPlural('Sièges')
        ->setPageTitle('index', 'Les sièges')
        ->setPaginatorPageSize('100')
        ->showEntityActionsInlined()
        ;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->setPermission(Action::DELETE, 'ROLE_STAFF')
        ->setPermission(Action::NEW, 'ROLE_STAFF')
        ->setPermission(Action::EDIT, 'ROLE_STAFF')
        ;
    }

}
