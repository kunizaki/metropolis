<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var \Akeeba\BRS\Platform\View\Publicfolder\Html $this
 */

defined('_AKEEBA') or die();

$text = $this->getContainer()->get('language');
?>

<div class="alert alert-info">
	<h3 class="alert-heading">
		<?= $text->text('PUBLICFOLDER_LBL_INFO_HEAD') ?>
	</h3>
	<p>
		<?= $text->sprintf('PUBLICFOLDER_LBL_INFO_BODY', $this->oldPublic, $this->oldRoot) ?>
	</p>

	<?php if ($this->isWindows): ?>
		<p>
			<?= $text->text('PUBLICFOLDER_LBL_BUT_WINDOWS') ?>
		</p>
	<?php elseif ($this->isServedDirectly && !$this->isServedFromPublic): ?>
		<p>
			<?= $text->text('PUBLICFOLDER_LBL_BUT_DIRECTLY') ?>
		</p>
	<?php elseif($this->isServedFromPublic && !$this->isServedDirectly): ?>
		<p>
			<?= $text->text('PUBLICFOLDER_LBL_BUT_CUSTOM') ?>
		</p>
	<?php endif ?>
</div>

<div class="card border-info <?= $this->hideInterface ? 'd-none' : '' ?>">
	<div class="card-header bg-info text-white">
		<h3>
			<?= $text->text('PUBLICFOLDER_LBL_OPTIONS_HEADER') ?>
		</h3>
	</div>

	<form action="index.php" method="post" id="publicfolderForm" name="publicfolderForm" class="card-body">
		<?php if ($this->noChoice): ?>
			<input type="hidden" id="usepublic" name="usepublic" value="<?= $this->stateVars->usesplit ? '1' : '0' ?>">
		<?php else: ?>
			<div class="row mb-3">
				<div class="col-sm-9 offset-sm-3">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch"
							   id="usepublic" name="usepublic" value="1"
							<?= $this->stateVars->usesplit ? 'checked="checked"' : '' ?>>
						<label class="form-check-label" for="usepublic">
							<?= $text->text('PUBLICFOLDER_LBL_USE_SPLIT') ?>
						</label>
					</div>
				</div>
			</div>
		<?php endif ?>

		<div class="row mb-3" <?= $this->showOn('usepublic:1') ?>>
			<label for="newpublic" class="col-sm-3 col-form-label">
				<?= $text->text('PUBLICFOLDER_LBL_NEWPUBLIC') ?>
			</label>

			<div class="col-sm-9">
				<input type="text" name="newpublic" id="newpublic" class="form-control"
					   value="<?= $this->hideInterface ? '' : $this->stateVars->newpublic ?>"
					<?= $this->hideInterface ? 'disabled' : '' ?>
				/>
				<p class="form-text <?= $this->hideInterface ? 'd-none' : '' ?>">
					<?= $text->sprintf('PUBLICFOLDER_LBL_NEWPUBLIC_HELP', $this->escape($this->stateVars->samplefolder)) ?>
				</p>
			</div>
		</div>

		<input type="hidden" name="view" value="publicfolder" />
		<input type="hidden" name="task" value="apply" />
	</form>
</div>