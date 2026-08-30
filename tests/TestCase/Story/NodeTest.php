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
    public function testConstructWithoutExistingImage(): void
    {
        $node = new Node(
            id: 999999,
            gameId: 'no-such-game-id-in-webroot',
            content: 'Some content.',
            choices: [],
            type: NodeType::STORY,
            victory: null,
        );

        $this->assertNull($node->image);
    }

    /**
     * Relies on `webroot/assets/img/stories/` resolving relative to the current working directory when the test
     * runs — the same assumption `Node::__construct()` itself makes via `file_exists()`. Creates and removes a
     * throwaway image file under a game id that can't collide with real content.
     *
     * @link \App\Story\Node::__construct()
     */
    #[Test]
    public function testConstructWithExistingImage(): void
    {
        $gameId = 'node-test-fixture';
        $id = 1;
        $directory = "webroot/assets/img/stories/$gameId";

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents("$directory/$id.jpg", '');

        try {
            $node = new Node(
                id: $id,
                gameId: $gameId,
                content: 'Some content.',
                choices: [],
                type: NodeType::STORY,
                victory: null,
            );

            $this->assertSame("/assets/img/stories/$gameId/$id.jpg", $node->image);
        } finally {
            unlink("$directory/$id.jpg");
            rmdir($directory);
        }
    }

    /**
     * @link \App\Story\Node::createFromArray()
     */
    #[Test]
    public function testCreateFromArray(): void
    {
        $node = Node::createFromArray(id: 3, gameId: 'test-game', data: [
            'content' => 'Some content.',
            'choices' => [
                ['content' => 'Go to page {{page}}', 'target' => 4],
                ['content' => 'Or go here', 'target' => 5],
            ],
            'type' => 'story',
            'victory' => null,
        ]);

        $this->assertSame(3, $node->id);
        $this->assertStringContainsString('<p>Some content.</p>', $node->content);
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
    public function testCreateFromArrayWithVictory(): void
    {
        $node = Node::createFromArray(id: 10, gameId: 'test-game', data: [
            'content' => 'The end.',
            'choices' => [],
            'type' => 'ending',
            'victory' => true,
        ]);

        $this->assertSame(NodeType::ENDING, $node->type);
        $this->assertTrue($node->victory);
    }
}
