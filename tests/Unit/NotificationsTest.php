<?php

namespace App\Tests\Entity;

use App\Entity\Notifications;
use App\Entity\Messages;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class NotificationsTest extends TestCase
{
    public function testGetSetMessage()
    {
        $notification = new Notifications();
        $message = new Messages();
        
        $notification->setMessage($message);
        $this->assertSame($message, $notification->getMessage());
    }

    public function testGetSetUser()
    {
        $notification = new Notifications();
        $user = new Users();
        
        $notification->setUser($user);
        $this->assertSame($user, $notification->getUser());
    }

    public function testGetSetAtRead()
    {
        $notification = new Notifications();
        
        $notification->setAtRead(true);
        $this->assertTrue($notification->getAtRead());

        $notification->setAtRead(false);
        $this->assertFalse($notification->getAtRead());
    }

    public function testInvalidAtReadThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $notification = new Notifications();
        $notification->setAtRead("invalid_value"); // Doit lancer une exception
    }
}
