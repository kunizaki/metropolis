<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Database\Html $this */

$text = $this->getContainer()->get('language');
?>

<div class="alert alert-danger">
	<h3 class="fs-1 d-flex flex-column gap-3 align-items-center mb-4">
		<span class="fa-stack fa-2x" aria-hidden="true">
			<span class="fa-solid fa-circle fa-stack-2x text-white"></span>
			<span class="fa fa-database fa-stack-1x"></span>
			<span class="fa fa-ban fa-stack-2x text-danger"></span>
		</span>

		<span>
			<?= $text->text('DATABASE_NOCONNECTORS_LBL_HEAD') ?>
		</span>
	</h3>

	<p>
		<?= $text->sprintf('DATABASE_NOCONNECTORS_LBL_SUMMARY', PHP_VERSION) ?>
	</p>

	<p class="text-danger-emphasis small">
		<span class="fa fa-robot me-1" aria-hidden="true"></span>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_NOAI') ?>
	</p>

</div>

<p class="text-info">
	<span class="fa fa-info-circle me-1"></span>
	<?= $text->text('DATABASE_NOCONNECTORS_LBL_PHP_PER_VERSION_CONFIG') ?>
</p>

<h3 class="fs-5 mt-4 border-bottom mb-2">
	<?= $text->text('DATABASE_NOCONNECTORS_LBL_FAQ') ?>
</h3>

<details class="mb-2">
	<summary class="fs-6 fw-semibold mb-1">
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_STEP_HEAD') ?>
	</summary>

	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_STEP_NO') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_STEP_SPECIFIC') ?>
	</p>
</details>

<details class="mb-2">
	<summary class="fs-6 fw-semibold mb-1">
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_INERROR_HEAD') ?>
	</summary>

	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_INERROR_NO') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_INERROR_WHAT') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_INERROR_CONFUSION') ?>
	</p>
</details>

<details class="mb-2">
	<summary class="fs-6 fw-semibold mb-1">
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_USEOTHER_HEAD') ?>
	</summary>

	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_USEOTHER_NO') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_USEOTHER_WHY') ?>
	</p>
</details>

<details class="mb-2">
	<summary class="fs-6 fw-semibold mb-1">
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_REINSTALL_HEAD') ?>
	</summary>

	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_REINSTALL_NO') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_REINSTALL_WHY') ?>
	</p>
	<p>
		<?= $text->text('DATABASE_NOCONNECTORS_LBL_REINSTALL_INCOMPATIBILITY') ?>
	</p>
</details>