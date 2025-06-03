<?php

namespace App\Tests\Controller\Admin;

use App\Tests\WebTestCaseBase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class DashboardControllerTest extends WebTestCaseBase
{
    public function testAdminDashboardIsSuccessfulForAdminUser(): void
    {
        $client = $this->createAuthenticatedClient(
            username: 'testuser@example.com',
            password: 'password',
            loginPath: '/admin/login',
            serverParameters: ['HTTPS' => true]
        );

        $this->assertPageIsSuccessful($client, '/admin');
    }
}
