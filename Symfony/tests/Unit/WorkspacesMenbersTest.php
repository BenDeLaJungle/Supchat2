<?php

namespace App\Tests\Unit;

use App\Entity\WorkspaceMembers;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Workspaces;
use App\Entity\Users;
use App\Entity\Roles;

class WorkspacesMenbersTest extends KernelTestCase
{
    private function createValidWorkspaceMember(): WorkspaceMembers
    {
        $workspace = new Workspaces();
        $user = new Users();
        $role = new Roles();

        $workspaceMember = new WorkspaceMembers();
        $workspaceMember->setWorkspace($workspace)
            ->setUser($user)
            ->setRole($role)
            ->setPublish(true)
            ->setModerate(false)
            ->setManage(true);

        return $workspaceMember;
    }

    public function testWorkspaceMembersValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $workspaceMember = $this->createValidWorkspaceMember();

        $errors = $container->get('validator')->validate($workspaceMember);
        $this->assertCount(0, $errors);
    }

    public function testWorkspaceMembersRelationErrors(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $workspaceMember = new WorkspaceMembers();

        $workspaceMember->setPublish(true)
            ->setModerate(false)
            ->setManage(true);

        $errors = $container->get('validator')->validate($workspaceMember);
        
        $this->assertCount(3, $errors);
    }
}
