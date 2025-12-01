<?php

namespace App\Controller\Security;

use App\Form\ChangePasswordFormType;
use App\Service\Security\Login\FirstLoginService;
use App\Service\Security\UpdatePasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\Exception\ExpiredLoginLinkException;
use Symfony\Component\Security\Http\LoginLink\Exception\InvalidLoginLinkException;

class FirstLoginController extends AbstractController
{
    public function __construct(
        private readonly FirstLoginService $firstLoginService,
        private readonly UpdatePasswordService $resetPasswordService,
        private readonly Security $security,
    ) {}

    #[Route(path: '/premiere-connexion', name: 'app_first_login')]
    public function firstLogin(Request $request): Response
    {
        try {
            $user = $this->firstLoginService->consumeFirstLoginLink($request);
        } catch (ExpiredLoginLinkException|InvalidLoginLinkException) {
            $this->addFlash('error',
                'Le lien a déjà été utilisé ou a expiré. Si vous avez oublié votre mot de passe, vous pouvez le reinitialiser avec "Mot de passe oublié".'
            );

            return $this->redirectToRoute('app_login');
        }

        // Render the password change form
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode(hash) the plain password, and set it.
            $this->resetPasswordService->setNewPassword($user, $plainPassword);

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès');

            // Login the user
            $this->security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('security/first-login/init.html.twig', [
            'user' => $user,
            'resetForm' => $form,
        ]);
    }
}
