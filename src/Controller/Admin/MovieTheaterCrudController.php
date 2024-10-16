<?php

namespace App\Controller\Admin;

use App\Entity\MovieTheater;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MovieTheaterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MovieTheater::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('theaterName', 'Cinéma'),
            TextField::new('city', 'Ville'),
            AssociationField::new('projectionRooms', 'Nombre de salles')->hideOnForm(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Cinéma')
        ->setEntityLabelInPlural('Cinémas')
        ->setPageTitle('index', 'Les cinémas')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter un cinéma'))
        ;
    }

}
