<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['MYSQL_ROOT_PASSWORD'];
$dbname = $_ENV['MYSQL_DATABASE'];

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT orderid, comments, shipdate_expected FROM sweetwater_test";
$result = $conn->query($sql);

$comments = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'orderid' => $row["orderid"],
            'comments' => $row["comments"],
            'shipdate_expected' => $row["shipdate_expected"] ? $row["shipdate_expected"] : null
        ];
    }
} else {
    $comments = ["message" => "No results found."];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($comments);
