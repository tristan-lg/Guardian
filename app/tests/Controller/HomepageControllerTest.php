<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCaseBase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class HomepageControllerTest extends WebTestCaseBase
{
    public function testHomepageIsSuccessful(): void
    {
        $client = static::createClient([], [
            'HTTPS' => true,
        ]);
        $this->assertPageIsSuccessful($client, '/');
    }
}
