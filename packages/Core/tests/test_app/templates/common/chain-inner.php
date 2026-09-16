<?php
declare(strict_types=1);

/**
 * @var \Elone\Core\View\View $this
 */

$this->extend('/common/chain-outer');
?>
[INNER]<?= $this->fetch('content') ?>[/INNER]
