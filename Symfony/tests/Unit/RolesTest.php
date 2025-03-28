<?php

namespace App\Tests\Unit;

use App\Entity\Roles;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RolesTest extends KernelTestCase
{
    private function createValidRole(): Roles
    {
        $role = new Roles();
        $role->setName("Testrole")
            ->setPublish(true)
            ->setManage(true)
            ->setModerate(true);

        return $role;
    }

    public function testRolesValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $role = $this->createValidRole();

        $errors = $container->get('validator')->validate($role);
        $this->assertCount(0, $errors);
    }

    public function testRolesNameError(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $role = $this->createValidRole(); // Réutilise l'entité de base
        $role->setName(3); // Injecte l'erreur

        $errors = $container->get('validator')->validate($role);
        $this->assertCount(1, $errors);
    }
}
