<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
$records = fetchRecords($conn);
$updatedCount = processRecords($conn, $records);
$conn->close();

echo json_encode(['message' => "Updated $updatedCount records successfully."]);

function fetchRecords($conn)
{
    $sql = "SELECT orderid, comments, shipdate_expected FROM sweetwater_test WHERE comments LIKE '%Expected Ship Date:%'";
    $result = $conn->query($sql);

    if ($result === false) {
        die(json_encode(['message' => 'Query failed: ' . $conn->error]));
    }

    return $result;
}

function processRecords($conn, $records)
{
    $updatedCount = 0;

    while ($row = $records->fetch_assoc()) {
        if (updateRecord($conn, $row)) {
            $updatedCount++;
        }
    }

    return $updatedCount;
}

function updateRecord($conn, $row)
{
    $orderid = $row['orderid'];
    $comments = $row['comments'];
    $shipdateExpected = $row['shipdate_expected'];

    if (preg_match('/Expected Ship Date:\s*(\d{2}\/\d{2}\/\d{2})/', $comments, $matches)) {
        $date = DateTime::createFromFormat('m/d/y', $matches[1]);
        if ($date) {
            $formattedDate = $date->format('Y-m-d');
            $updatedComments = preg_replace('/Expected Ship Date:\s*\d{2}\/\d{2}\/\d{2}\s*/', '', $comments);
            return executeUpdate($conn, $orderid, $formattedDate, $updatedComments, $shipdateExpected);
        }
    }

    return false;
}

function executeUpdate($conn, $orderid, $formattedDate, $updatedComments, $shipdateExpected)
{
    if ($shipdateExpected === null || $shipdateExpected == "0000-00-00") {
        $updateSql = "UPDATE sweetwater_test SET shipdate_expected = ?, comments = ? WHERE orderid = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssi", $formattedDate, $updatedComments, $orderid);
    } else {
        $updateSql = "UPDATE sweetwater_test SET comments = ? WHERE orderid = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $updatedComments, $orderid);
    }

    $stmt->execute();
    $stmt->close();

    return true;
}
