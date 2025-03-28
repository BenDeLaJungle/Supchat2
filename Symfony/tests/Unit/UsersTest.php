<?php

namespace App\Tests\Entity;

use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    public function testGetSetFirstName()
    {
        $user = new Users();
        $user->setFirstName("John");

        $this->assertSame("John", $user->getFirstName());
    }

    public function testGetSetLastName()
    {
        $user = new Users();
        $user->setLastName("Doe");

        $this->assertSame("Doe", $user->getLastName());
    }

    public function testGetSetUserName()
    {
        $user = new Users();
        $user->setUserName("johndoe");

        $this->assertSame("johndoe", $user->getUserName());
    }

    public function testGetSetEmailAddress()
    {
        $user = new Users();
        $user->setEmailAddress("john.doe@example.com");

        $this->assertSame("john.doe@example.com", $user->getEmailAddress());
    }

    public function testGetSetPassword()
    {
        $user = new Users();
        $user->setPassword("hashed_password");

        $this->assertSame("hashed_password", $user->getPassword());
    }

    public function testGetSetRole()
    {
        $user = new Users();
        $user->setRole("ROLE_ADMIN");

        $this->assertSame("ROLE_ADMIN", $user->getRole());
    }

    public function testInvalidRoleThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = new Users();
        $user->setRole("INVALID_ROLE");
    }

    public function testGetSetTheme()
    {
        $user = new Users();
        $user->setTheme(true);

        $this->assertTrue($user->getTheme());
    }

    public function testGetSetStatus()
    {
        $user = new Users();
        $user->setStatus("online");

        $this->assertSame("online", $user->getStatus());
    }

    public function testGetSetOauthProvider()
    {
        $user = new Users();
        $user->setOauthProvider("google");

        $this->assertSame("google", $user->getOauthProvider());
    }

    public function testGetSetOauthID()
    {
        $user = new Users();
        $user->setOauthID("123456789");

        $this->assertSame("123456789", $user->getOauthID());
    }

    public function testGetRoles()
    {
        $user = new Users();
        $user->setRole("ROLE_USER");

        $this->assertSame(["ROLE_USER"], $user->getRoles());
    }

    public function testGetUserIdentifier()
    {
        $user = new Users();
        $user->setEmailAddress("john.doe@example.com");

        $this->assertSame("john.doe@example.com", $user->getUserIdentifier());
    }
}
