<?php

namespace App\Tests\Unit;

use App\Entity\Workspaces;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkspacesTest extends KernelTestCase
{
    public function testWorkspaceValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $workspace = new Workspaces();

        $creator = new Users();

        $workspace->setName("My Workspace")
            ->setStatus(true)
            ->setCreator($creator);

        $errors = $container->get('validator')->validate($workspace);
        $this->assertCount(0, $errors);
    }

    public function testWorkspaceNameError(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $workspace = new Workspaces();

        $creator = new Users();

        $workspace->setName("") 
            ->setStatus(true)
            ->setCreator($creator);

        $errors = $container->get('validator')->validate($workspace);

        $this->assertGreaterThanOrEqual(1, count($errors));
    }
}
