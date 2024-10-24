<?php
require_once 'config.php';

function insertRecord($table, $data) {
    global $pdo;
    $keys = array_keys($data);
    $fields = implode(', ', $keys);
    $placeholders = ':' . implode(', :', $keys);
    
    $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function getRecords($table, $conditions = [], $orderBy = '') {
    global $pdo;
    $sql = "SELECT * FROM $table";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', array_map(function($key) {
            return "$key = :$key";
        }, array_keys($conditions)));
    }
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($conditions);
    return $stmt->fetchAll();
}

function updateRecord($table, $data, $conditions) {
    global $pdo;
    $set = implode(', ', array_map(function($key) {
        return "$key = :$key";
    }, array_keys($data)));
    $where = implode(' AND ', array_map(function($key) {
        return "$key = :condition_$key";
    }, array_keys($conditions)));
    
    $sql = "UPDATE $table SET $set WHERE $where";
    $stmt = $pdo->prepare($sql);
    
    $executeData = $data;
    foreach ($conditions as $key => $value) {
        $executeData["condition_$key"] = $value;
    }
    
    return $stmt->execute($executeData);
}

function deleteRecord($table, $conditions) {
    global $pdo;
    $where = implode(' AND ', array_map(function($key) {
        return "$key = :$key";
    }, array_keys($conditions)));
    
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($conditions);
}