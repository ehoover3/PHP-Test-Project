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
        $records = $this->fetchRecordsWithShipDate();
        $updatedCount = $this->processRecords($records);
        return $updatedCount;
    }

    private function fetchRecordsWithShipDate()
    {
        $sql = "SELECT orderid, comments, shipdate_expected 
                FROM sweetwater_test 
                WHERE comments LIKE '%Expected Ship Date:%'";
        $result = $this->conn->query($sql);

        if ($result === false) {
            throw new Exception('Query failed: ' . $this->conn->error);
        }

        return $result;
    }

    private function processRecords($records)
    {
        $updatedCount = 0;

        while ($row = $records->fetch_assoc()) {
            if ($this->updateRecord($row)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    private function updateRecord($row)
    {
        $orderid = $row['orderid'];
        $comments = $row['comments'];
        $shipdateExpected = $row['shipdate_expected'];

        if (!preg_match('/Expected Ship Date:\s*(\d{2}\/\d{2}\/\d{2})/', $comments, $matches)) {
            return false;
        }

        $date = DateTime::createFromFormat('m/d/y', $matches[1]);
        if (!$date) {
            return false;
        }

        $formattedDate = $date->format('Y-m-d');
        $updatedComments = preg_replace('/Expected Ship Date:\s*\d{2}\/\d{2}\/\d{2}\s*/', '', $comments);

        return $this->executeUpdate($orderid, $formattedDate, $updatedComments, $shipdateExpected);
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

        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function close()
    {
        $this->conn->close();
    }
}
