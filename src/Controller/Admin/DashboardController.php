<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use App\Entity\MovieTheater;
use App\Entity\ProjectionEvent;
use App\Entity\ProjectionFormat;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Entity\TicketCategory;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    // #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(MovieCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Tableau de bord')
            ->setFaviconPath('/favicon.png')
            ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            // MenuItem::subMenu('Film & séances', 'fa fa-ticket')->setSubItems([
            // ]),
            // MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToCrud('Films', 'fa fa-clapperboard', Movie::class),
            MenuItem::linkToCrud('Séances', 'fa fa-film', ProjectionEvent::class),
            MenuItem::linkToCrud('Réservations', 'fa fa-ticket', Reservation::class),
            MenuItem::linkToCrud('Catégories de projection', 'fa fa-bars', ProjectionFormat::class),
            MenuItem::linkToCrud('Cinémas', 'fa fa-location-dot', MovieTheater::class),
            MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class),
            MenuItem::linkToCrud('Tarifs', 'fa fa-money-bill', TicketCategory::class),
            MenuItem::linkToCrud('Billets', 'fa fa-ticket', Ticket::class),
        ];
    }

    // public function configureActions(Actions $actions): Actions
    // {
    //     return parent::configureActions($actions)
    //     ->update(Crud::PAGE_INDEX, Action::NEW, fn ($action) => $action->setLabel('Ajouter une categorie de projection'))
    //     ;
    // }
}
