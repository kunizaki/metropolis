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

$cParams = ComponentHelper::getParams('com_spproperty');
$property = $displayData['property'];
$calc_price =  SppropertyHelper::getCalculatedPrice($property->solid_price, $property->psize);
$total_price = SppropertyHelper::generateCurrency($calc_price, $property->currency, $property->currency_position, $property->currency_format);

$separator = $cParams->get('use_separator', '1') ? ' / ' : ' ';

if ($property) { ?>
    <h3 class="spproperty-price-title"><?php echo $property->property_status == 'rent' ? Text::_('COM_SPPROPERTY_PROPERTY_RENT') : Text::_('COM_SPPROPERTY_PROPERTY_PRICE'); ?></h3>

    <!-- If fixed price given -->
    <?php if ($property->fixed_price) { ?>
        <?php if ($property->property_status == 'rent') { ?>
            <p class="spproperty-total-price">
                <?php echo $property->price . $separator . (!empty($property->rent_period) ? $property->rent_period : Text::_('COM_SPPROPERTY_RENT_PERIOD_DEFAULT')); ?>
            </p>
        <?php } else { ?>
            <p class="spproperty-total-price">              
                <?php echo $property->price; ?>
            </p>
        <?php } ?>
    <?php } else { ?>
    <p class="spproperty-total-price">
        <?php if ($property->property_status == 'rent') {
            echo $total_price . $separator . (!empty($property->rent_period) ? $property->rent_period : Text::_('COM_SPPROPERTY_RENT_PERIOD_DEFAULT'));
        } else {
            echo $total_price;
        }
        ?>
    </p>

    <p class="spproperty-per-sft">
        <?php echo $property->price;
        if (isset($cParams['show_unit']) && $cParams['show_unit']) {
            echo $separator;
            echo empty($cParams['measurement']) ?  Text::_('COM_SPPROPERTY_PROPERTIES_SQFT') : $cParams['measurement'];
        } else {
            echo  Text::_('COM_SPPROPERTY_PROPERTY_PRICE_PER_UNIT');
        } ?>
    </p>
    <?php } ?>
<?php } ?>

