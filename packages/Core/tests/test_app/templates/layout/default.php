<?php
declare(strict_types=1);

/**
 * @var string $content
 */

/**
 * The `id` marker is what a test checks for to prove the layout actually wrapped the content — rendering without
 * a layout produces bare content, with no wrapper at all.
 */
?>

<div id="test-layout"><?= $content ?></div>
