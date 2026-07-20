<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @deprecated 11.0 */

/** @var \Akeeba\BRS\Platform\View\Setup\Html $this */

use Akeeba\BRS\Framework\Helper\Select;

$text     = $this->getContainer()->get('language');
$select   = new Select($this->getContainer());
$jVersion = $this->getContainer()->get('session')->get('jversion');
?>
<div class="card border-tertiary mb-3">
	<h3 class="card-header bg-tertiary text-white">
		<?= $text->text('SETUP_HEADER_FTPPARAMS') ?>
	</h3>

	<div class="card-body">
		<div class="alert alert-info small">
			<?= $text->text('SETUP_LABEL_FTPENABLE_HELP') ?>
		</div>

		<div class="text-center" style="margin-bottom: 20px">
			<button type="button" id="showFtpOptions"
			        class="btn btn-success <?= $this->stateVars->ftpenable ? 'd-none' : '' ?>"
			>
				<?= $text->text('SETUP_LABEL_FTPENABLE') ?>
			</button>
			<button type="button" id="hideFtpOptions"
			        class="btn btn-danger <?= $this->stateVars->ftpenable ? '' : 'd-none' ?>"
			>
				<?= $text->text('SETUP_LABEL_FTPDISABLE') ?>
			</button>
		</div>

		<input type="hidden" id="enableftp" name="enableftp"
		       value="<?= $this->stateVars->ftpenable ?>" />

		<div id="ftpLayerHolder"
		     class="<?= $this->stateVars->ftpenable ? '' : 'd-none' ?>"
		>
			<div class="row mb-3">
				<label for="ftphost" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LABEL_FTPHOST') ?>
				</label>
				<div class="col-sm-9">
					<input type="text" id="ftphost" name="ftphost" class="form-control"
					       value="<?= $this->stateVars->ftphost ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LABEL_FTPHOST_HELP') ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<label for="ftpport" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LABEL_FTPPORT') ?>
				</label>
				<div class="col-sm-9">
					<input type="text" id="ftpport" name="ftpport" class="form-control"
					       value="<?= empty($this->stateVars->ftpport) ? '21' : $this->stateVars->ftpport ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LABEL_FTPPORT_HELP') ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<label for="ftpuser" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LABEL_FTPUSER') ?>
				</label>
				<div class="col-sm-9">
					<input type="text" id="ftpuser" name="ftpuser" class="form-control"
					       value="<?= $this->stateVars->ftpuser ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LABEL_FTPUSER_HELP') ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<label for="ftppass" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LABEL_FTPPASS') ?>
				</label>
				<div class="col-sm-9">
					<input type="password" id="ftppass" name="ftppass" class="form-control"
					       value="<?= $this->stateVars->ftppass ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LABEL_FTPPASS_HELP') ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<label for="ftpdir" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LABEL_FTPDIR') ?>
				</label>
				<div class="col-sm-9">
					<input type="text" id="ftpdir" name="ftpdir" class="form-control"
					       value="<?= $this->stateVars->ftpdir ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LABEL_FTPDIR_HELP') ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>