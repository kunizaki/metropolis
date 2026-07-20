<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Main\Html $this */
$text = $this->getContainer()->get('language');

?>

<?php if ($this->meetsRecommended && $this->meetsRequired) : ?>
	<div class="alert alert-success text-center" role="alert">
		<span class="fa fa-check-circle fa-3x text-success" aria-hidden="true"></span>
		<h1 class="my-2">
			<?= $text->text('MAIN_LBL_OVERVIEW_READY') ?>
		</h1>
		<p>
			<?= $text->text('MAIN_LBL_OVERVIEW_ALL_REQUIREMENTS_MET') ?>
		</p>
		<button class="btn btn-outline-primary btn-lg nextStep">
			<span class="fas fa-chevron-right" aria-hidden="true"></span>
			<?= $text->text('MAIN_LBL_OVERVIEW_PROCEED_BUTTON') ?>
		</button>
	</div>
<?php elseif ($this->meetsRequired) : ?>
	<div class="alert alert-success text-center" role="alert">
		<span class="fa fa-check-circle fa-3x" aria-hidden="true"></span>
		<h1 class="mt-2 mb-0">
			<?= $text->text('MAIN_LBL_OVERVIEW_READY') ?>
		</h1>
		<p class="mt-0 mb-2 fs-2 text-info">
			<?= $text->text('MAIN_LBL_OVERVIEW_READY_NOTES') ?>
		</p>
		<p>
			<?= $text->text('MAIN_LBL_OVERVIEW_SOME_REQUIREMENTS_MET') ?>
		</p>
		<button class="btn btn-outline-primary btn-lg nextStep">
			<span class="fas fa-chevron-right" aria-hidden="true"></span>
			<?= $text->text('MAIN_LBL_OVERVIEW_PROCEED_BUTTON') ?>
		</button>
	</div>
<?php else : ?>
	<div class="alert alert-danger text-center" role="alert">
		<span class="fa fa-exclamation-circle fa-3x" aria-hidden="true"></span>
		<h1 class="my-2">
			<?= $text->text('MAIN_LBL_OVERVIEW_NOT_READY') ?>
		</h1>
		<p>
			<?= $text->text('MAIN_LBL_OVERVIEW_NO_REQUIREMENTS_MET') ?>
		</p>
		<p>
			<?= $text->text('MAIN_LBL_OVERVIEW_PROCEED_AT_RISK') ?>
		</p>
		<button class="btn btn-outline-secondary btn-lg nextStep">
			<span class="fas fa-chevron-right" aria-hidden="true"></span>
			<?= $text->text('MAIN_LBL_OVERVIEW_PROCEED_BUTTON') ?>
		</button>
	</div>
<?php endif; ?>
