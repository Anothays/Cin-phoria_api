<?php

namespace App\Controller\Admin;

use App\Entity\ProjectionFormat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProjectionFormatCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return ProjectionFormat::class; }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('projectionFormatName', 'Nom'),
            // TextField::new("director", 'Réalisateur'),
            // ArrayField::new('casting')->hideOnIndex(),
            MoneyField::new('extraCharge', 'coût additionnel')->setCurrency('EUR'),
            // NumberField::new('AverageNote', 'Note sur 10')->hideOnIndex(),
            // TextEditorField::new('synopsis')->hideOnIndex(),
            AssociationField::new('projectionEvents', 'séances concernées'),
            // DateField::new('createdAt', 'Rajouté le'),
            // DateField::new('releasedOn', 'Sortie le'),
            // BooleanField::new('isStaffFavorite', 'En favoris'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Categorie de projection')
        ->setEntityLabelInPlural('Categories de projection')
        ->setPageTitle('index', 'Categories de projection')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter une categorie de projection'))
        ;
    }
    
}
