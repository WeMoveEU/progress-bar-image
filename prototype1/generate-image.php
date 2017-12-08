<?php

$json = file_get_contents('http://dataviz.wemove.eu/public/question/f6a48e99-e64d-4fa3-b7dc-4477c2537412.json');
$obj = json_decode($json);
$count = $obj[0]->count;
$goal = 1000;
$progress = $count / $goal;


// Color versions

$tealSet = array(
  "name" => "teal",
  "done" => "#07B1B2",
  "upcoming" => "#82D8D9",
  "divider" => "#312783");

$purpleSet = array(
  "name" => "purple",
  "done" => "#941B80",
  "upcoming" => "#D45BC0",
  "divider" => "#640050");

$yellowSet = array(
  "name" => "yellow",
  "done" => "#E67900",
  "upcoming" => "#F0E500",
  "divider" => "#C61215");

$colorSets = array($tealSet, $purpleSet, $yellowSet);
$colorTransparent = new ImagickPixel("none");

// Size versions

$regularSize = array(
  "name" => "regular",
  "imageWidth" => 480,
  "imageHeight" => 75,
  "goalBarHeight" => 25,
  "goalBarWidth" => 420,
  "goalBarRounding" => 5,
  "triangleSize" => 4,
  "triangleOffset" => 2,
  "bottomTextOffset" => 17,
  "topTextOffset" => 8
);

$sizeSets = array($regularSize);


foreach ($sizeSets as $sizeSet) {

  $imageWidth = $sizeSet["imageWidth"];
  $imageHeight = $sizeSet["imageHeight"];
  $goalBarHeight = $sizeSet["goalBarHeight"];
  $goalBarWidth = $sizeSet["goalBarWidth"];
  $goalBarRounding = $sizeSet["goalBarRounding"];
  $triangleSize = $sizeSet["triangleSize"];
  $triangleOffset = $sizeSet["triangleOffset"];
  $bottomTextOffset = $sizeSet["bottomTextOffset"];
  $topTextOffset = $sizeSet["topTextOffset"];

  $goalBarLeftMargin = $goalBarRightMargin = ($imageWidth - $goalBarWidth) / 2;
  $goalBarTopMargin = ($imageHeight - $goalBarHeight) / 2 ;
  $goalBarBottomMargin = ($imageHeight - $goalBarHeight) / 2;

  // Don't get longer than max
  $progressChange = $progress < 1 ?
    $goalBarLeftMargin + ($progress * $goalBarWidth) : $goalBarLeftMargin + $goalBarWidth;

  foreach ($colorSets as $colorSet) {

    $draw = new ImagickDraw();
    $draw->setFontSize(15);
    $draw->setStrokeAntialias(TRUE);
    $draw->setTextAntialias(TRUE);
    $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
    $draw->annotation($goalBarLeftMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "0");
    $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
    $draw->annotation($imageWidth - $goalBarRightMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "$goal");
    $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
    $draw->annotation($progressChange, $goalBarTopMargin - $topTextOffset, "$count");


    $draw->setStrokeColor($colorSet["done"]);
    $draw->setFillColor($colorSet["done"]);
    $draw->roundRectangle(
      $goalBarLeftMargin, $goalBarTopMargin,
      $progressChange, $imageHeight - $goalBarBottomMargin,
      $goalBarRounding, $goalBarRounding);

    $draw->setStrokeColor($colorSet["upcoming"]);
    $draw->setFillColor($colorSet["upcoming"]);
    $draw->roundRectangle(
      $progressChange, $goalBarTopMargin,
      $imageWidth - $goalBarRightMargin, $imageHeight - $goalBarBottomMargin,
      $goalBarRounding, $goalBarRounding);

    $topTriangle = [
      [
        'x' => $progressChange - $triangleSize,
        'y' => $goalBarTopMargin - $triangleOffset,
      ],
      [
        'x' => $progressChange + $triangleSize,
        'y' => $goalBarTopMargin - $triangleOffset,
      ],
      [
        'x' => $progressChange,
        'y' => $goalBarTopMargin + $triangleSize - $triangleOffset,
      ],
    ];
    $bottomTriangle = [
      [
        'x' => $progressChange - $triangleSize,
        'y' => $imageHeight - $goalBarBottomMargin + $triangleOffset,
      ],
      [
        'x' => $progressChange + $triangleSize,
        'y' => $imageHeight - $goalBarBottomMargin + $triangleOffset,
      ],
      [
        'x' => $progressChange,
        'y' => $imageHeight - $goalBarBottomMargin - $triangleSize + $triangleOffset,
      ],
    ];


    $draw->setStrokeAntialias(TRUE);
    $draw->setStrokeColor($colorSet["divider"]);
    $draw->setFillColor($colorSet["divider"]);
    $draw->polygon($topTriangle);
    $draw->polygon($bottomTriangle);
    $draw->line($topTriangle[2]['x'], $topTriangle[2]['y'], $bottomTriangle[2]['x'], $bottomTriangle[2]['y']);


    $canvas = new Imagick();
    $canvas->newImage($imageWidth, $imageHeight, $colorTransparent);

    $canvas->drawImage($draw);
    $canvas->setImageFormat('png');

    /* Output the image */
    $canvas->writeImage("progressbar-" . $colorSet["name"] . "-" . $sizeSet["name"] . ".png");
  }
}
?>

