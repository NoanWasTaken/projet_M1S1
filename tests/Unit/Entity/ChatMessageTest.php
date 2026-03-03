<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use PHPUnit\Framework\TestCase;

class ChatMessageTest extends TestCase
{
    public function testConstructorSetsFields(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Bonjour');

        $this->assertSame($conv, $msg->getConversation());
        $this->assertSame('user', $msg->getRole());
        $this->assertSame('Bonjour', $msg->getContent());
        $this->assertNotNull($msg->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $msg->getCreatedAt());
    }

    public function testSetConversation(): void
    {
        $conv1 = new ChatConversation();
        $conv2 = new ChatConversation();
        $msg = new ChatMessage($conv1, 'assistant', 'Réponse');

        $msg->setConversation($conv2);

        $this->assertSame($conv2, $msg->getConversation());
    }

    public function testGetRole(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'assistant', 'Hello');

        $this->assertSame('assistant', $msg->getRole());
    }

    public function testGetContent(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Ma question');

        $this->assertSame('Ma question', $msg->getContent());
    }

    public function testIdIsNullByDefault(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Test');

        $this->assertNull($msg->getId());
    }
}
