<?php

namespace App\Tests\Entity;

use App\Entity\Reactions;
use App\Entity\Messages;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class ReactionsTest extends TestCase
{
    public function testGetSetMessage()
    {
        $reaction = new Reactions();
        $message = new Messages();
        
        $reaction->setMessage($message);
        $this->assertSame($message, $reaction->getMessage());
    }

    public function testGetSetUser()
    {
        $reaction = new Reactions();
        $user = new Users();
        
        $reaction->setUser($user);
        $this->assertSame($user, $reaction->getUser());
    }

    public function testGetSetEmojiCode()
    {
        $reaction = new Reactions();
        $emoji = "😊";
        
        $reaction->setEmojiCode($emoji);
        $this->assertSame($emoji, $reaction->getEmojiCode());
    }
}
