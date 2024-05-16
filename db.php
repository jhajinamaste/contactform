<?php
// DATABASE
$dbname = "contact_form";
$dbuname = "root";
$dbpass = "";
$dbhost = "localhost";

$dsn = "mysql:dbname=$dbname;host=$dbhost;charset=utf8mb4";
$db = new PDO($dsn, $dbuname, $dbpass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// TIMEZONE
date_default_timezone_set("Asia/Kolkata");