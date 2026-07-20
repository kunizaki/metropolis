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
<div class="card border-secondary mb-3">
	<header class="card-header bg-body-tertiary text-body">
		<h3 class="fs-5 p-0 m-0 d-flex align-items-center">
			<span class="flex-grow-1">
				<?= $text->text('MAIN_HEADER_EXTRAINFO') ?>
			</span>
			<button type="button" class="btn btn-dark btn-sm" role="button"
			        data-bs-toggle="collapse" href="#siteInfoDetails" aria-controls="siteInfoDetails"
			        aria-expanded="true"
			>
				<span class="visually-hidden">
					<?= $text->text('GENERAL_LBL_TOGGLE_DISPLAY') ?>
				</span>
				<span class="fas fa fa-arrows-up-down" aria-hidden="true"></span>
			</button>
		</h3>
	</header>
	<div class="card-body collapse show" id="siteInfoDetails">
		<?php if (empty($this->siteInfo)): ?>
		<div class="alert alert-danger">
			<h4 class="alert-heading">
				<?= $text->text('MAIN_ERR_EXTRAINFO_HEADER') ?>
			</h4>
			<p>
				<?= $text->text('MAIN_ERR_EXTRAINFO_DETAILS') ?>
			</p>
		</div>
		<?php else: ?>
		<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col">
					<?= $text->text('MAIN_LBL_SETTING') ?>
				</th>
				<th scope="col">
					<?= $text->text('MAIN_LBL_BACKUP_SETTING') ?>
				</th>
				<th scope="col">
					<?= $text->text('MAIN_LBL_CURRENT_SETTING') ?>
				</th>
				<th scope="col">
					<?= $text->text('MAIN_LBL_CHECK_STATUS') ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php if ($this->getContainer()->get('configuration')->type === 'joomla'): ?>
			<tr>
				<th scope="row">
					<?= $text->text('MAIN_LBL_EXTRAINFO_VERSION_JOOMLA') ?>
				</th>
				<td>&mdash;</td>
				<td>
					<?= $this->getContainer()->get('session')->get('jversion', '') ?>
				</td>
				<td>
					<span class="fa fa-check-circle text-success" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_VALID') ?></span>
				</td>
			</tr>
			<?php elseif($this->getContainer()->get('configuration')->type === 'wordpress'): ?>
			<tr>
				<th scope="row">
					<?= $text->text('MAIN_LBL_EXTRAINFO_VERSION_WORDPRESS') ?>
				</th>
				<td>
					&mdash;
				</td>
				<td>
					<?= $this->getContainer()->get('session')->get('version', '') ?>
				</td>
				<td>
					<span class="fa fa-check-circle text-success" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_VALID') ?></span>
				</td>
			</tr>
			<?php endif ?>
			<?php foreach ($this->siteInfo as $siteInfo): ?>
			<tr>
				<th scope="row">
					<?php if ($siteInfo->isChanged()): ?>
						<span class="visually-hidden"><?= $siteInfo->getChangedInfo() ?></span>
						<span class="fa fa-info-circle me-1 text-info-emphasis" aria-hidden="true"
							  data-bs-toggle="tooltip" data-bs-placement="right"
							  data-bs-title="<?= $siteInfo->getChangedInfo() ?>"></span>
					<?php endif; ?>
					<span class="<?= $siteInfo->isChanged() ? 'text-warning' : '' ?>"><?= $siteInfo->getName() ?></span>
				</th>
				<td>
					<?= $siteInfo->getAtBackup() ?? '&mdash;' ?>
				</td>
				<td>
					<?= $siteInfo->getAtRestore() ?? '&mdash;' ?>
				</td>
				<td>
					<?php if ($siteInfo->isChanged()): ?>
					<span class="fa fa-exclamation-triangle text-warning" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_INVALID') ?></span>
					<?php else: ?>
					<span class="fa fa-check-circle text-success" aria-hidden="true"></span>
					<span class="visually-hidden"><?= $text->text('MAIN_LBL_CHECK_VALID') ?></span>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach ?>
			</tbody>
		</table>
		<?php endif; ?>

		<div class="mt-4 <?= @file_exists('README.html') ? '' : 'd-none' ?>">
			<hr>
			<button type="button" class="btn btn-primary"
					data-bs-toggle="modal" data-bs-target="#readmeModal">
				<span class="fa fa-file-text me-1" aria-hidden="true"></span>
				<?= $text->text('MAIN_LBL_README_BUTTON') ?>
			</button>
			<p class="small text-muted mt-2 mb-0">
				<?= $text->text('MAIN_LBL_README_INFO') ?>
			</p>
		</div>

		<div class="modal fade" tabindex="-1" id="readmeModal"
			 aria-labelledby="readmeModalLabel" aria-hidden="true"
		>
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title fs-5" id="readmeModalLabel">
							<?= $text->text('MAIN_LBL_README_HEADER') ?>
						</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal"
								aria-label="<?= $text->text('GENERAL_LBL_CLOSE') ?>"></button>
					</div>
					<div class="modal-body" style="height: max(200px, 45vh)">
						<iframe src="README.html" frameborder="0" style="height: 100%; width: 100%"></iframe>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>