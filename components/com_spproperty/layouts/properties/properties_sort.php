<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;

$property_total     = $displayData['property_total'];
$all_properties_url = $displayData['all_properties_url'];
$property_types     = $displayData['property_types'];
$catid              = $displayData['catid'];
?>

<?php if ($property_total) { ?>
    <ul class="spproperty-listing-url">
        <li <?php echo ($catid == '') ? 'class="active"' : '';?>>
            <a href="<?php echo $all_properties_url; ?>"><?php echo Text::_('COM_SPPROPERTY_ALL') . ' (' . $property_total . ')';?></a>
        </li>
        <?php foreach ($property_types as $property_type) { ?>
            <li <?php echo ($catid == $property_type->id) ? 'class="active"' : '';?>>
                <a href="<?php echo $property_type->url; ?>"><?php echo $property_type->title . ' (' . $property_type->this_count . ')';?></a>
            </li>

        <?php } ?>
    </ul>
<?php } ?>
