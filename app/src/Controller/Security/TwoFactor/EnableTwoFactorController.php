<?php

namespace App\Controller\Security\TwoFactor;

use App\Entity\User;
use App\Form\EnableTwoFactorFormType;
use App\Service\Security\TwoFactorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/activation-2fa')]
class EnableTwoFactorController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService,
    ) {}

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('', name: 'app_2fa_enable')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isTotpAuthenticationEnabled()) {
            return $this->redirectToRoute('homepage');
        }

        $user->setTotpSecret($this->twoFactorService->generateTwoFactorSecret());
        $form = $this->createForm(EnableTwoFactorFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $topSecretToken */
            $topSecretToken = $user->getTotpSecret();
            $this->twoFactorService->setUserTwoFactorSecret($user, $topSecretToken);

            // Enable 2FA
            $this->addFlash('success', 'La double authentification a bien été activée');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('security/two-factor/enable-2fa.html.twig', [
            'form' => $form,
            'qrCode' => $this->twoFactorService->getQrCodeContent($user),
            'secretCode' => $user->getTotpSecret(),
        ]);
    }
}