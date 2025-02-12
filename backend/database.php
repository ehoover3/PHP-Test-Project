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

    public function updateCommentsInBatch()
    {
        $comments = $this->getOrderComments();
        if (isset($comments['message'])) {
            return 0; // No records to update
        }

        $updatedCount = 0;
        foreach ($comments as $comment) {
            if ($this->processRecord($comment)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    private function processRecord($comment)
    {
        $orderid = $comment['orderid'];
        $comments = $comment['comments'];
        $shipdateExpected = $comment['shipdate_expected'];

        if (preg_match('/Expected Ship Date:\s*(\d{2}\/\d{2}\/\d{2})/', $comments, $matches)) {
            $date = DateTime::createFromFormat('m/d/y', $matches[1]);
            if ($date) {
                $formattedDate = $date->format('Y-m-d');
                $updatedComments = preg_replace('/Expected Ship Date:\s*\d{2}\/\d{2}\/\d{2}\s*/', '', $comments);

                // Update ship date if it's not set
                if ($shipdateExpected === null || $shipdateExpected == "0000-00-00") {
                    $this->updateShipDate($orderid, $formattedDate);
                }

                // Update comments
                return $this->executeUpdate($orderid, $formattedDate, $updatedComments, $shipdateExpected);
            }
        }
        return false;
    }

    private function executeUpdate($orderid, $formattedDate, $updatedComments, $shipdateExpected)
    {
        if ($shipdateExpected === null || $shipdateExpected == "0000-00-00") {
            $updateSql = "UPDATE sweetwater_test SET shipdate_expected = ?, comments = ? WHERE orderid = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("ssi", $formattedDate, $updatedComments, $orderid);
        } else {
            $updateSql = "UPDATE sweetwater_test SET comments = ? WHERE orderid = ?";
            $stmt = $this->conn->prepare($updateSql);
            $stmt->bind_param("si", $updatedComments, $orderid);
        }

        $stmt->execute();
        $stmt->close();
        return true;
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
