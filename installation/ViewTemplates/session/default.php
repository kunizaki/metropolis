<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Session\Html $this */

$lang = $this->getContainer()->get('language');
?>

<div class="fs-3 mt-5 text-center">
	<span class="fa-stack fa-2x" aria-hidden="true">
	  <span class="fa fa-pencil fa-stack-1x"></span>
	  <span class="fa fa-ban fa-stack-2x text-danger"></span>
	</span>
</div>
<h2 class="alert-heading text-center mt-3 mb-4">
	<?= $lang->text('SESSION_LBL_HEAD') ?>
</h2>

<hr class="my-5">

<p><?= $lang->text('SESSION_LBL_MAINMESSAGE') ?></p>

<p class="text-success-emphasis">
	<?= $lang->text('SESSION_LBL_FIX') ?>
</p>