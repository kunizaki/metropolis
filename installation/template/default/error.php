<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') || die;

/**
 * @var string|null $error_message
 * @var string|null $error_code
 */

?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Akeeba Backup Site Restoration Script v. <?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?></title>

	<script src="media/js/bootstrap.bundle.min.js"></script>
	<script src="media/js/darkmode.min.js" defer></script>

	<link rel="stylesheet" href="media/css/theme.min.css">
	<link rel="stylesheet" href="media/css/fontawesome.min.css">

	<meta name="theme-color" content="#514F50">
</head>
<body data-bs-theme="" class="brs-error">
<nav class="navbar navbar-expand-lg bg-dark border-bottom border-2 sticky-top container-xl navbar-dark pt-2 pb-1 px-2 d-print-none"
	 id="topNavbar">
	<h1 class="navbar-brand fs-5">
		<img src="media/images/akeeba.svg" width="80px" class="me-2" alt="Akeeba Backup Site Restoration Script">
		<span class="fs-6">
			v. <?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?>
		</span>
	</h1>
</nav>

<main class="container-xl py-2 min-vh-100">
	<?= $error_message ?>
</main>

<footer class="container-xl bg-dark text-light p-3 pb-3 text-light small sticky-sm-bottom d-print-none" data-bs-theme="dark">
	Akeeba Backup Site Restoration Script <?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?>
	<?php if (defined('AKEEBA_DEBUG') && AKEEBA_DEBUG): ?>
		<span class="text-body-tertiary">on</span>
		<span class="text-muted">PHP <?= PHP_VERSION ?>
				<span class="text-body-tertiary">at</span>
				<?= htmlentities($_SERVER['HTTP_HOST']) ?>
			<?php if ($_SERVER['HTTP_HOST'] != php_uname('n')): ?>
				<span class="text-body-tertiary">
					(<?= php_uname('n') ?>)
				</span>
			<?php endif ?>
			</span>
	<?php endif ?>
</footer>
<footer class="container-xl bg-dark text-light p-3 pt-1 text-light small d-print-none" data-bs-theme="dark">
	<div class="d-flex flex-column">
		<p class="mb-2">
			Copyright &copy;2006–<?= sprintf('%d', date('Y')) ?> <a href="https://www.akeeba.com/" target="_blank">Akeeba Ltd</a>.
		</p>
		<p class="mb-2">
			Akeeba Backup Site Restoration Script is Free Software distributed under the <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GNU GPL version 3</a> or any later version published by the Free Software Foundation.
		</p>
	</div>
</footer>
</body>
</html>