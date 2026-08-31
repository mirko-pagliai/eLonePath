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
            type: NodeType::STORY,
            victory: null,
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
            type: NodeType::STORY,
            victory: null,
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
            'type' => 'story',
            'victory' => null,
        ]);

        $this->assertSame(3, $node->id);
        $this->assertStringContainsString('<p>Some content.</p>', $node->content);
        $this->assertNull($node->image);
        $this->assertSame(NodeType::STORY, $node->type);
        $this->assertNull($node->victory);

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
            'type' => 'story',
            'victory' => null,
        ]);

        $this->assertNull($node->image);
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithVictory(): void
    {
        $node = Node::createFromArray(id: 10, gameId: 'test-game', data: [
            'content' => 'The end.',
            'image' => null,
            'choices' => [],
            'type' => 'ending',
            'victory' => true,
        ]);

        $this->assertSame(NodeType::ENDING, $node->type);
        $this->assertTrue($node->victory);
    }
}
