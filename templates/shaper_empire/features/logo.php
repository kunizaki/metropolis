<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2023 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

class Helix3FeatureLogo {

	private $helix3;
	public $position;

	public function __construct( $helix3 ){
		$this->helix3 = $helix3;
		$this->position = $this->helix3->getParam('logo_position', 'logo');
	}

	public function renderFeature() {

		//Logo Image
		if( $this->helix3->getParam('logo_type') == 'image' ) {
			jimport('joomla.image.image');

			if( $this->helix3->getParam('logo_image') ) {
				$path 		  = JPATH_ROOT . '/' . $this->helix3->getParam('logo_image');
			} else {
				$path 	    = JPATH_ROOT . '/templates/' . $this->helix3->getTemplate() . '/images/presets/' . $this->helix3->Preset() . '/logo.png';
			}

			if ($this->helix3->getParam('scroll_logo')) {
				$scroll_path 		  = JPATH_ROOT . '/' . $this->helix3->getParam('scroll_logo');
			} else {
				$scroll_path 	    = JPATH_ROOT . '/templates/' . $this->helix3->getTemplate() . '/images/presets/' . $this->helix3->Preset() . '/logo-scroll.png';
			}

			if(file_exists($path)) {
				$image  = new JImage( $path );
				$width 	= $image->getWidth();
				$height = $image->getHeight();
			} else {
				$width 	= '';
				$height = '';
			}
			// has scroll logo
			if(file_exists($scroll_path)) {
				$scroll_image  = new JImage( $scroll_path );
				$scroll_width  = $scroll_image->getWidth();
				$scroll_height = $scroll_image->getHeight();
			} else {
				$scroll_width 	= '';
				$scroll_height = '';
			}

		}

		$html  = '';
		$custom_logo_class = '';
		$sitename = JFactory::getApplication()->get('sitename');

		if( $this->helix3->getParam('mobile_logo') ) {
			$custom_logo_class = ' d-none d-lg-block';
		}

		$html .= '<a class="logo" href="' . JURI::base(true) . '/">';

		if( $this->helix3->getParam('logo_type') == 'image' ) {

				$scroll_logo = ($this->helix3->getParam('scroll_logo')) ? 'has-scroll-logo' : 'no-scroll-logo';
				$ratina_scroll_logo = ($this->helix3->getParam('scroll_logo_2x')) ? 'has-ratina-scroll-logo' : 'no-ratina-scroll-logo';
				$html .= '<h1 class="' . $scroll_logo . ' ' . $ratina_scroll_logo . '">';

				if ($this->helix3->getParam('logo_image')) {
					$html .= '<img class="sp-default-logo'. $custom_logo_class .'" src="' . $this->helix3->getParam('logo_image') . '" srcset="'. ($this->helix3->getParam('logo_image_2x') ? $this->helix3->getParam('logo_image_2x') . ' 2x' : '') .'" alt="'. $sitename .'">';
				} else{
					$html .= '<img class="sp-default-logo'. $custom_logo_class .'" src="' . $this->helix3->getTemplateUri() . '/images/presets/' . $this->helix3->Preset() . '/logo.png" alt="'. $sitename .'">';
				}

				if ($this->helix3->getParam('scroll_logo')) {
					$html .= '<img class="sp-scroll-logo'. $custom_logo_class .'" src="' . $this->helix3->getParam('scroll_logo') . '" srcset="'. ($this->helix3->getParam('scroll_logo_2x') ? $this->helix3->getParam('scroll_logo_2x') . ' 2x' : '') .'" alt="'. $sitename .'">';
				} else {
					$html .= '<img class="sp-scroll-logo'. $custom_logo_class .'" src="' . $this->helix3->getTemplateUri() . '/images/presets/' . $this->helix3->Preset() . '/logo-scroll.png" alt="'. $sitename .'">';
				}


				if( $this->helix3->getParam('mobile_logo') ) {
					$html .= '<img class="sp-default-logo d-block d-lg-none" src="' . $this->helix3->getParam('mobile_logo') . '" alt="'. $sitename .'">';
				}

				$html .= '</h1>';


		} else {
			if( $this->helix3->getParam('logo_text') ) {
				$html .= '<h1>' . $this->helix3->getParam('logo_text') . '</h1>';
			} else {
				$html .= '<h1>' . $sitename . '</h1>';
			}
		}

		$html .= '</a>';

		return $html;
	}

}
