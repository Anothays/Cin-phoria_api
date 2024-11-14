<?php

namespace App\Controller\Admin;

use App\Entity\Incident;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class IncidentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Incident::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Incident')
        ->setEntityLabelInPlural('Incidents')
        ->setPageTitle('index', 'Les incidents')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextEditorField::new('description'),
            DateField::new('createdAt', 'Rajouté le'),
            BooleanField::new('resolved', 'Traité'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        // ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter un film'))
        ->remove(Crud::PAGE_INDEX, Action::EDIT)
        ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;
    }

}
