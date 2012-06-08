<?php

include 'BattlelogApi.php';

$api = new BattlelogApi();

echo $api->getUri("bf3/overviewPopulateStats/180351032/None/1/");
