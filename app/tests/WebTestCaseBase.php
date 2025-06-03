<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User; // Assuming User entity exists for login

class WebTestCaseBase extends WebTestCase
{
    protected function createAuthenticatedClient(
        string $username = 'testuser@example.com',
        string $password = 'password',
        string $loginPath = '/login',
        array $serverParameters = []
    ): KernelBrowser
    {
        $client = static::createClient([], $serverParameters);

        // It's common to fetch the user entity from the database first
        // to ensure the user exists, or to create one if necessary.
        // For simplicity here, we'll assume the user exists.
        // $userRepository = static::getContainer()->get(UserRepository::class);
        // $testUser = $userRepository->findOneByEmail($username);

        // Simulate logging in - details depend on your authentication setup
        $crawler = $client->request('GET', $loginPath);

        // Check if the response is a redirect, possibly due to already being logged in
        // or other redirection logic on the login page itself.
        if ($client->getResponse()->isRedirect()) {
            $crawler = $client->followRedirect();
            // After redirect, check if we landed on the expected page or if the form is even there
            // This part might need adjustment based on application flow.
            // For now, we'll try to find the form if it exists.
        }

        // Attempt to find the login form. Handle cases where it might not be present.
        $formNode = $crawler->selectButton('Connexion');
        if ($formNode->count() === 0) {
            // If the "Connexion" button is not found, perhaps the user is already authenticated
            // or the login page structure is different.
            // For now, we'll return the client as is. Further checks could be added here.
            // For example, assert that we are on the target page after an attempted login.
            return $client;
        }

        $form = $formNode->form([
            '_username' => $username, // Default Symfony field name
            '_password' => $password, // Default Symfony field name
        ]);
        $client->submit($form);

        // Follow redirect only if there is one. Some successful logins might not redirect.
        if ($client->getResponse()->isRedirect()) {
            $client->followRedirect();
        }

        return $client;
    }

    protected function assertPageIsSuccessful(KernelBrowser $client, string $url): void
    {
        $client->request('GET', $url);
        $this->assertResponseIsSuccessful(sprintf('The page "%s" should be successful.', $url));
    }

    // Helper to get a user if needed, adjust as per your user provider
    protected function getUser(string $email): ?User
    {
        if (!static::$booted) {
            static::bootKernel();
        }
        $container = static::getContainer();
        return $container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => $email]);
    }
}
