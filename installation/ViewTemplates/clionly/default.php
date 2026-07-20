<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Akeeba\BRS\View\Clionly\Html $this */
?>

<div class="card border-2 border-danger mt-5">
	<h2 class="text-danger fw-bold card-header bg-danger text-white">
		<span class="fa fa-terminal me-2" aria-hidden="true"></span>
		CLI-Only Mode Enabled
	</h2>

	<div class="card-body py-5 fs-4">
		<p class="mb-4">
			This restoration script is currently configured to run only from the command line (CLI).
		</p>
		<p class="mb-0">
			If you would like to run the restoration script from the web interface, please remove the <code>config.yml.php</code> file from the <code>installation</code> folder.
		</p>
	</div>
</div>
