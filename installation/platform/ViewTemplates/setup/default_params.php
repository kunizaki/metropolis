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
<div class="card border-primary mb-3">
	<h3 class="card-header bg-primary text-white">
		<?= $text->text('SETUP_HEADER_SITEPARAMS') ?>
	</h3>

	<div class="card-body">
		<div class="row mb-3">
			<label for="sitename" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_SITENAME') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="sitename" name="sitename" class="form-control"
				       value="<?= $this->stateVars->sitename ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_SITENAME_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="siteemail" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_SITEEMAIL') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="siteemail" name="siteemail" class="form-control"
				       value="<?= $this->stateVars->siteemail ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_SITEEMAIL_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="emailsender" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_EMAILSENDER') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="emailsender" name="emailsender" class="form-control"
				       value="<?= $this->stateVars->emailsender ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_EMAILSENDER_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<div class="col offset-sm-3">
				<div class="form-check form-switch">
					<input type="checkbox" class="form-check-input" id="newsecret" name="newsecret"
						   role="switch" value="1"
						<?= $this->newSecretOptions['disabled'] ?>
						<?= $this->newSecretOptions['checked'] ?>
					>
					<label for="newsecret" class="form-check-label" <?= $this->newSecretOptions['disabled'] ?>>
						<?= $text->text('SETUP_LBL_SERVERCONFIG_NEWSECRET') ?>
					</label>
				</div>
				<div class="form-text d-none">
					<?= $text->text($this->newSecretOptions['help']) ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="livesite" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_LIVESITE') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="livesite" name="livesite" class="form-control"
				       value="<?= $this->stateVars->livesite ?>" />

				<?php if (substr(PHP_OS, 0, 3) == 'WIN'): ?>
					<div class="alert alert-warning my-1">
						<span class="fa fa-exclamation-triangle me-1" aria-hidden="true"></span>
						<?= $text->text('SETUP_LBL_LIVESITE_WINDOWS_WARNING') ?>
					</div>
				<?php endif; ?>
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_LIVESITE_HELP') ?>
				</div>
			</div>
		</div>

		<?php if($this->protocolMismatch): ?>
			<div class="row mb-3">
				<div class="col-sm-12">
					<div class="alert alert-warning">
						<?= $text->text('SETUP_LBL_SERVERCONFIG_DISABLEFORCESSL_WARN')?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="row mb-3">
			<label for="force_ssl" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LABEL_FORCESSL') ?>
			</label>
			<div class="col-sm-9">
				<?= $select->forceSSL($this->stateVars->force_ssl) ?>
				<div class="form-text d-none">
					<?= $text->text('SETUP_LABEL_FORCESSL_TIP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="cookiedomain" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_COOKIEDOMAIN') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="cookiedomain" name="cookiedomain" class="form-control"
				       value="<?= $this->stateVars->cookiedomain ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_COOKIEDOMAIN_HELP') ?>
				</div>
			</div>
		</div>

		<div class="row mb-3">
			<label for="cookiepath" class="col-form-label col-sm-3">
				<?= $text->text('SETUP_LBL_COOKIEPATH') ?>
			</label>
			<div class="col-sm-9">
				<input type="text" id="cookiepath" name="cookiepath" class="form-control"
				       value="<?= $this->stateVars->cookiepath ?>" />
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_COOKIEPATH_HELP') ?>
				</div>
			</div>
		</div>

		<?php if (version_compare($jVersion, '2.5.0', 'ge')): ?>
			<div class="row mb-3">
				<label for="robotHandling" class="col-form-label col-sm-3">
					<?= $text->text('SETUP_LBL_ROBOTS_LBL') ?>
				</label>
				<div class="col-sm-9">
					<?= $select->genericlist([
						$select->option(0, $text->text('SETUP_LBL_ROBOTS_OPT_NO_CHANGE')),
						$select->option(1, $text->text('SETUP_LBL_ROBOTS_OPT_INDEX_FOLLOW')),
						$select->option(2, $text->text('SETUP_LBL_ROBOTS_OPT_INDEX_NO_FOLLOW')),
						$select->option(3, $text->text('SETUP_LBL_ROBOTS_OPT_NO_INDEX_NO_FOLLOW')),
					], 'robotHandling', [
						'list.select' => $this->robotHandling,
						'list.attr' => [
							'class' => 'form-select',
						]
					]) ?>
				</div>
			</div>
		<?php endif ?>


		<?php if (version_compare($jVersion, '3.2', 'ge')): ?>
			<div class="row mb-3">
				<div class="col offset-sm-3">
					<div class="form-check form-switch">
						<input type="checkbox" class="form-check-input" id="mailonline" name="mailonline"
						       role="switch" value="1"
							<?= $this->stateVars->mailonline ? 'checked="checked"' : '' ?>
						>
						<label for="mailonline" class="form-check-label">
							<?= $text->text('SETUP_LBL_MAILONLINE') ?>
						</label>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col offset-sm-3">
					<div class="form-check form-switch">
						<input type="checkbox" class="form-check-input" id="resetsessionoptions" name="resetsessionoptions"
						       role="switch" value="1"
							<?= $this->stateVars->resetsessionoptions ? 'checked="checked"' : '' ?>
						>
						<label for="resetsessionoptions" class="form-check-label">
							<?= $text->text('SETUP_LBL_RESETSESSIONOPTIONS') ?>
						</label>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col offset-sm-3">
					<div class="form-check form-switch">
						<input type="checkbox" class="form-check-input" id="resetcacheoptions" name="resetcacheoptions"
						       role="switch" value="1"
							<?= $this->stateVars->resetcacheoptions ? 'checked="checked"' : '' ?>
						>
						<label for="resetcacheoptions" class="form-check-label">
							<?= $text->text('SETUP_LBL_RESETCACHEOPTIONS') ?>
						</label>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php
		$langKeySuffix = version_compare($jVersion, '5.1.0', 'ge') ? '_51' : '';
		?>

		<div class="row mb-3">
			<div class="col offset-sm-3">
				<button type="button"
				        class="btn btn-dark w-100"
				        id="usesitedirs" name="usesitedirs">
					<span class="fa fa-wand-sparkles me-1" aria-hidden="true"></span>
					<?= $text->text('SETUP_LBL_USESITEDIRS' . $langKeySuffix) ?>
				</button>
				<div class="form-text d-none">
					<?= $text->text('SETUP_LBL_USESITEDIRS_HELP' . $langKeySuffix) ?>
				</div>
			</div>
		</div>
	</div>
</div>
