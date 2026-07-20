<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Component\ComponentHelper;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

FormHelper::loadFieldClass('list');

class JFormFieldSpcurrency extends ListField
{
      protected $type = 'Spcurrency';
      protected $layout = 'joomla.form.field.list-fancy-select';

      protected function getOptions()
      {
            $type       = (string) $this->element['field_type'];
            $data       = array();
            $cParams    = ComponentHelper::getParams('com_spproperty');

            if ($type == 'currency') {
                        $v    = $cParams->get('currency', 'USD:$');
                        $data = array(
                              ""          => Text::sprintf('COM_SPPROPERTY_SELECT_CURRENCY', $v),
                              "USD:$"     => "United States dollar($)",
                              "ALL:Lek"   => "Albania Lek(Lek)",
                              "AFN:؋"     => "Afghanistan Afghani(؋)",
                              "ARS:$"     => "Argentina Peso($)",
                              "AWG:ƒ"     => "Aruba Guilder(ƒ)",
                              "AUD:$"     => "Australia Dollar($)",
                              "AZN:₼"     => "Azerbaijan Manat(₼)",
                              "BDT:৳"     => "Bangladesh Taka(৳)",
                              "BGN:лв"    => "Bulgaria Lev(лв)",
                              "BRL:R$"    => "Brazil Real(R$)",
                              "BND:$"     => "Brunei Darussalam Dollar($)",
                              "GBP:£"     => "British pound(£)",
                              "BRL:R$"    => "Brazilian Real(R$)",
                              "KHR:៛"     => "Cambodia Riel(៛)",
                              "CAD:$"     => "Canadian Dollar($)",
                              "CZK:Kč"    => "Czech Koruna(Kč)",
                              "DKK:kr."   => "Danish Krone(kr.)",
                              "EUR:€"     => "Euro(€)",
                              "HKD:HK$"   => "Hong Kong Dollar(HK$)",
                              "HUF:Ft"    => "Hungarian Forint(Ft)",
                              "INR:₹"     => "India Rupee(₹)",
                              "ILS:₪"     => "Israeli New Sheqel(₪)",
                              "JPY:¥"     => "Japanese Yen(¥)",
                              "MYR:RM"    => "Malaysian Ringgit(RM)",
                              "MXN:Mex$"  => "Mexican Peso(Mex$)",
                              "NOK:kr"    => "Norwegian Krone(kr)",
                              "NZD:$"     => "New Zealand Dollar($)",
                              "PHP:₱"     => "Philippine Peso(₱)",
                              "PLN:zł"    => "Polish Zloty(zł)",
                              "RUB:₽"     => "Russian Ruble(₽)",
                              "SGD:$"     => "Singapore Dollar($)",
                              "SEK:kr"    => "Swedish Krona(kr)",
                              "CHF:CHF"   => "Swiss Franc(CHF)",
                              "TWD:角"    => "Taiwan New Dollar(角)",
                              "THB:฿"     => "Thai Baht(฿)",
                              "TRY:TRY"   => "Turkish Lira(TRY)"
                        );
            } elseif ($type == 'currency_position') {
                  $v    = $cParams->get('currency_position', 'left');
                  if ($v == 'left') {
                        $v = 'Before';
                  } else {
                        $v = 'After';
                  }
                  $data = array(
                        ""          => Text::sprintf('COM_SPPROPERTY_SELECT_CURRENCY_POSITION', ucfirst($v)),
                        "left"      => Text::_('COM_SPPROPERTY_SELECT_CURRENCY_POSITION_LEFT'),
                        "right"     => Text::_('COM_SPPROPERTY_SELECT_CURRENCY_POSITION_RIGHT')
                  );
            } elseif ($type == 'currency_format') {
                  $v    = $cParams->get('currency_format', 'short');
                  $data = array(
                        ""          => Text::sprintf('COM_SPPROPERTY_SELECT_CURRENCY_FORMAT', ucfirst($v)),
                        "short"     => Text::_('COM_SPPROPERTY_SELECT_CURRENCY_FORMAT_SHORT'),
                        "long"      => Text::_('COM_SPPROPERTY_SELECT_CURRENCY_FORMAT_LONG')

                  );
            }
            $options[] = HTMLHelper::_('select.option', '', '');

            foreach ($data as $key => $item) {
                  $options[] = HTMLHelper::_('select.option', $key, $item);
            }
                  
            return array_merge(parent::getOptions(), $options);
      }
}
