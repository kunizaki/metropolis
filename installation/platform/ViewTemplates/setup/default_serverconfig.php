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
<div class="card border-warning mb-3">
	<h3 class="card-header bg-warning text-white">
		<?= $text->text('SETUP_HEADER_SERVERCONFIG') ?>
	</h3>

	<div class="card-body">
		<div class="alert alert-info small">
			<?= $text->text('SETUP_SERVERCONFIG_DESCR') ?>
		</div>

		<?php if ($this->htaccessSupported && $this->hasHtaccess): ?>
			<div class="row mb-3">
				<label for="htaccessHandling" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LBL_HTACCESSCHANGE_LBL') ?>
				</label>
				<div class="col-sm-9">
					<?= $select->genericlist($this->htaccessOptions, 'htaccessHandling', [
						'list.select' => $this->htaccessOptionSelected,
						'list.attr' => [
							'class' => 'form-select',
						]
					]) ?>
					<div class="form-text d-none">
						<?= $text->text('SETUP_LBL_HTACCESSCHANGE_DESC') ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($this->webConfSupported): ?>
			<div class="row mb-3">
				<div class="col offset-sm-3">
					<div class="form-check form-switch">
						<input type="checkbox" class="form-check-input"
						       id="replacewebconfig" name="replacewebconfig"
						       role="switch" value="1"
							<?= $this->replaceWeconfigOptions['disabled'] ?>
							<?= $this->replaceWeconfigOptions['checked'] ?>
						>
						<label for="replacewebconfig" class="form-check-label"
							<?= $this->replaceWeconfigOptions['disabled'] ?>
						>
							<?= $text->text('SETUP_LBL_SERVERCONFIG_REPLACEWEBCONFIG') ?>
						</label>
					</div>
					<div class="form-text d-none">
						<?= $text->text($this->replaceWeconfigOptions['help']) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="row mb-3">
			<div class="col offset-sm-3">
				<div class="form-check form-switch">
					<input type="checkbox" class="form-check-input"
					       id="removephpini" name="removephpini"
					       role="switch" value="1"
						<?= $this->removePhpiniOptions['disabled'] ?>
						<?= $this->removePhpiniOptions['checked'] ?>
					>
					<label for="removephpini" class="form-check-label"
						<?= $this->removePhpiniOptions['disabled'] ?>
					>
						<?= $text->text('SETUP_LBL_SERVERCONFIG_REMOVEPHPINI') ?>
					</label>
				</div>
				<div class="form-text d-none">
					<?= $text->text($this->removePhpiniOptions['help']) ?>
				</div>
			</div>
		</div>

		<?php if ($this->htaccessSupported): ?>
			<div class="row mb-3">
				<div class="col offset-sm-3">
					<div class="form-check form-switch">
						<input type="checkbox" class="form-check-input"
						       id="removehtpasswd" name="removehtpasswd"
						       role="switch" value="1"
							<?= $this->removeHtpasswdOptions['disabled'] ?>
							<?= $this->removeHtpasswdOptions['checked'] ?>
						>
						<label for="removehtpasswd" class="form-check-label"
							<?= $this->removeHtpasswdOptions['disabled'] ?>
						>
							<?= $text->text('SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD') ?>
						</label>
					</div>
					<div class="form-text d-none">
						<?= $text->text($this->removeHtpasswdOptions['help']) ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>