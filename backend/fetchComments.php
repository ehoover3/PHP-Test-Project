<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/database.php';

$db = new Database();
$comments = $db->getOrderComments();
echo json_encode($comments);
$db->close();
