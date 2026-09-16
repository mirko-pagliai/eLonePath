<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 */

$this->extend('/common/panel');
$this->assign('title', 'Extended Title');
$this->start('sidebar');
?>Sidebar text.<?php $this->end(); ?>
Body text.