<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use App\Entity\ProjectionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectionEventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return ProjectionEvent::class; }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            AssociationField::new('movie', 'Film'),
            DateField::new('beginAt', 'Date')->setFormat('short'),
            DateField::new('beginAt', 'Heure')->setFormat('HH:mm'),
            DateTimeField::new('beginAt', 'Début de séance')->onlyOnForms(),
            TextField::new('movieTheater', 'Cinéma')->hideOnForm(),
            AssociationField::new('projectionRoom', 'Salle'),
            ChoiceField::new('language', 'Version'),
            AssociationField::new('format'),
            AssociationField::new('reservations', 'Places vendues')->hideOnForm(),
            NumberField::new('availableSeatsCount', 'Places restantes')->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Séance')
        ->setEntityLabelInPlural('Séances')
        ->setPageTitle('index', 'Les séances')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Nouvelle séance'))
        ;
    }
}
