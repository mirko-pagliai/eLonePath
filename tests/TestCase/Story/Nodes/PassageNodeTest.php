<?php
declare(strict_types=1);

namespace Test\Story\Nodes;

use App\Story\Nodes\Choice;
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
     * @link \App\Story\Nodes\PassageNode::getType()
     */
    #[Test]
    public function testType(): void
    {
        $node = new PassageNode(id: 1, gameId: 'test-game', content: '', choices: []);
        $this->assertSame(NodeType::PASSAGE, $node->getType());
    }

    /**
     * @link \App\Story\Nodes\PassageNode::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = PassageNode::createFromArray(id: 3, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'choices' => [
                ['content' => 'Go to page {{page}}', 'target' => 4],
                ['content' => 'Or go here', 'target' => 5],
            ],
            'type' => 'passage',
        ]);

        $this->assertSame(3, $node->id);
        $this->assertStringContainsString('Some content.', $node->content);

        $this->assertCount(2, $node->choices);
        $this->assertInstanceOf(Choice::class, $node->choices[0]);
        $this->assertSame('Go to page 4', $node->choices[0]->content);
        $this->assertSame('Or go here', $node->choices[1]->content);
    }

    /**
     * @link \App\Story\Nodes\PassageNode::createFromArray()
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
