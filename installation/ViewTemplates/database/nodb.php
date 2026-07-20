<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Database\Html $this */

$text = $this->getContainer()->get('language');
?>

<div class="alert alert-info">
	<h3 class="fs-1 d-flex flex-column gap-3 align-items-center mb-4">
		<span class="fa-stack fa-2x" aria-hidden="true">
			<span class="fa-solid fa-circle fa-stack-2x text-white"></span>
			<span class="fa fa-database fa-stack-1x text-body-tertiary"></span>
			<span class="fa fa-ban fa-stack-2x text-secondary fa-rotate-90"></span>
		</span>

		<span>
			<?= $text->text('DATABASE_NODB_LBL_HEAD') ?>
		</span>
	</h3>

	<p>
		<?php if ($this->key === 'site'): ?>
		<?= $text->text('DATABASE_NODB_LBL_BODY_MAIN') ?>
		<?php else: ?>
		<?= $text->sprintf('DATABASE_NODB_LBL_BODY', $this->key) ?>
		<?php endif ?>
	</p>
</div>

