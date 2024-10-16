<?php

namespace App\Controller\Admin;

use App\Entity\TicketCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TicketCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TicketCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setEntityLabelInSingular('Tarif')
        ->setEntityLabelInPlural('Tarifs')
        ->setPageTitle('index', 'Les tarifs')
        ->setPaginatorPageSize('20')
        ->showEntityActionsInlined()
        ->setDefaultSort(["price" => "DESC"])
        ;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('categoryName', 'Nom de la catégorie'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
        ->setPermissions([
            Action::DELETE, 'ROLE_ADMIN'
        ])
        ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter un tarif'))
        ;
    }
}
