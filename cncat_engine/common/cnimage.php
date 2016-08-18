<?php
/*******************************************************************************
 * CNCat 4.4 
 * Copyright (c) "CN-Software" Ltd. 
 * http://www.cn-software.com/cncat/
 * ----------------------------------------------------------------------------
 * Please do not modify this header!
 *
 * If you change the original code, we do not guarantee the correct functioning
 * of the program and correct updates.
 * See full text of license agreement in cncat-license.txt file located at the 
 * root folder of the web directory.
*******************************************************************************/


function cn_image_process($filename) {
    return cn_image_proccess($filename);
}

function cn_image_proccess($filename) {
    global $CNCAT;

    $img_data = file_get_contents($filename);

    $image_data = '';
    $thumb_data = '';
    $image_mime = '';

    if (($info = getimagesize($filename)) && ($original_image = @imagecreatefromstring($img_data))) {
        if ($info[2] == IMAGETYPE_PNG) {
            $image_mime = "image/png";
        } elseif ($info[2] == IMAGETYPE_GIF) {
            $image_mime = "image/gif";
        } else {
            $image_mime = "image/jpeg";
        }

        $image = cn_image_resize($original_image, $CNCAT["config"]["image_width"], $CNCAT["config"]["image_height"]);
        $thumb_image = cn_image_resize($image, $CNCAT["config"]["image_twidth"], $CNCAT["config"]["image_theight"]);

        ob_start();
        if ($info[2] == IMAGETYPE_PNG) {
            @imagepng($image);
        } elseif ($info[2] == IMAGETYPE_GIF) {
            @imagegif($image);
        } else {
            @imagejpeg($image, null, 80);
        }
        $image_data = ob_get_clean();

        ob_start();
        if ($info[2] == IMAGETYPE_PNG) {
            @imagepng($thumb_image);
        } elseif ($info[2] == IMAGETYPE_GIF) {
            @imagegif($thumb_image);
        } else {
            @imagejpeg($thumb_image, null, 80);
        }
        $thumb_data = ob_get_clean();
    } else {
        return false;
    }

    return array(
        'image_data' => $image_data,
        'image_mime' => $image_mime,
        'thumb_data' => $thumb_data,
    );
}

function cn_image_resize($image, $width, $height) {
    if (!$width || !$height) {
        return $image;
    }

    $img_width = imagesx($image);
    $img_height = imagesy($image);

    $size_cofx = 1;
    $size_cofy = 1;

    if ($width > 0) {
        $size_cofx = $width / $img_width;
    }

    if ($height > 0) {
        $size_cofy = $height / $img_height;
    }

    $size_cof = min($size_cofx, $size_cofy);

    if ($size_cof >= 1) {
        return $image;
    }

    if ($size_cofx < $size_cofy) {
        $new_width = $width;
        $new_height = floor($img_height * $size_cof);
    } else {
        $new_width = floor($img_width * $size_cof);
        $new_height = $height;
    }

    $new_image = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);

    return $new_image;
}
?>
