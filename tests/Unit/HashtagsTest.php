<?php

namespace App\Tests\Entity;

use App\Entity\Hashtags;
use App\Entity\Channels;
use App\Entity\Messages;
use PHPUnit\Framework\TestCase;

class HashtagsTest extends TestCase
{
    public function testGetSetChannel()
    {
        $hashtags = new Hashtags();
        $channel = new Channels();

        $hashtags->setChannel($channel);

        $this->assertSame($channel, $hashtags->getChannel());
    }

    public function testGetSetMessage()
    {
        $hashtags = new Hashtags();
        $message = new Messages();

        $hashtags->setMessage($message);

        $this->assertSame($message, $hashtags->getMessage());
    }

    public function testInitialValuesAreNull()
    {
        $hashtags = new Hashtags();

        $this->assertNull($hashtags->getId());
        $this->assertNull($hashtags->getChannel());
        $this->assertNull($hashtags->getMessage());
    }
}
