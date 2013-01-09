<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

$route['default_controller'] = "byzantin";
$route['(.*)'] = "byzantin/index/$1";
$route[''] = 'byzantin/index';

