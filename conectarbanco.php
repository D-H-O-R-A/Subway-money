<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

const config = array(
    'db_user' => 'subwayoriginal_adm',
    'db_pass' => 'subwayoriginal_adm',
    'db_name' => 'subwayoriginal_adm',        
    'db_host' => 'localhost'
);

function connect() {
    $conn = new mysqli(config['db_host'], config['db_user'], config['db_pass'], config['db_name']);
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }
    return $conn;
}

function select( $table, $conditions = []) {
    $conn = connect();
    $query = "SELECT * FROM $table";
    $params = [];
    if (!empty($conditions)) {
        $query .= " WHERE " . join(" AND ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($conditions)));
        $params = array_values($conditions);
    }
    
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $stmt->close();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


function update($table, $values, $conditions) {
    $conn = connect();
    $setClause = join(", ", array_map(function($key) {
        return "$key = ?";
    }, array_keys($values)));
    
    $whereClause = join(" AND ", array_map(function($key) {
        return "$key = ?";
    }, array_keys($conditions)));

    $query = "UPDATE $table SET $setClause WHERE $whereClause";
    $params = array_merge(array_values($values), array_values($conditions));

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $stmt->close();
    return $stmt->affected_rows;
}


function delete($query, $params) {
    $conn = connect();
    return update($conn, $query, $params); // A lógica de DELETE é similar à de UPDATE
}

function insert($query, $params) {
    $conn = connect();
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $stmt->close();
    return $stmt->insert_id;
}

