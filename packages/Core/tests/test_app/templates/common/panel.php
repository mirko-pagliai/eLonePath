<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 */
?>

<h1><?= h($this->fetch('title')) ?></h1>
<?= $this->fetch('content') ?>
<aside><?= $this->fetch('sidebar') ?></aside>
