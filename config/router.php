<?php
// นำเข้าไฟล์เชื่อมต่อฐานข้อมูล
include 'dbcon.php';

function insertData($table, $data)
{
    global $dbcon;

    // Escape table name
    $table = "`" . str_replace("`", "``", $table) . "`";

    // Generate column names and placeholders
    $columns = implode(", ", array_map(fn($col) => "`$col`", array_keys($data)));
    $placeholders = implode(", ", array_fill(0, count($data), "?"));

    // SQL Insert
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->execute(array_values($data));

        return $dbcon->lastInsertId(); // ส่งคืน Last Insert ID
    } catch (PDOException $e) {
        return "Error executing query: " . $e->getMessage();
    }
}

function updateData($table, $data, $condition)
{
    global $dbcon;

    $table = "`" . str_replace("`", "``", $table) . "`";

    $updates = implode(", ", array_map(fn($key) => "`$key` = ?", array_keys($data)));

    $sql = "UPDATE $table SET $updates WHERE $condition";

    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->execute(array_values($data));
        return null; // สำเร็จ
    } catch (PDOException $e) {
        return "Error executing query: " . $e->getMessage();
    }
}

function deleteData($table, $condition)
{
    global $dbcon;

    $table = "`" . str_replace("`", "``", $table) . "`";

    $sql = "DELETE FROM $table WHERE $condition";

    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->execute();
        return null; // สำเร็จ
    } catch (PDOException $e) {
        return "Error executing query: " . $e->getMessage();
    }
}

function selectData($sql, $params = [])
{
    global $dbcon;

    try {
        $stmt = $dbcon->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return "Error executing query: " . $e->getMessage();
    }
}

// ฟังก์ชัน Check Token
function checkToken($a_token)
{
    if (!isset($a_token) || $a_token == '') {
        return ['status' => 'denied', 'message' => 'token not found.'];
    }

    $sqlQuery = "SELECT * FROM access WHERE a_token = :a_token AND a_expired > NOW()";
    $params = [':a_token' => $a_token];

    $results = selectData($sqlQuery, $params);
    if (count($results) > 0) {
        return null;
    } else {
        return ['status' => 'denied', 'message' => 'token expired.'];
    }
}