<?php

require __DIR__.'/vendor/autoload.php';

$cityCode = 34;
$date = '22/08/2023';

$edevlet = new \Od\EczaneApi\EDevlet();
$result = $edevlet->getDataBySelections($cityCode, $date);

// Example JSON Response
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);