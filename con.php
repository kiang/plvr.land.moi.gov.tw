<?php
$oFh = fopen(__DIR__ . '/address.csv', 'w');
$headerDone = false;
foreach(glob(__DIR__ . '/20*.csv') AS $csvFile) {
  $fh = fopen($csvFile, 'r');
  if(false === $headerDone) {
    fputcsv($oFh, fgetcsv($fh, 2048));
    $headerDone = true;
  } else {
    fgetcsv($fh, 2048);
  }

  while($line = fgetcsv($fh, 2048)) {
    fputcsv($oFh, $line);
  }
}
