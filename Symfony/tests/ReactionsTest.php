<?php

namespace App\Tests\Entity;

use App\Entity\Reactions;
use App\Entity\Messages;
use App\Entity\Users;
use PHPUnit\Framework\TestCase;

class ReactionsTest extends TestCase
{
    public function testReactionCanBeCreated(): void
    {
        $user = new Users();  // Vérifie que Users peut être instancié sans arguments
        $message = new Messages();  // Vérifie que Messages peut être instancié sans arguments
        $reaction = new Reactions();

        $reaction->setUser($user);
        $reaction->setMessage($message);
        $reaction->setEmojiCode('👍');

        $this->assertSame($user, $reaction->getUser());
        $this->assertSame($message, $reaction->getMessage());
        $this->assertEquals('👍', $reaction->getEmojiCode()); // assertEquals() est plus sûr ici
    }

    public function testReactionHasNoIdInitially(): void
    {
        $reaction = new Reactions();
        $this->assertNull($reaction->getId(), "L'ID devrait être null avant persistance en base.");
    }
}

