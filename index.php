<?php

require_once 'src/EDevlet.php';
require_once 'src/Scraper.php';

$cityCode = 34;
$date = '22/08/2023';

$edevlet = new EDevlet();
$result = $edevlet->getDataBySelections($cityCode, $date);
// Example JSON Response
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);