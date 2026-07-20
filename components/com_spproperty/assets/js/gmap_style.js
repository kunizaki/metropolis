
/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */


window.addEventListener('load', function () {

    setTimeout(() => {
        const gmStyle = document.querySelector('.gm-style');
        if (gmStyle !== null) {
            $('.gm-style').css({ "z-index": "3" })
        }
    }, 2000);

})