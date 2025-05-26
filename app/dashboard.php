<?php

define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header.php';
include __DIR__ . '/templates/client_nav.php';

#checkUserType('Panettiere');

$idUtente = getCurrentUserId();

#$pageTitle = "Ordini DeliBread";




?>
