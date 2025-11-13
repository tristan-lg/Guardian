<?php

namespace App\Controller\Admin;

use App\Entity\Analysis;
use App\Entity\Audit;
use App\Entity\Credential;
use App\Entity\NotificationChannel;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('@Admin/dashboard/dashboard.html.twig', [
            'projects' => $this->em->getRepository(Project::class)->getProjectOrderedByGrade(),
            'credentials' => $this->em->getRepository(Credential::class)->findAll(),
            'channels' => $this->em->getRepository(NotificationChannel::class)->findAll(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Guardian')
        ;
    }

    public function configureMenuItems(): iterable
    {
        $expiredCredentials = count($this->em->getRepository(Credential::class)->findExpired());
        $expiredChannels = count($this->em->getRepository(NotificationChannel::class)->findExpired());

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToRoute('Page publique', 'fa fa-eye', 'homepage');

        yield MenuItem::linkToCrud('Projets', 'fas fa-list', Project::class);

        yield MenuItem::linkToCrud('Analyses', 'fas fa-flask', Analysis::class);

        yield MenuItem::section('Gestion des audits');

        yield MenuItem::linkToCrud('Audits', 'fas fa-magnifying-glass', Audit::class);

        yield MenuItem::section('Gestion des accès');

        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);

        yield MenuItem::linkToCrud('Identifiants', 'fas fa-user-secret', Credential::class)
            ->setBadge(
                $expiredCredentials ? ($expiredCredentials . ' expirés') : 'OK',
                $expiredCredentials ? 'danger' : 'success'
            )
        ;

        yield MenuItem::linkToCrud('Notifications', 'fas fa-bell', NotificationChannel::class)
            ->setBadge(
                $expiredChannels ? ($expiredChannels . ' expirés') : 'OK',
                $expiredChannels ? 'danger' : 'success'
            )
        ;
    }
}
