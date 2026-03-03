<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ChatConversationTest extends TestCase
{
    public function testInitialization(): void
    {
        $conv = new ChatConversation();

        $this->assertNotNull($conv->getCreatedAt());
        $this->assertNotNull($conv->getUpdatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $conv->getCreatedAt());
        $this->assertCount(0, $conv->getMessages());
    }

    public function testSetAndGetUser(): void
    {
        $conv = new ChatConversation();
        $user = new User();
        $user->setEmail('chat@example.com');

        $conv->setUser($user);

        $this->assertSame($user, $conv->getUser());
    }

    public function testSetUserNull(): void
    {
        $conv = new ChatConversation();
        $conv->setUser(null);

        $this->assertNull($conv->getUser());
    }

    public function testAddMessage(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Bonjour');

        $conv->addMessage($msg);

        $this->assertCount(1, $conv->getMessages());
        $this->assertSame($conv, $msg->getConversation());
    }

    public function testAddMessageNoDuplicate(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Hello');

        $conv->addMessage($msg);
        $conv->addMessage($msg);

        $this->assertCount(1, $conv->getMessages());
    }

    public function testGetMessageCount(): void
    {
        $conv = new ChatConversation();
        $msg1 = new ChatMessage($conv, 'user', 'Message 1');
        $msg2 = new ChatMessage($conv, 'assistant', 'Réponse 1');

        $conv->addMessage($msg1);
        $conv->addMessage($msg2);

        $this->assertSame(2, $conv->getMessageCount());
    }

    public function testGetPreviewWithUserMessage(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'user', 'Quel est le meilleur clavier ?');

        $conv->addMessage($msg);

        $this->assertSame('Quel est le meilleur clavier ?', $conv->getPreview());
    }

    public function testGetPreviewWithoutUserMessage(): void
    {
        $conv = new ChatConversation();
        $msg = new ChatMessage($conv, 'assistant', 'Je suis là pour vous aider');

        $conv->addMessage($msg);

        $this->assertSame('(vide)', $conv->getPreview());
    }

    public function testGetPreviewEmptyConversation(): void
    {
        $conv = new ChatConversation();

        $this->assertSame('(vide)', $conv->getPreview());
    }

    public function testTouch(): void
    {
        $conv = new ChatConversation();
        $before = $conv->getUpdatedAt();

        usleep(1000);
        $conv->touch();

        $this->assertGreaterThanOrEqual($before, $conv->getUpdatedAt());
    }

    public function testGetPreviewTruncatesLongMessage(): void
    {
        $conv = new ChatConversation();
        $longText = str_repeat('a', 100);
        $msg = new ChatMessage($conv, 'user', $longText);

        $conv->addMessage($msg);

        $preview = $conv->getPreview();
        $this->assertLessThanOrEqual(83, mb_strlen($preview));
    }
}
