<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Offsitedirs\Html $this */

$text  = $this->getContainer()->get('language');
$paths = $this->getContainer()->get('paths');
?>
<div id="restoration-dialog" class="modal fade" aria-hidden="true"
	 data-bs-backdrop="static" data-bs-keyboard="false"
	 aria-labelledby="restoration-dialog-head" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 85%">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="restoration-dialog-head"><?= $text->text('OFFSITEDIRS_HEADER_COPY') ?></h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div id="restoration-progress" class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Progress">
					<div class="progress-bar" id="restoration-progress-bar" style="width: 0;"></div>
				</div>
				<div id="restoration-success" class="mt-3">
					<div class="alert alert-success" role="alert">
						<?= $text->text('OFFSITEDIRS_HEADER_SUCCESS') ?>
					</div>
					<p>
						<?= $text->text('OFFSITEDIRS_MSG_SUCCESS') ?>
					</p>
					<button type="button" class="btn btn-success" id="btnSuccessAndNext">
						<span class="fa fa-chevron-right"></span>
						<?= $text->text('OFFSITEDIRS_BTN_SUCCESS') ?>
					</button>
				</div>
				<div id="restoration-error" class="mt-3">
					<div class="alert alert-danger">
						<?= $text->text('OFFSITEDIRS_HEADER_ERROR') ?>
					</div>
					<div class="my-2" id="restoration-lbl-error"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<h3 class="fs-4"><?= $text->sprintf('OFFSITEDIRS_HEADER_MASTER', $this->substep->virtual) ?></h3>

<div class="alert alert-info">
	<?= $text->sprintf('OFFSITEDIRS_LBL_EXPLANATION', $this->substep->name, $this->substep->virtual, $paths->get('site')) ?>
</div>

<div class="card border-info">
	<h4 class="card-header bg-info text-white">
		<?= $text->text('OFFSITEDIRS_FOLDER_DETAILS') ?>
	</h4>

	<div class="card-body">
		<div class="row mb-3">
			<label class="col-form-label col-sm-3" for="virtual_folder">
				<?= $text->text('OFFSITEDIRS_VIRTUAL_FOLDER') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="virtual_folder" class="form-control"
					   disabled="disabled" value="<?= $this->substep->virtual?>"/>
			</div>
		</div>

		<div class="row mb-3">
			<label class="col-form-label col-sm-3" for="target_folder">
				<?= $text->text('OFFSITEDIRS_TARGET_FOLDER')?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="target_folder" class="form-control" value="<?= $this->substep->name?>"/>
			</div>
		</div>
	</div>
</div>
