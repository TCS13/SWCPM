<?php
ob_start();
session_start();
//Connect to database
require_once('connect.php');
include('swc_ws_lib.php');
include('planet_lib.php');
include('message_lib.php');
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>SWC Prospecting Manager</title>
    </head>