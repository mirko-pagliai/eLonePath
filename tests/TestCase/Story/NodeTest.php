<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Choice;
use App\Story\Node;
use App\Story\NodeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * NodeTest.
 */
#[CoversClass(Node::class)]
class NodeTest extends TestCase
{
    /**
     * @link \App\Story\Node::__construct()
     */
    #[Test]
    public function testConstructTransformsContentAsMarkdown(): void
    {
        $node = new Node(
            id: 1,
            gameId: 'test-game',
            content: 'Some content.',
            image: null,
            choices: [],
            type: NodeType::PASSAGE,
        );

        $this->assertStringContainsString('<p>Some content.</p>', $node->content);
    }

    /**
     * @link \App\Story\Node::__construct()
     */
    #[Test]
    public function testConstructStoresImage(): void
    {
        $node = new Node(
            id: 1,
            gameId: 'test-game',
            content: 'Some content.',
            image: ['path' => 'cover.jpg', 'title' => 'Cover art'],
            choices: [],
            type: NodeType::PASSAGE,
        );

        $this->assertSame(['path' => 'cover.jpg', 'title' => 'Cover art'], $node->image);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = Node::createFromArray(id: 3, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'image' => null,
            'choices' => [
                ['content' => 'Go to page {{page}}', 'target' => 4],
                ['content' => 'Or go here', 'target' => 5],
            ],
            'type' => 'passage',
        ]);

        $this->assertSame(3, $node->id);
        $this->assertStringContainsString('<p>Some content.</p>', $node->content);
        $this->assertNull($node->image);
        $this->assertSame(NodeType::PASSAGE, $node->type);

        $this->assertCount(2, $node->choices);
        $this->assertInstanceOf(Choice::class, $node->choices[0]);
        $this->assertSame('Go to page 4', $node->choices[0]->content);
        $this->assertSame('Or go here', $node->choices[1]->content);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithoutImageKey(): void
    {
        $node = Node::createFromArray(id: 3, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'choices' => [],
            'type' => 'passage',
        ]);

        $this->assertNull($node->image);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithoutChoicesKey(): void
    {
        $node = Node::createFromArray(id: 12, gameId: 'test-game', data: [
            'content' => 'The end.',
            'type' => 'defeat',
        ]);

        $this->assertSame([], $node->choices);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithVictoryType(): void
    {
        $node = Node::createFromArray(id: 10, gameId: 'test-game', data: [
            'content' => 'The end.',
            'image' => null,
            'choices' => [],
            'type' => 'victory',
        ]);

        $this->assertSame(NodeType::VICTORY, $node->type);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithDefeatType(): void
    {
        $node = Node::createFromArray(id: 11, gameId: 'test-game', data: [
            'content' => 'The end.',
            'image' => null,
            'choices' => [],
            'type' => 'defeat',
        ]);

        $this->assertSame(NodeType::DEFEAT, $node->type);
    }
}
