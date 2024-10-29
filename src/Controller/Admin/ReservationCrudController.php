<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ActionConfigDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Réservation')
        ->setEntityLabelInPlural('Réservations')
        ->setPageTitle('index', 'Les réservations')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', "ID")->onlyOnIndex(),
            MoneyField::new('totalPrice', 'Prix total')->setCurrency('EUR'),
            AssociationField::new('projectionEvent', 'Séance')->hideOnForm(),
            DateTimeField::new('createdAt', 'Émise le'),
            BooleanField::new('isPaid', 'Payé')->renderAsSwitch(false),
            BooleanField::new('hasRate', 'comm'),
            AssociationField::new('user', 'Par')->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        // ->add(Crud::PAGE_INDEX, Action::DETAIL)
        // ->disable(Action::NEW)
        // ->disable(Action::EDIT)
        ;
    }

}
