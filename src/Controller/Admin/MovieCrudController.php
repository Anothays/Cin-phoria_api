<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class MovieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string { return Movie::class; }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addTab('Informations'),
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextField::new("director", 'Réalisateur'),
            ArrayField::new('casting')->hideOnIndex(),
            NumberField::new('minimumAge', 'Age')->hideOnIndex(),
            NumberField::new('durationInMinutes', 'durée (en minutes)')->hideOnIndex(),
            NumberField::new('AverageNote', 'Note sur 5')->hideOnForm(),
            TextEditorField::new('synopsis')->hideOnIndex(),
            DateField::new('createdAt', 'Rajouté le'),
            DateField::new('releasedOn', 'Sortie le'),
            BooleanField::new('staffFavorite', 'En favoris'),
            TextField::new('coverImageFile', 'Image')->setFormType(VichImageType::class)->onlyOnForms(),
            ImageField::new('coverImageName', 'Image')->setBasePath('/uploads/images')->onlyOnIndex(),
            AssociationField::new('comments', 'Commentaires')->onlyOnDetail(),
            FormField::addTab('Séances')->hideWhenCreating(),
            AssociationField::new('projectionEvents', 'séances')
            ->hideWhenCreating(),

        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Film')
        ->setEntityLabelInPlural('Films')
        ->setPageTitle('index', 'Les films')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter un film'))
        ;
    }
}
