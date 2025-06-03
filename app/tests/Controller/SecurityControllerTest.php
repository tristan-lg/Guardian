<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCaseBase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SecurityControllerTest extends WebTestCaseBase
{
    public function testAdminLoginPageIsSuccessful(): void
    {
        $client = static::createClient([], ['HTTPS' => true]);
        $this->assertPageIsSuccessful($client, '/admin/login');
    }

    public function testUnauthenticatedUserIsRedirectedToLogin(): void
    {
        $client = static::createClient([], ['HTTPS' => true]);
        $client->request('GET', '/admin');

        $this->assertResponseRedirects(
            '/admin/login',
            302,
            'Unauthenticated user accessing /admin should be redirected to /admin/login.'
        );
    }

    public function testLogoutIsSuccessful(): void
    {
        $client = $this->createAuthenticatedClient(
            username: 'testuser@example.com',
            password: 'password',
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        // Make a request to an authenticated page first to ensure session is active
        $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful("Admin page should be accessible before logout.");

        // Go to the logout URL
        // Symfony's default logout path name is 'app_logout', usually configured at /logout
        // We need to find the actual path if it's different. Assuming /logout for now.
        // EasyAdmin might have its own specific logout mechanism/path under /admin.
        // Let's try the default /logout first.
        $logoutUrl = '/logout';
        // If EasyAdmin handles logout, it might be something like:
        // $logoutUrl = $client->getContainer()->get('router')->generate('admin', ['crudAction' => 'logout']);
        // However, Symfony's security system usually defines a global /logout.

        $client->request('GET', $logoutUrl);

        // Assert that the logout redirects (usually to the homepage or login page)
        // The default target after logout is often the homepage '/'
        $this->assertResponseRedirects(
            '/', // Assuming redirect to homepage. Adjust if different (e.g., /admin/login)
            302,
            "Logout should redirect."
        );

        // Follow the redirect
        $client->followRedirect();

        // Try to access an authenticated page again
        $client->request('GET', '/admin');

        // Assert that we are redirected to the login page
        $this->assertResponseRedirects(
            '/admin/login',
            302,
            "Accessing /admin after logout should redirect to /admin/login."
        );
    }
}
