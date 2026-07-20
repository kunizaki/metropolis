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

<div class="container">
	<div class="row row-cols-1 g-3">
		<div class="col">
			<?= $this->loadAnyTemplate('main/panel_overview') ?>
		</div>
		<div class="col">
			<div class="card border-info mb-3">
				<header class="card-header bg-info text-white">
					<h3 class="fs-5 p-0 m-0 d-flex align-items-center">
						<span class="flex-grow-1">
							<?= $text->text('MAIN_LBL_REQUIRED_SETTINGS') ?>
						</span>
						<button type="button" class="btn btn-dark btn-sm" role="button"
								data-bs-toggle="collapse" href="#requiredSettingsDetails" aria-controls="requiredSettingsDetails"
								aria-expanded="<?= $this->meetsRequired ? 'true' : 'false' ?>"
						>
							<span class="visually-hidden">
								<?= $text->text('GENERAL_LBL_TOGGLE_DISPLAY') ?>
							</span>
							<span class="fas fa fa-arrows-up-down" aria-hidden="true"></span>
						</button>
					</h3>
				</header>
				<div class="card-body collapse <?= $this->meetsRequired ? '' : 'show' ?>" id="requiredSettingsDetails">
					<?= $this->loadAnyTemplate('main/panel_checks', ['checks' => $this->requiredChecks]) ?>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card border-secondary mb-3">
				<header class="card-header bg-secondary text-white">
					<h3 class="fs-5 p-0 m-0 d-flex align-items-center">
						<span class="flex-grow-1">
							<?= $text->text('MAIN_LBL_RECOMMENDED_SETTINGS') ?>
						</span>
						<button type="button" class="btn btn-dark btn-sm" role="button"
								data-bs-toggle="collapse" href="#recommendedSettingsDetails" aria-controls="recommendedSettingsDetails"
								aria-expanded="<?= $this->meetsRecommended ? 'true' : 'false' ?>"
						>
							<span class="visually-hidden">
								<?= $text->text('GENERAL_LBL_TOGGLE_DISPLAY') ?>
							</span>
							<span class="fas fa fa-arrows-up-down" aria-hidden="true"></span>
						</button>
					</h3>
				</header>
				<div class="card-body collapse <?= $this->meetsRecommended ? '' : 'show' ?>" id="recommendedSettingsDetails">
					<?= $this->loadAnyTemplate('main/panel_checks', ['checks' => $this->recommendedChecks]) ?>
				</div>
			</div>
		</div>
		<div class="col">
			<?= $this->loadAnyTemplate('main/panel_info') ?>
		</div>
	</div>
</div>