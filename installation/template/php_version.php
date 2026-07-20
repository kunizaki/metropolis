<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * @var string $minVersion
 */

$currentVersion = PHP_VERSION;
?>

<div class="alert alert-danger">
	<h2 class="my-3 alert-heading">Your PHP version is too old</h2>

	<p class="fs-5">
		This restoration script requires PHP <?= $minVersion ?> or newer.
	</p>

	<p class="fs-5">
		Your current PHP version is <?php echo $currentVersion; ?>.
	</p>
</div>

<h4 class="mt-4">How to fix this</h4>

<p class="fs-5">
	Please ask your host how you can switch your site to a newer PHP version. In most cases you just need to go into your hosting control panel and change the PHP version. Choose a PHP version compatible with this script, i.e. a PHP version newer than <?= $minVersion ?>.
</p>

<hr>

<h4 class="mt-4">Can this message appear in error?</h4>

<p>
	No, it can not. The PHP version is reported by PHP itself. The version string is compiled into the PHP language itself. It can never be wrong. The minimum required version of this installer is hardcoded into the installer itself. Therefore, it cannot be wrong either.
</p>

<p>
	What could be wrong is your perception of which PHP version is used by your host, and it's not your fault. Most servers have multiple PHP versions installed at the same time. The PHP version they report in the hosting control panel is the PHP version used by the control panel itself, <em>not</em> the version used for hosting your site. What is relevant to you and your site is only the latter. The discrepancy between what's reported and what is relevant to you leads to this kind of misunderstanding.
</p>


<h4 class="mt-4">How to read PHP versions</h4>

<p>
	PHP and Joomla! version numbers are not decimals. They are three numbers separated by dots. For example: 1.2.3 These numbers have names. The leftmost number (1) is called the major version. The middle number (2) is called the minor version. The rightmost number (3) is called the revision, but it may also be referred to as a "point release" or "subminor version".
</p>

<p>
	When you want to know if a version is newer than a different version you have to start reading from left to right:
</p>

<ul>
	<li>If the major version is higher, your version is newer. If it is lower, your version is older. If it's the same, continue reading.</li>
	<li>If the minor version is higher, your version is newer. If it is lower, your version is older. If it's the same, continue reading.</li>
	<li>Trailing zeros in the revision do count. Do not remove them. If the revision is higher, your version is newer. If it is lower, your version is older. If it's the same you have the same version.</li>
</ul>