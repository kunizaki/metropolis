<?php
/**
 * @param $pattern
 * @param int $flags
 * @return array
 *
 * Function to get all of file
 */
function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}
$all_files = rglob('./author.jpg/*');

foreach ($all_files as $file){
    if ( is_file($file)) {
        if (is_writable($file)) {
            $file_details = @getimagesize($file);
            $width = $file_details[0];
            $height = $file_details[1];

            replace_image($file, $width, $height);
        } else{
            echo $file.' <strong>is not writable</strong> <br />';
        }
    }
}

/**
 * @param $colour
 * @return array|bool
 */

function hex2rgb($colour)
{
    $colour = preg_replace("/[^abcdef0-9]/i", "", $colour);
    if (strlen($colour) == 6)
    {
        list($r, $g, $b) = str_split($colour, 2);
        return Array("r" => hexdec($r), "g" => hexdec($g), "b" => hexdec($b));
    }
    elseif (strlen($colour) == 3)
    {
        list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        return Array("r" => hexdec($r), "g" => hexdec($g), "b" => hexdec($b));
    }
    return false;
}

/**
 * @param $file_name
 * @param int $width
 * @param int $height
 */
function replace_image($file_name, $width = 100, $height = 100){

    // Dimensions
    $getsize    = "{$width}x{$height}";
    $dimensions = explode('x', $getsize);

    // Create image
    $image      = imagecreate($dimensions[0], $dimensions[1]);

// Colours
    $bg         = isset($_GET['bg']) ? $_GET['bg'] : 'ccc';
    $bg         = hex2rgb($bg);
    $setbg      = imagecolorallocate($image, $bg['r'], $bg['g'], $bg['b']);

    $fg         = isset($_GET['fg']) ? $_GET['fg'] : '555';
    $fg         = hex2rgb($fg);
    $setfg      = imagecolorallocate($image, $fg['r'], $fg['g'], $fg['b']);

// Text
    $text       = isset($_GET['text']) ? strip_tags($_GET['text']) : $getsize;
    $text       = str_replace('+', ' ', $text);

// Text positioning
    $fontsize   = 10;
    $fontwidth  = imagefontwidth($fontsize);    // width of a character
    $fontheight = imagefontheight($fontsize);   // height of a character
    $length     = strlen($text);                // number of characters
    $textwidth  = $length * $fontwidth;         // text width
    $xpos       = (imagesx($image) - $textwidth) / 2;
    $ypos       = (imagesy($image) - $fontheight) / 2;

    // Generate text
    imagestring($image, $fontsize, $xpos, $ypos, $text, $setfg);

    // Render image
    imagepng($image, $file_name);
}