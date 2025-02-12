<?php

class Database
{
    private $conn;

    public function __construct()
    {
        require_once __DIR__ . '/vendor/autoload.php';

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $this->connect();
    }

    private function connect()
    {
        $this->conn = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME']
        );

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function getOrderComments()
    {
        $sql = "SELECT orderid, comments, shipdate_expected FROM sweetwater_test";
        $result = $this->conn->query($sql);

        if ($result === false) {
            return ['message' => 'Query failed: ' . $this->conn->error];
        }

        if ($result->num_rows > 0) {
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                $comments[] = [
                    'orderid' => $row['orderid'],
                    'comments' => $row['comments'],
                    'shipdate_expected' => $row['shipdate_expected'] ?: null
                ];
            }
            return $comments;
        }

        return ['message' => 'No results found.'];
    }

    public function updateShipDate($orderId, $shipDateTime)
    {
        $sql = "UPDATE sweetwater_test SET shipdate_expected = ? WHERE orderid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $shipDateTime, $orderId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function close()
    {
        $this->conn->close();
    }
}
