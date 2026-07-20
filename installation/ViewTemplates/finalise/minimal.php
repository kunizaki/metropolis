<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Finalise\Html $this */

$text = $this->getContainer()->get('language');
$baseUri = $this->getContainer()->get('uri')->base();
?>

<?= $this->extra_warning ?>

<div class="alert <?= $this->showConfig ? 'alert-warning' : 'alert-success' ?>">
	<h3 class="alert-heading d-flex flex-column align-items-center gap-2 fs-1 mb-3">
		<?php if ($this->showConfig): ?>
			<div class="d-flex flex-row gap-5">
				<span class="fa fa-car-side fa-2x" aria-hidden="true"></span>
				<span class="d-none d-lg-inline ms-1" aria-hidden="true"></span>
				<span class="fa fa-flag-checkered text-muted fa-rotate-by" style="--fa-rotate-angle: 350deg;" aria-hidden="true"></span>
			</div>
			<span><?= $text->text('FINALISE_LBL_ALMOSTREADY') ?></span>
		<?php else: ?>
			<span class="fa fa-flag-checkered fa-2x" aria-hidden="true"></span>
			<?= $text->text('FINALISE_LBL_READY') ?>
		<?php endif ?>
	</h3>

	<?php if ($this->showConfig): ?>
	<div>
		<div class="badge bg-danger py-2">
			<span class="fa fa-exclamation-circle me-1" aria-hidden="true"></span>
			<?= $text->text('FINALISE_LBL_ACTION_REQUIRED') ?>
		</div>
		<span>
			<?= $text->sprintf('FINALISE_LBL_DONTFORGETCONFIG', basename($this->configFilename)) ?>
		</span>
	</div>
	<?php endif ?>
</div>

<?php if ($this->showConfig): ?>
<div class="card border-info mb-3">
	<h3 class="card-header fs-5 bg-info text-white">
		<span class="fa fa-file-code me-2" aria-hidden="true"></span>
		&#9312;
		<?= $text->text('FINALISE_HEADER_CONFIGURATION') ?>
	</h3>
	<div class="card-body">
		<p>
			<?= $text->sprintf('FINALISE_LBL_CONFIGINTRO', basename($this->configFilename)) ?>
		</p>
		<div class="border border-secondary rounded-2 bg-body-tertiary p-2 mb-3">
			<pre class="m-0" style="max-height: max(25vh, 300px)" id="rawConfigContents"><?= htmlentities($this->configuration) ?></pre>
			<button type="button" id="copyConfig"
			        class="btn btn-secondary mt-2 px-4 w-100"
			>
				<span class="fa fa-clipboard me-1"></span>
				<?= $text->text('FINALISE_LBL_COPYCONFIG') ?>
			</button>
		</div>
		<p>
			<?= $text->sprintf('FINALISE_LBL_CONFIGOUTRO', $this->configFilename) ?>
		</p>
	</div>
</div>
<?php endif; ?>

<div class="card mb-3 border-secondary">
	<h3 class="card-header fs-5">
		<span class="fa fa-shoe-prints me-2" aria-hidden="true"></span>
		<?php if($this->showConfig): ?>
		&#9313;
		<?php endif; ?>
		<?= $text->text('FINALISE_LBL_NEXT_STEPS') ?>
	</h3>

	<div class="card-body">
		<?php if($this->showConfig): ?>
		<p>
			<span class="fa fa-triangle-exclamation me-1" aria-hidden="true"></span>
			<?= $text->sprintf('FINALISE_LBL_MAKESURECONFIG', basename($this->configFilename)) ?>
		</p>
		<?php endif; ?>

		<p id="finaliseKickstart" class="d-none">
			<?= $text->text('FINALISE_LBL_KICKSTART') ?>
		</p>

		<p id="finaliseIntegrated" class="d-none">
			<?= $text->text('FINALISE_LBL_INTEGRATED') ?>
		</p>

		<div id="finaliseStandalone" class="d-none">
			<p>
				<?= $text->text('FINALISE_LBL_STANDALONE') ?>
			</p>
			<p>
				<button type="button" id="removeInstallation"
						class="btn btn-success btn-lg"
				>
					<span class="fa fa-trash me-1" aria-hidden="true"></span>
					<?= $text->text('FINALISE_BTN_REMOVEINSTALLATION') ?>
				</button>
			</p>
		</div>
	</div>
</div>

<div class="modal fade" id="error-dialog" tabindex="-1"
	 data-bs-backdrop="static" data-bs-keyboard="true"
	 aria-labelledby="error-dialog-head" aria-hidden="false"
>
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 85%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="error-dialog-head" class="modal-title">
					<?= $text->text('FINALISE_HEADER_ERROR') ?>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $text->text('GENERAL_LBL_CLOSE') ?>"></button>
			</div>
			<div class="modal-body">
				<p><?= $text->text('FINALISE_LBL_ERROR') ?></p>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="success-dialog" tabindex="-1"
	 data-bs-backdrop="static" data-bs-keyboard="true"
	 aria-labelledby="success-dialog-head" aria-hidden="false"
>
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 85%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 id="success-dialog-head" class="modal-title">
					<?= $text->text('FINALISE_HEADER_SUCCESS') ?>
				</h5>
			</div>
			<div class="modal-body">
				<p>
					<?= $text->sprintf('FINALISE_LBL_SUCCESS', 'https://www.akeeba.com/documentation/troubleshooter/prbasicts.html') ?>
				</p>
				<a class="btn btn-success" href="<?= $baseUri . '../index.php' ?>">
					<span class="fa fa-chevron-right" aria-hidden="true"></span>
					<?= $text->text('FINALISE_BTN_VISITFRONTEND') ?>
				</a>
			</div>
		</div>
	</div>
</div>


