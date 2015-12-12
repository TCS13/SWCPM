<?php

error_reporting(0);//Change to 0 for the main site

define('DB_user', '');
define('DB_password', '');
define('DB_host', '');
define('DB_name', '');
$dbc = mysql_connect(DB_host, DB_user, DB_password);
global $DB_table_prefix;
$DB_table_prefix = 'ted_';
global $site_name;
$site_name = 'http://dot.swc-tf.com/ted_pm/';
mysql_select_db(DB_name);
?>