<?php
declare(strict_types=1);

/**
 * Minimal fixture, used only by `ViewTest::testRenderKeepsDataAvailableToHelpersDuringEvaluation()` — it calls a
 * helper (`$this->Probe`) that reads `get()` on the view rendering it, from *inside* that same render, the exact
 * thing the regression test locks in.
 */
?>

<?= $this->Probe->readState() ?>

