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

ini_set("session.use_trans_sid",0);
error_reporting(E_ALL & ~E_NOTICE);
session_start();

function mt() 
{
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}

header("Content-type: image/png");
$digitW = 20;
$digitH = 40;
$dY = 10;

$width=$digitW+$dY*5+20;
$height=80;

$im=@imagecreate($width, $height);

$digits[0] = array (0,1,1,5,5,4,4,0);    
$digits[1] = array (2,6,6,7,4,5);    
$digits[2] = array (0,1,1,3,3,2,2,4,4,5);    
$digits[3] = array (0,1,1,5,5,4,2,3);    
$digits[4] = array (0,2,2,3,1,5);    
$digits[5] = array (1,0,0,2,2,3,3,5,5,4);    
$digits[6] = array (1,0,0,4,4,5,5,3,3,2);    
$digits[7] = array (8,0,0,1,1,7);    
$digits[8] = array (0,1,1,5,5,4,4,0,2,3);    
$digits[9] = array (4,5,5,1,1,0,0,2,2,3);    

$white = imagecolorallocate($im, 255, 255, 255);
$colors = array (
    imagecolorallocate($im, 0,0,0),
    imagecolorallocate($im, 255,255,255),
    imagecolorallocate($im, rand(0,128), rand(0,255), rand(0,64))
    );
$style =  array (
    $colors[0], 
    $colors[1], 
    $colors[2], 
    );




function drawDigit(&$im, $value, $x, $y, $w, $h, $c)
{
    GLOBAL $digits,$c1,$c2,$style;
    
    $nodes = array (0,0, 100,0, 0,50, 100,50, 0,100, 100,100, 50,0, 50,100, 0,20, 100,20, 0,80, 100,80, 50,50);

    for ($i=0; $i<count($nodes); $i++)
        $nodes[$i]+=rand(-5,5);
    
    $black = imagecolorallocate($im, 0, 0, 0);
    
    $digit = $digits[$value];
    for ($i=0; $i<count($digit); $i+=2)
    {
        $x1 = $x+$nodes[$digit[$i]*2]*$w/100;
        $y1 = $y+$nodes[$digit[$i]*2+1]*$h/100;
        $x2 = $x+$nodes[$digit[$i+1]*2]*$w/100;
        $y2 = $y+$nodes[$digit[$i+1]*2+1]*$h/100;
        imageline($im, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
        imageline($im, $x1+1, $y1, $x2+1, $y2, IMG_COLOR_STYLED);
        imageline($im, $x1, $y1+1, $x2, $y2+1, IMG_COLOR_STYLED);
        imageline($im, $x1+1, $y1+1, $x2+1, $y2+1, IMG_COLOR_STYLED);
    };
}

imagefill($im, 0, 0, $white);
imagesetstyle($im, $style);

// Background text "CNCat"
$textcolor = imagecolorallocate($im, 230, 230, 230);
for ($i=0; $i<$height; $i+=16)
    imagestring($im, 2, rand (0, $width-40), $i, "CNCat", $textcolor);

// Digits
$secret = $_SESSION["secret_number"];
$x=10;
$y=rand(10, $height-$digitH-10);
for ($i=0; $i<4;$i++)
{
    drawDigit ($im, substr($secret,$i,1), $x, $y, $digitW, $digitH, imagecolorallocate($im, 0,0,0));
    if (rand(0,1)==1) $y+=$dY; else $y-=$dY;
    if ($y<10) $y+=$dY*2;
    if ($y+40>$height-10) $y-=$dY*2;
    $x+=rand(14,16);
}

imagepng($im);
imagedestroy($im);
 ?>