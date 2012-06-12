<?php

include 'battlelogapi/BattlelogApi.php';

$api = new BattlelogApi();
$soldier = $api->getBF3Soldier('180351032');
var_dump($soldier->getData());

//echo $api->getUri("bf3/overviewPopulateStats/180351032/None/1/");
