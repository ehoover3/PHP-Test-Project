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

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Order ID: " . $row["orderid"] . "<br>";
        echo "Comments: " . $row["comments"] . "<br>";
        echo "Expected Ship Date: " . ($row["shipdate_expected"] ? $row["shipdate_expected"] : 'NULL') . "<br><br>";
    }
} else {
    echo "No results found.";
}

$conn->close();
