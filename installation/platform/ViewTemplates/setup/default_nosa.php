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

$text   = $this->getContainer()->get('language');
$select = new Select($this->getContainer());
?>
<div class="row row-cols-1 mb-3">
	<div class="col">
		<div class="alert alert-danger" role="alert">
			<h4 class="alert-heading d-flex flex-column align-items-center fs-1 gap-3 mb-4">
					<span class="fa-stack fa-2x" aria-hidden="true">
						<span class="fa-solid fa-circle fa-stack-2x text-white"></span>
						<span class="fa-solid fa-user-tie fa-stack-1x text-dark"></span>
					  	<span class="fa-solid fa-ban fa-stack-2x text-danger"></span>
					</span>
				<span>
						<?= $text->text('SETUP_LBL_NOSA_HEAD') ?>
					</span>
			</h4>
			<p>
				<?= $text->text('SETUP_LBL_NOSA_DESC') ?>
			</p>
			<p class="fw-semibold fs-2 text-center bg-danger text-white p-2 my-4">
				<?= $text->text('SETUP_LBL_NOSA_ACTION_REQUIRED') ?>
			</p>
			<p>
				<?= $text->text('SETUP_LBL_NOSA_ACTION_SUMMARY') ?>
			</p>
			<p>
				<?= $text->text('SETUP_LBL_NOSA_ACTION_INFO') ?>
			</p>
		</div>
	</div>
</div>