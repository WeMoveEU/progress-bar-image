<?php

$json = file_get_contents('http://dataviz.wemove.eu/public/question/f6a48e99-e64d-4fa3-b7dc-4477c2537412.json');
$obj = json_decode($json);
$count = $obj[0]->count;
$goal = 1000;
$progress = $count / $goal;

$imageWidth = 480;
$imageHeight = 75;
$colorTransparent = "white";//new ImagickPixel("none");

$goalBarHeight = 25;
$goalBarWidth = 420;
$goalBarRounding = 5;

$goalBarLeftMargin = $goalBarRightMargin = ($imageWidth - $goalBarWidth) / 2;
$goalBarTopMargin = ($imageHeight - $goalBarHeight) / 2 ;
$goalBarBottomMargin = ($imageHeight - $goalBarHeight) / 2;

$triangleSize = 4;
$triangleOffset = 2;


$bottomTextOffset = 17;
$topTextOffset = 8;

// Don't get longer than max
$progressChange = $progress < 1 ?
  $goalBarLeftMargin + ($progress * $goalBarWidth) : $goalBarLeftMargin + $goalBarWidth;

$draw = new ImagickDraw();
$draw->setFontSize(15);
$draw->setStrokeAntialias(true);
$draw->setTextAntialias(true);
$draw->setTextAlignment(\Imagick::ALIGN_LEFT);
$draw->annotation($goalBarLeftMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "0");
$draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
$draw->annotation($imageWidth - $goalBarRightMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "Goal: $goal");
$draw->setTextAlignment(\Imagick::ALIGN_CENTER);
$draw->annotation($progressChange, $goalBarTopMargin - $topTextOffset, "$count");


$draw->setStrokeColor("green");
$draw->setFillColor("green");
$draw->roundRectangle (
  $goalBarLeftMargin , $goalBarTopMargin ,
  $progressChange , $imageHeight - $goalBarBottomMargin ,
  $goalBarRounding , $goalBarRounding );

$draw->setStrokeColor("lightgreen");
$draw->setFillColor("lightgreen");
$draw->roundRectangle (
  $progressChange , $goalBarTopMargin ,
  $imageWidth - $goalBarRightMargin , $imageHeight - $goalBarBottomMargin ,
  $goalBarRounding , $goalBarRounding );

$topTriangle = array(
  [ 'x' => $progressChange - $triangleSize, 'y' => $goalBarTopMargin - $triangleOffset],
  [ 'x' => $progressChange + $triangleSize, 'y' => $goalBarTopMargin - $triangleOffset],
  [ 'x' => $progressChange , 'y' => $goalBarTopMargin + $triangleSize - $triangleOffset] );
$bottomTriangle = array(
  [ 'x' => $progressChange - $triangleSize, 'y' => $imageHeight - $goalBarBottomMargin + $triangleOffset ],
  [ 'x' => $progressChange + $triangleSize, 'y' => $imageHeight - $goalBarBottomMargin + $triangleOffset],
  [ 'x' => $progressChange , 'y' => $imageHeight - $goalBarBottomMargin - $triangleSize + $triangleOffset] );


$draw->setStrokeAntialias(false);
$draw->setStrokeColor("darkgreen");
$draw->setFillColor("darkgreen");
$draw->polygon( $topTriangle );
$draw->polygon( $bottomTriangle );
$draw->line($topTriangle[2]['x'], $topTriangle[2]['y'], $bottomTriangle[2]['x'], $bottomTriangle[2]['y']);



$canvas = new Imagick();
$canvas->newImage($imageWidth, $imageHeight, $colorTransparent);

$canvas->drawImage($draw);
$canvas->setImageFormat('png');

/* Output the image */
header("Content-Type: image/png");
echo $canvas;
?>

