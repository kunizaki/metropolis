<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Uri\Uri;

$url        = $displayData['url'];
$title      = $displayData['title'];
$root       = Uri::base();
$root       = new Uri($root);
$itemUrl    = $root->getScheme() . '://' . $root->getHost() . $url;

?>

<ul class="sppropety-details-social">
    <li>
        <a class="twitter" onClick="window.open('http://twitter.com/share?url=<?php echo $itemUrl; ?>&amp;text=<?php echo str_replace(" ", "%20", $title); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo $itemUrl; ?>&amp;text=<?php echo str_replace(" ", "%20", $title); ?>">
            <i class="fa-brands fa-x-twitter"></i>
        </a>
    </li>

    <li>
        <a class="facebook" onClick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $itemUrl; ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo $itemUrl; ?>">
            <i class="fa fa-facebook-official"></i>
        </a>
    </li>

    <li>
        <a class="instagram" href='javascript:void((function()%7Bvar%20e=document.createElement(&apos;script&apos;);e.setAttribute(&apos;type&apos;,&apos;text/javascript&apos;);e.setAttribute(&apos;charset&apos;,&apos;UTF-8&apos;);e.setAttribute(&apos;src&apos;,&apos;http://assets.pinterest.com/js/pinmarklet.js?r=&apos;+Math.random()*99999999);document.body.appendChild(e)%7D)());'>
            <i class="fa fa-pinterest-p"></i>
        </a>
    </li>
</ul>