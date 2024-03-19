<?php

$routes = glob(__DIR__ . "/api/*.php");

foreach ($routes as $route) require($route);
