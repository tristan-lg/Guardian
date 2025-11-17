<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EntityManagerInterface $em, #[Autowire('%app.homepage_require_login%')] bool $homepageRequireLogin): Response
    {
        if ($homepageRequireLogin) {
            $this->denyAccessUnlessGranted('ROLE_USER');
        }

        return $this->render('@Pages/homepage.html.twig', [
            'projects' => $em->getRepository(Project::class)->getProjectOrderedByGrade(),
        ]);
    }
}
