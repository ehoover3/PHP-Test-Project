<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['message' => 'Connection failed: ' . $conn->connect_error]));
}

$sql = "SELECT orderid, comments FROM sweetwater_test WHERE shipdate_expected IS NULL";
$result = $conn->query($sql);

if ($result === false) {
    die(json_encode(['message' => 'Query failed: ' . $conn->error]));
}

$updated_count = 0;

while ($row = $result->fetch_assoc()) {
    $orderid = $row['orderid'];
    $comments = $row['comments'];

    if (preg_match('/Expected Ship Date:\s*(\d{2}\/\d{2}\/\d{2})/', $comments, $matches)) {
        $date = DateTime::createFromFormat('m/d/y', $matches[1]);

        if ($date) {
            $formatted_date = $date->format('Y-m-d');

            $update_sql = "UPDATE sweetwater_test SET shipdate_expected = ? WHERE orderid = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $formatted_date, $orderid);
            $stmt->execute();
            $stmt->close();

            $updated_count++;
        }
    }
}

$conn->close();
echo json_encode(['message' => "Updated $updated_count records successfully."]);
