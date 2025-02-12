<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/database.php';

$db = new Database();
$updatedCount = $db->updateCommentsInBatch();
$db->close();

echo json_encode(['message' => "Updated $updatedCount records successfully."]);
