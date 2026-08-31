<?php
declare(strict_types=1);

namespace Test\Story;

use App\Story\Choice;
use App\Story\Nodes\NodeType;
use App\Story\Nodes\PassageNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * PassageNodeTest.
 */
#[CoversClass(PassageNode::class)]
class PassageNodeTest extends TestCase
{
    /**
     * @link \App\Story\Nodes\Node::__construct()
     */
    #[Test]
    public function testConstructTransformsContentAsMarkdown(): void
    {
        $node = new PassageNode(
            id: 1,
            gameId: 'test-game',
            content: 'Some content.',
            image: null,
            choices: [],
        );

        $this->assertStringContainsString('<p>Some content.</p>', $node->content);
    }

    /**
     * @link \App\Story\Nodes\Node::__construct()
     */
    #[Test]
    public function testConstructStoresImage(): void
    {
        $node = new PassageNode(
            id: 1,
            gameId: 'test-game',
            content: 'Some content.',
            image: ['path' => 'cover.jpg', 'title' => 'Cover art'],
            choices: [],
        );

        $this->assertSame(['path' => 'cover.jpg', 'title' => 'Cover art'], $node->image);
    }

    /**
     * @link \App\Story\PassageNode::type()
     */
    #[Test]
    public function testType(): void
    {
        $node = new PassageNode(id: 1, gameId: 'test-game', content: '', image: null, choices: []);
        $this->assertSame(NodeType::PASSAGE, $node->type());
    }

    /**
     * @link \App\Story\PassageNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = PassageNode::createFromArray(id: 3, gameId: 'test-game', data: [
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

        $this->assertCount(2, $node->choices);
        $this->assertInstanceOf(Choice::class, $node->choices[0]);
        $this->assertSame('Go to page 4', $node->choices[0]->content);
        $this->assertSame('Or go here', $node->choices[1]->content);
    }

    /**
     * @link \App\Story\PassageNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithoutImageKey(): void
    {
        $node = PassageNode::createFromArray(id: 3, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'choices' => [],
            'type' => 'passage',
        ]);

        $this->assertNull($node->image);
    }

    /**
     * @link \App\Story\PassageNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArrayWithoutChoicesKey(): void
    {
        $node = PassageNode::createFromArray(id: 1, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'type' => 'passage',
        ]);

        $this->assertSame([], $node->choices);
    }
}
