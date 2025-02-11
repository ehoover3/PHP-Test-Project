<!-- readComments.php -->
<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/updateCommentsInBatchProcess.php';

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_NAME']);
$comments = getOrderComments($conn);
sendResponse($comments);
$conn->close();

function getOrderComments($conn)
{
    $sql = "SELECT orderid, comments, shipdate_expected FROM sweetwater_test";
    $result = $conn->query($sql);

    if ($result === false) {
        sendResponse(['message' => 'Query failed: ' . $conn->error]);
        exit;
    }

    if ($result->num_rows > 0) {
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = [
                'orderid' => $row['orderid'],
                'comments' => $row['comments'],
                'shipdate_expected' => $row['shipdate_expected'] ? $row['shipdate_expected'] : null
            ];
        }
        return $comments;
    } else {
        return ['message' => 'No results found.'];
    }
}

function sendResponse($data)
{
    echo json_encode($data);
}
