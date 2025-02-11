<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['MYSQL_ROOT_PASSWORD'];
$dbname = $_ENV['MYSQL_DATABASE'];

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

$sql = "SELECT orderid, comments, shipdate_expected FROM sweetwater_test";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'orderid' => $row['orderid'],
            'comments' => $row['comments'],
            'shipdate_expected' => $row['shipdate_expected'] ? $row['shipdate_expected'] : null
        ];
    }
    echo json_encode($comments);
} else {
    echo json_encode(['message' => 'No results found.']);
}

$conn->close();
