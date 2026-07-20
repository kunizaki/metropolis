<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\BRS\View\Main\Html */

$lang = $this->getContainer()->get('language');

?>
<noscript>
	<div class="alert alert-danger mt-4">
		<div class="fs-3 mt-3 text-center">
			<span class="fa-brands fa-js fa-3x"></span>
		</div>

		<h2 class="alert-heading text-center mt-3 mb-4"><?= $lang->text('MAIN_LBL_NOJS_HEAD') ?></h2>

		<p>
			<?= $lang->text('MAIN_LBL_NOJS_BODY') ?>
		</p>
	</div>
</noscript>

<div class="mt-4 d-flex flex-column justify-content-center align-items-center" style="height: 65vh;" id="brs-main-loading">
	<div class="fs-3 mt-4 mb-5 text-center">
		<span class="fa-solid fa-spinner fa-spin-pulse fa-3x"></span>
	</div>

	<h2 class="alert-heading text-center mt-3 mb-4"><?= $lang->text('MAIN_LBL_DEFAULT_HEAD') ?></h2>

	<p class="text-center">
		<?= $lang->text('MAIN_LBL_DEFAULT_BODY') ?>
	</p>
</div>