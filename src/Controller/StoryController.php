<?php
declare(strict_types=1);

namespace App\Controller;

use Elone\Core\Controller;
use RuntimeException;

class StoryController extends Controller
{
    /**
     * @param int $node
     * @return void
     * @link templates/story/chapter.php
     */
    public function chapter(string $storyId, int $nodeNumber): void
    {
        $dir = $this->getConfiguration()->rootPath() . "resources/stories/$storyId/";

        $contents = file_get_contents($dir . 'story.json');
        if ($contents === false) {
            throw new RuntimeException('Failed to read story.json');
        }

        $json = json_decode($contents);
        if (!is_object($json)) {
            throw new RuntimeException('Failed to parse story.json');
        }

        $game = $json->game;
        $node = $json->nodes->{$nodeNumber};

        $image = $dir . "img/$nodeNumber.jpg";
        if (is_readable($image)) {
            $image = "/assets/img/stories/$game->id/$nodeNumber.jpg";
        } else {
            $image = null;
        }

        $choices = $node->choices;
        foreach ($choices as $k => $choice) {
            $choices[$k]->text = str_replace(
                search: '{{page}}',
                replace: $choice->target,
                subject: $choice->text,
            );
        }

        $this->set([
            'content' => $node->content,
            'choices' => $choices,
            'game' => $game,
            'image' => $image,
            'page' => $nodeNumber,
        ]);
    }
}
