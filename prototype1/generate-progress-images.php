<?php

// Pulling data for progress bar

$json = file_get_contents('http://dataviz.wemove.eu/public/question/f6a48e99-e64d-4fa3-b7dc-4477c2537412.json');
if( $json !== FALSE ) {
  $obj = json_decode($json);
  $count = $obj[0]->count;
  $goal = 1000;
  $progress = $count / $goal;
} else {
  // bail out and do nothing, we don't have numbers
  die(1);
}

// Color versions definitions

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

// Size versions definitions

$shortSize = array(
  "name" => "short",
  "imageWidth" => 300,
  "imageHeight" => 75,
  "goalBarHeight" => 25,
  "goalBarWidth" => 270,
  "goalBarRounding" => 5,
  "triangleSize" => 4,
  "triangleOffset" => 2,
  "bottomTextOffset" => 17,
  "topTextOffset" => 8,
  "fontSize" => 15
);

$regularSize = array(
  "name" => "regular",
  "imageWidth" => 500,
  "imageHeight" => 75,
  "goalBarHeight" => 25,
  "goalBarWidth" => 480,
  "goalBarRounding" => 5,
  "triangleSize" => 4,
  "triangleOffset" => 2,
  "bottomTextOffset" => 17,
  "topTextOffset" => 8,
  "fontSize" => 15
);

$longSize = array(
  "name" => "long",
  "imageWidth" => 720,
  "imageHeight" => 75,
  "goalBarHeight" => 25,
  "goalBarWidth" => 700,
  "goalBarRounding" => 5,
  "triangleSize" => 4,
  "triangleOffset" => 2,
  "bottomTextOffset" => 17,
  "topTextOffset" => 8,
  "fontSize" => 15
);



$sizeSets = array($shortSize, $regularSize, $longSize);


// Let's start looping over sizes to generate N = (color x sizes) images

foreach ($sizeSets as $sizeSet) {

  // let's redefine vars first, for more clarity further down the way
  $imageWidth = $sizeSet["imageWidth"];
  $imageHeight = $sizeSet["imageHeight"];
  $goalBarHeight = $sizeSet["goalBarHeight"];
  $goalBarWidth = $sizeSet["goalBarWidth"];
  $goalBarRounding = $sizeSet["goalBarRounding"];
  $triangleSize = $sizeSet["triangleSize"];
  $triangleOffset = $sizeSet["triangleOffset"];
  $bottomTextOffset = $sizeSet["bottomTextOffset"];
  $topTextOffset = $sizeSet["topTextOffset"];

  // calculating elements positions
  $goalBarLeftMargin = $goalBarRightMargin = ($imageWidth - $goalBarWidth) / 2;
  $goalBarTopMargin = ($imageHeight - $goalBarHeight) / 2 ;
  $goalBarBottomMargin = ($imageHeight - $goalBarHeight) / 2;

  // Don't get longer than the size of whole bar
  $progressChange = $progress < 1 ?
    $goalBarLeftMargin + ($progress * $goalBarWidth) : $goalBarLeftMargin + $goalBarWidth;

  // got sizes roughly calculated, we can now loop over colors

  foreach ($colorSets as $colorSet) {

    $draw = new ImagickDraw();

    // Let's put letters first

    $draw->setFontSize($sizeSet["fontSize"]);
    $draw->setStrokeAntialias(TRUE);
    $draw->setTextAntialias(TRUE);
    $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
    $draw->annotation($goalBarLeftMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "0");
    $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
    $draw->annotation($imageWidth - $goalBarRightMargin, $imageHeight - $goalBarBottomMargin + $bottomTextOffset, "$goal");
    $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
    $draw->annotation($progressChange, $goalBarTopMargin - $topTextOffset, "$count");

    // Now draw the rectangle for progress already made

    $draw->setStrokeColor($colorSet["done"]);
    $draw->setFillColor($colorSet["done"]);
    $draw->roundRectangle(
      $goalBarLeftMargin, $goalBarTopMargin,
      $progressChange, $imageHeight - $goalBarBottomMargin,
      $goalBarRounding, $goalBarRounding);

    // Now fill it up to the goal size with inactive/upcoming color

    $draw->setStrokeColor($colorSet["upcoming"]);
    $draw->setFillColor($colorSet["upcoming"]);
    $draw->roundRectangle(
      $progressChange, $goalBarTopMargin,
      $imageWidth - $goalBarRightMargin, $imageHeight - $goalBarBottomMargin,
      $goalBarRounding, $goalBarRounding);

    // Draw some triangles and lines to coverup the gap between bars

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

    // ...generate and write down final an image

    $canvas = new Imagick();
    $canvas->newImage($imageWidth, $imageHeight, $colorTransparent);

    $canvas->drawImage($draw);
    $canvas->setImageFormat('png');

    /* Output the image */
    $canvas->writeImage("progressbar-" . $colorSet["name"] . "-" . $sizeSet["name"] . ".png");
  }
}
?>

