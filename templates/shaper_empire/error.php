<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

jimport('joomla.filesystem.file');

$doc = JFactory::getDocument();
$params = JFactory::getApplication()->getTemplate('true')->params;

//Favicon
if ($favicon = $params->get('favicon')) {
    $doc->addFavicon(JURI::base(true) . '/' . $favicon);
} else {
    $doc->addFavicon($this->baseurl . '/templates/' . $this->template . '/images/favicon.ico');
}


//Error Logo
if ($logo_image = $params->get('error_logo')) {
    $logo = JURI::base(true) . '/' . $logo_image;
} elseif ($logo_image = $params->get('logo_image')) {
    $logo = JURI::base(true) . '/' . $logo_image;
} else {
    $logo = $this->baseurl . '/templates/' . $this->template . '/images/presets/preset1/logo@2x.png';
}

//Error BG
if ($error_bg = $params->get('error_background')) {
    $error_bg = JURI::base(true) . '/' . $error_bg;

    $error_bg_style = '.error-page-inner{'
            . 'background: url(' . $error_bg . ');'
            . '}';
    $doc->addStyleDeclaration($error_bg_style);
}

//Stylesheets
$custom_css_path = JPATH_ROOT . '/templates/' . $this->template . '/css/custom.css';
if (file_exists($custom_css_path)) {
	$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/custom.css' );
}
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/bootstrap.min.css' );
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/joomla-fontawesome.min.css' );
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/font-awesome-v4-shims.min.css' );
$doc->addStylesheet( $this->baseurl . '/templates/' . $this->template . '/css/template.css' );


$doc->setTitle($this->error->getCode() . ' - ' . $this->title);
$header_contents = ''; 
if(!class_exists('JDocumentRendererHead')) { 
    $head = JPATH_LIBRARIES . '/joomla/document/html/renderer/head.php'; 
    if(file_exists($head)) { 
    require_once($head); 
    } 
}
$header_renderer = new JDocumentRendererHead($doc);
$header_contents = $header_renderer->render(null);
?>
<!DOCTYPE html>
<html class="error-page" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
            <meta name="viewport" content="width=device-width, initial-scale=1">
                <?php echo $header_contents; ?>
                </head>
                <body>
                    <div class="error-page-inner">
                        <div class="container">
                            <div class="row">
                                <div class="error-page-wrap">
                                    <div class="error-page-logo">
                                        <img class="img-resposive error-logo" src="<?php echo $logo; ?>" />
                                    </div>
                                    <p class="error-message"><?php echo $this->error->getMessage(); ?></p>

                                    <?php if ($this->debug) : ?>
                                        <div>
                                            <?php echo $this->renderBacktrace(); ?>
                                            <?php // Check if there are more Exceptions and render their data as well ?>
                                            <?php if ($this->error->getPrevious()) : ?>
                                                <?php $loop = true; ?>
                                                <?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
                                                <?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
                                                <?php $this->setError($this->_error->getPrevious()); ?>
                                                <?php while ($loop === true) : ?>
                                                    <p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
                                                    <p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <?php echo $this->renderBacktrace(); ?>
                                                    <?php $loop = $this->setError($this->_error->getPrevious()); ?>
                                                <?php endwhile; ?>
                                                <?php // Reset the main error object to the base error ?>
                                                <?php $this->setError($this->error); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <a class="btn btn-primary btn-sm error-button" href="<?php echo $this->baseurl; ?>/" title="<?php echo JText::_('HOME'); ?>"> <?php echo JText::_('HELIX_GO_BACK'); ?></a>
                                    <div class="eror-copyright">
                                        <p>&copy; 2016 All Rights Reserved </p>
                                    </div>
                                    <?php echo $doc->getBuffer('modules', '404', array('style' => 'sp_xhtml')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </body>
                </html>