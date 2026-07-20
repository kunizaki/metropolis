<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\Platform\View\Setup\Html $this */

use Akeeba\BRS\Framework\Helper\Select;

$text     = $this->getContainer()->get('language');
$select   = new Select($this->getContainer());
$jVersion = $this->getContainer()->get('session')->get('jversion');
?>
<div class="card border-info mb-3">
	<h3 class="card-header bg-info text-white">
		<?= $text->text('SETUP_HEADER_FINETUNING') ?>
	</h3>

	<div class="card-body">

		<div class="row mb-3">
			<label for="siteroot" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_SITEROOT') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" disabled="disabled" id="siteroot" class="form-control"
				       value="<?= $this->stateVars->site_root_dir ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_SITEROOT_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="tmppath" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_TMPPATH') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="tmppath" name="tmppath" class="form-control"
				       value="<?= $this->stateVars->tmppath ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_TMPPATH_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="logspath" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_LOGSPATH') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="logspath" name="logspath" class="form-control"
				       value="<?= $this->stateVars->logspath ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_LOGSPATH_HELP') ?>
				</div>
			</div>
		</div>

		<?php if(version_compare($this->container->session->get('jversion', '2.5.0'), '5.1.0', 'ge')): ?>
			<div class="row mb-3">
				<label for="cache_path" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LBL_CACHE_PATH') ?>
				</label>
				<div class="col-sm-9">
					<input type="text" id="cache_path" name="cache_path" class="form-control"
					       value="<?= $this->stateVars->cache_path ?? '' ?>" />
					<div class="form-text d-none">
						<?= $text->text('SETUP_LBL_CACHE_PATH_HELP') ?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<input type="hidden" id="cache_path" name="cache_path"
			       value="" />
		<?php endif ?>
	</div>
</div>