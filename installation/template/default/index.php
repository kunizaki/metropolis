<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') || die;

/** @var \Akeeba\BRS\Framework\Document\Html $this */

use Akeeba\BRS\Template\DefaultTemplate as DefaultTemplateAlias;

$text          = $this->getContainer()->get('language');
$helper        = new DefaultTemplateAlias($this->getContainer());
$input         = $this->getContainer()->get('input');
$view          = $input->getCmd('view', 'main') ?: 'main';
$isBareDisplay = $input->getCmd('tmpl', '') === 'component';
$isDebug       = defined('AKEEBA_DEBUG') && AKEEBA_DEBUG;
[$langCode,] = explode('-', $text->getLangCode());

$helper->applyDarkModeJavaScript();
?>
<!DOCTYPE html>
<html lang="<?= $langCode ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Akeeba Backup Site Restoration Script <?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?></title>

	<script type="application/json" class="akeeba-script-options new"><?= json_encode($this->getScriptOptions(), $isDebug ? JSON_PRETTY_PRINT : 0) ?: '{}' ?></script>

	<script src="media/js/bootstrap.bundle.min.js"></script>
	<script src="media/js/darkmode.min.js" defer></script>
	<script src="media/js/ajax.min.js" defer></script>
	<script src="media/js/system.min.js" defer></script>

	<link rel="stylesheet" href="media/css/theme.min.css">
	<link rel="stylesheet" href="media/css/fontawesome.min.css">

	<?php foreach ($this->getStyles() as $params): ?>
		<link rel="stylesheet" href="<?= $params['url'] ?>" <?= ($params['media']) ? " media=\"{$params['media']}\"" : '' ?>>
	<?php endforeach ?>
	<?php foreach ($this->getStyleDeclarations() as $content): ?>
		<style <?= ($params['media']) ? " media=\"{$params['media']}\"" : '' ?>><?= $content ?></style>
	<?php endforeach ?>
	<?php foreach ($this->getScripts() as $params): ?>
		<script type="text/javascript" src="<?= $params['url'] ?>"<?= ($params['defer'] ?? false) ? ' defer="defer"' : '' ?>></script>
	<?php endforeach ?>
	<?php foreach ($this->getScriptDeclarations() as $content): ?>
		<script type="text/javascript"><?= $content ?></script>
	<?php endforeach ?>

	<meta name="theme-color" content="#514F50">
</head>
<body data-bs-theme="" class="brs-view-<?= $view ?>">

<!-- Top navbar (sticky) -->
<nav class="navbar navbar-expand-lg bg-dark border-bottom border-2 sticky-top container-xl navbar-dark pt-2 pb-1 px-2 d-print-none"
	 id="topNavbar">
	<h1 class="navbar-brand fs-5 d-flex align-items-center gap-2">
		<?= $helper->getLogo() ?>
		<span class="fs-6">
			v. <?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?>
		</span>
	</h1>

	<?php if ($this->getButtons()): ?>
	<ul class="navbar-nav ms-auto pt-0 pb-2 me-2 gap-2">
		<?php foreach ($this->getButtons() as $button): ?>
		<button type="button" class="btn border <?= $button->buttonClass ?>" id="<?= $button->id ?>">
			<span class="fa <?= $button->icon ?> me-1" aria-hidden="true"></span>
			<?= strpos($button->title, ' ') ? $button->title : $text->text($button->title) ?>
		</button>
		<?php endforeach ?>
	</ul>
	<?php endif ?>
</nav>

<!-- Breadcrumbs -->
<?php if ($this->getContainer()->get('breadcrumbs')->hasCrumbs()): ?>
<nav class="container-xl py-2 px-2 mb-0 bg-body-tertiary border-bottom d-print-none" id="breadcrumbs" aria-label="Breadcrumbs">
	<ol class="breadcrumb mb-0">
		<?php foreach ($this->getContainer()->get('breadcrumbs')->getCrumbs() as $crumb): ?>
			<li class="breadcrumb-item <?= $crumb->active ? 'active fw-bold' : '' ?>"
				aria-current="<?= $crumb->active ? 'page' : '' ?>">
				<?= $text->text($crumb->name) ?>
				<?php if ($crumb->substeps > 1): ?>
					<span class="visually-hidden"><?= $text->sprintf('GENERAL_LBL_SUBSTEPS_COUNT', $crumb->substeps) ?></span>
					<?php if ($crumb->active): ?>
						<span class="visually-hidden"><?= $text->sprintf('GENERAL_LBL_SUBSTEP_SR', $crumb->substepIndex, $crumb->substeps) ?></span>
						<sup class="badge bg-info ms-1" aria-hidden="true">
							<?= $text->sprintf('GENERAL_LBL_SUBSTEP_OF', $crumb->substepIndex, $crumb->substeps) ?>
						</sup>
					<?php else: ?>
						<sup class="badge bg-secondary rounded-pill ms-1" aria-hidden="true">
							<?= $crumb->substeps ?>
						</sup>
					<?php endif ?>
				<?php endif ?>
			</li>
		<?php endforeach ?>
	</ol>
</nav>
<?php endif; ?>

<!-- Documentation and video tutorial links -->
<?php if ($this->getHelpURL() || $this->getVideoURL()): ?>
	<section id="help" class="container-xl py-2 px-2 d-print-none">
		<div class="row row-cols-1">
			<div class="col px-5">
				<div class="m-1 p-2 border rounded-3 bg-body-tertiary">
					<div class="d-flex flex-row gap-3 justify-content-start align-items-center">
						<h4 class="alert-heading fw-light fs-6 flex-grow-1">
							<span class="fa fa-info me-1" aria-hidden="true"></span>
							<?= $text->text('GENERAL_LBL_WHATTODONEXT') ?>
						</h4>
						<?php if ($this->getHelpURL()): ?>
							<a href="<?= $this->getHelpURL() ?>" target="_blank" class="btn btn-outline-primary">
								<span class="fa fa-book-reader me-1" aria-hidden="true"></span>
								<?= $text->text('GENERAL_BTN_RTFM') ?>
							</a>
						<?php endif ?>
						<?php if ($this->getVideoURL()): ?>
							<a href="<?= $this->getVideoURL() ?>" target="_blank" class="btn btn-outline-info">
								<span class="fa fa-video me-1" aria-hidden="true"></span>
								<?= $text->text('GENERAL_BTN_VIDEO') ?>
							</a>
						<?php endif ?>
					</div>
				</div>
			</div>
		</div>
	</section>
<?php endif ?>

<!-- Main container -->
<main class="container-xl py-2 min-vh-100">
	<?php
	foreach (['error', 'warning', 'success', 'info'] as $type):
		if (!($messages = $this->getContainer()->get('application')->getMessageQueueFor($type))) continue;
	?>
	<div id="brs-message-<?= $type?>" class="brs-message alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
		<?php foreach ($messages as $message): ?>
			<p><?php echo $message ?></p>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>

	<?= $this->getBuffer() ?>
</main>

<!-- Footer (semi-sticky) -->
<footer class="container-xl bg-dark text-light p-3 pb-3 text-light small sticky-sm-bottom d-print-none" data-bs-theme="dark">
	Akeeba Backup Site Restoration Script
	<span class="text-info-emphasis">
		(<?= $text->text('BRS_PLATFORM_TITLE_' . $this->getContainer()->get('configuration')->type) ?>)
	</span>
	<?= defined('AKEEBA_VERSION') ? AKEEBA_VERSION : '' ?>
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