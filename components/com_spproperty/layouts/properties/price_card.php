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
use Joomla\CMS\Component\ComponentHelper;

$cParams = ComponentHelper::getParams('com_spproperty','');
$property = $displayData['property'];
$calc_price =  SppropertyHelper::getCalculatedPrice($property->solid_price, $property->psize);
$total_price = SppropertyHelper::generateCurrency($calc_price, $property->currency, $property->currency_position, $property->currency_format);

$separator = $cParams->get('use_separator', '1') ? ' / ' : ' ';

?>

<?php if ($property->fixed_price) { ?>
    <?php echo $property->price; ?>
<?php } else { ?>
    <?php if ($property->property_status == 'rent') { ?>
        <?php echo $total_price ; ?>
    <?php } else { ?>
        <?php echo $property->price; ?>
    <?php } ?>
<?php } ?>

<span class="price-tag">
    <?php if ($property->property_status == 'rent') { ?>
        <?php
            echo !empty($property->rent_period) ? $separator . $property->rent_period : $separator . Text::_('COM_SPPROPERTY_RENT_PERIOD_DEFAULT');
        ?>
    <?php } else { ?>
        <?php
        if (!$property->fixed_price && $cParams['show_unit']) {
            echo empty($cParams['measurement']) ?  $separator . Text::_('COM_SPPROPERTY_PROPERTIES_SQFT') : ' / ' . $cParams['measurement'];
        }
        ?>
        
    <?php } //end of else ?>
</span>