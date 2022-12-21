<?php

/**
 * @package DSG
 * @subpackage cli
 */

/**
 * Include path, change to wherever you want to keep the classes and functions
 */
define('DSG_INCLUDE_PATH', realpath('../../web/includes'));
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . DSG_INCLUDE_PATH);
