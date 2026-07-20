<?php

/**
* @package mod_spproperty_emi_calculator
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$doc = Factory::getDocument();
$doc->addScriptDeclaration("
    jQuery(function($){
        $('#emi-calculator" . $module->id . "').spemicalc({
            module_id: " . $module->id . ",
            currency: '" . explode(':', $cParams->get('currency', 'USD:$'))[1] . "',
            autosubmit: true
        });
    });
");

?>

<div id="mod-sp-property-emi-calculator<?php echo $module->id; ?>" class="sp-property-emi-calculator <?php echo $params->get('moduleclass_sfx'); ?>">
    <div id="emi-calculator<?php echo $module->id; ?>">
        <form action="#" id="spec-form<?php echo $module->id; ?>">
            <div class="spec-container">
                <div class="spec-graph" style="display: none;">
                    <div class="spec-display-graph">
                        <canvas id="spec-chart<?php echo $module->id;?>"></canvas>
                    </div>
                    <div class="spec-display-info">
                        <div class="interest-payable"><?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_TOTAL_INTEREST_PAYABLE'); ?><p class="interest-payable-value"></strong></div>
                        <div class="principal-and-interest"><?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_TOTAL_PRINCIPAL_PLUS_INTEREST_PAYABLE'); ?><p class="principal-and-interest-value"></p></div>
                    </div>
                </div>
                <div class="spec-input-container">
                    <div class="sppb-row">
                        <div class="form-group col-sm-6">
                            <label for="" class="control-label"><?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_LOAN_AMOUNT'); ?></label>
                            <input type="number" step='.01' class="form-control spec-loan-amount" placeholder="<?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_LOAN_AMOUNT_PLACEHOLDER'); ?>" name="spec-loan-amount" id="spec-load-amount" required='required' value="5000000" >
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="control-label"><?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_INTEREST_RATE'); ?></label>
                            <input type="number" step=".01" class="form-control spec-interest" placeholder="<?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_SPEC_INTEREST_PLACEHOLDER'); ?>" name="spec-interest" id="spec-interest" required='required' value="10" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_LOAN_TENURE'); ?></label>
                        <div class="sppb-row">
                            <div class="col-sm-6">
                                <input type="number" class="form-control spec-tenure-period-year" name="spec-tenure-period-year" id="spec-tenure-period-year" placeholder="<?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_HINT_YEAR')?>" required='required' value="5">
                            </div>
                            <div class="col-sm-6">
                                <input type="number" class="form-control spec-tenure-period-month" name="spec-tenure-period-month" id="spec-tenure-period-month" placeholder="<?php echo Text::_('MOD_SPPROPERTY_EMI_CALCULATOR_HINT_MONTH')?>">
                            </div>
                        </div>
                    </div>
                    <div>
                        <input type="submit" class="sppb-btn sppb-btn-primary sppb-btn-block" value="Calculate">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>