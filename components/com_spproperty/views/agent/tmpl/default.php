<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;

$doc = Factory::getDocument();
$doc->addScriptdeclaration('var spproperty_url="' . Uri::base() . 'index.php?option=com_spproperty";');

if ($this->map_type == 'leaflet') {
    $doc->addStyleDeclaration('
		#agent-map{
			height: 500px;
		}
		.leaflet-bottom{
			bottom: 0;
			z-index: 1!important;
		}
		.spproperty * {
			z-index: 2;
		}

	');
}

$mod_pos_agent_right = $doc->countModules('spproperty-agent-right');
?>

<div id="spproperty-agent" class="spproperty spproperty-view-agent">
    <div class="row">
        <div class="spproperty-col-sm-<?php echo $mod_pos_agent_right ? '8' : '12'; ?>">
            <div class="agent-info">
                <?php echo LayoutHelper::render('agents.agent', array('agent' => $this->item, 'desc_limit' => false, 'params' => $this->cParams)); ?>
            </div>
            
            <?php if (count($this->plocations)) { ?>
                <div class="spproperty-agent-map-widget">
                    <?php if ($this->map_type == 'leaflet') { ?>
                        <div id="agent-map"></div>
                    <?php } else { ?>
                        <div id="spproperty-agent-map" class="spproperty-agent-map" style="width: 100%; height: 500px;" data-locations='<?php echo json_encode($this->plocations); ?>'> </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php if ($this->cParams->get('show_contact', 1)) { ?>
                <div class="agent-contact-from">
                    <div class="contact-from-title text-center">
                        <h2 class="title-heading"><?php echo Text::_('COM_SPPROPERTY_AGENT_CONTACT_TITLE'); ?></h2>
                        <p class="title-subheading"><?php echo Text::_('COM_SPPROPERTY_AGENT_CONTACT_DESC'); ?></p>
                    </div>
                    <form class="spproperty-agent-form">
                        <div class="controls controls-row">
                            <input type="text" name="name" id="name" class="col-sm-4" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_FULLNAME'); ?>">
                            <input type="email" name="email" id="email" class="col-sm-4" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_EMAIL'); ?>">
                            <input type="text" name="phone" id="phone" class="col-sm-4" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_PHONE'); ?>">
                        </div>
                        <div class="controls controls-row">
                            <input id="subject" name="subject" type="text" class="col-sm-12" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_SUBJECT'); ?>">
                        </div>
                        <div class="controls">
                            <textarea id="message" name="message" class="col-sm-12" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_MESSAGE'); ?>" rows="5"></textarea>
                            <input type="hidden" name="agnt_email" value="<?php echo base64_encode($this->item->email); ?>">
                        </div>
                        <div class="controls">
                            <input type="hidden" id="showcaptcha" name="showcaptcha" value="<?php echo $this->captcha; ?>">
                            <?php if ($this->captcha) { ?>
                                <div class="input-field">
                                    <?php
                                        PluginHelper::importPlugin('captcha', $this->captchaPlugin);
                                        $dispatcher = Factory::getApplication();
                                        $dispatcher->triggerEvent('onInit', ['dynamic_recaptcha_spmedical']);
                                        $recaptcha = $dispatcher->triggerEvent('onDisplay', array(null, 'dynamic_recaptcha_spmedical', 'class="spproperty-dynamic-recaptcha"'));

                                        echo (isset($recaptcha[0])) ? $recaptcha[0] : '<p class="spproperty-alert-warning">' . Text::sprintf('COM_SPMEDICAL_CAPTCHA_NOT_INSTALLED', strtoupper($this->captchaPlugin)) . '</p>' ;                                      
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="controls spproperty-row">
                            <?php if ($this->contact_tac) { ?>
                                <div class="input-field spproperty-col-sm-10">
                                    <label class="form-checkbox">
                                        <input type="checkbox" id="tac" name="tac" value="tac" required="true" data-apptac="true">
                                        <?php $contact_tac_text = !empty($this->contact_tac_text) ? trim($this->contact_tac_text) : '';
                                        echo empty($contact_tac_text) ? Text::_('COM_SPPROPERTY_TERMS_AND_CONDITIONS') : $contact_tac_text; ?>
                                    </label>
                                </div>

                                <div class="spproperty-col-sm-2">
                                    <button type="submit" id="contact-submit"  class="btn btn-primary btn-sm input-medium pull-right"><?php echo Text::_('COM_PROPERTY_FORMBTN_SUBMIT'); ?></button>
                                </div>
                            <?php } else {?>
                                <div class="spproperty-col-sm-12">
                                    <button type="submit" id="contact-submit"  class="btn btn-primary btn-sm input-medium pull-right"><?php echo Text::_('COM_PROPERTY_FORMBTN_SUBMIT'); ?></button>
                                </div>
                            <?php } ?>
                            
                        </div>
                    </form>
                    <div style="display:none;margin-top:10px;" class="spproperty-cont-status"></div>
                </div> <!-- /.agent-contact-from -->
            <?php } ?>

            <div class="agent-property-list">
                <div class="agent-properties-from-title text-center">
                    <h2 class="title-heading"><?php echo Text::_('COM_SPPROPERTY_AGENT_MYPROPERTIES'); ?></h2>
                    <p class="title-subheading"><?php echo Text::_('COM_SPPROPERTY_AGENT_MYPROPERTIES_DESC'); ?></p>
                </div> <!-- /.agent-properties-from-title -->
                <div class="agent-properties">
                    <?php echo LayoutHelper::render('properties.properties', array('properties' => $this->agent_properties, 'columns' => $this->properties_columns)); ?>
                </div> <!-- /.agent-properties -->
            </div> <!-- /.agent-property-list -->
        </div>
        <?php if ($mod_pos_agent_right) { ?>
            <div class="spproperty-col-sm-4">
                <?php
                    $modules = ModuleHelper::getModules('spproperty-agent-right');
                    $attribs['style'] = 'sp_xhtml';

                foreach ($modules as $key => $module) {
                    echo ModuleHelper::renderModule($module, $attribs);
                }
                ?>
            </div>
        <?php } ?>
    </div>
</div> <!-- /.spproperty-agent -->

<?php if ($this->map_type == 'leaflet') { ?>
    <script>
        jQuery(function($){
            var markers = [
            <?php
            foreach ($this->plocations as $location) {
                $latlon = explode(',', $location['location']);
                $title  = $location['title'];
                ?>
                    {
                        'lat' : '<?php echo $latlon[0]; ?>',
                        'lon' : '<?php echo $latlon[1]; ?>',
                        'text': `<?php echo $title; ?>`,
                    },
                    <?php
            }
            ?>
            ];
            var mapbox_token = "<?php echo $this->mapbox_token; ?>";
            var map_view    = "<?php echo $this->map_view; ?>";
            var title = "<?php echo $this->item->title; ?>";
            $("#agent-map").spleaflet({
                'markers' : markers,
                'token'   : mapbox_token,
                'view'    : map_view,
                'map'     : 'agent-map',
                'zoom'    : 3
            });
        });
    </script>
<?php } ?>