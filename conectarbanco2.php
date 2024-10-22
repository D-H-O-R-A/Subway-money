<?php
class Database {
    private $conn;
    private const CONFIG = array(
        'db_user' => 'subwayoriginal_adm',
        'db_pass' => 'subwayoriginal_adm',
        'db_name' => 'subwayoriginal_adm',        
        'db_host' => 'localhost'
    );

    public function __construct() {
        $this->connect();
    }

    public function __destruct() {
        $this->conn->close();
    }

    private function connect() {
        $this->conn = new mysqli(self::CONFIG['db_host'], self::CONFIG['db_user'], self::CONFIG['db_pass'], self::CONFIG['db_name']);
        if ($this->conn->connect_error) {
            die("Erro na conexão: " . $this->conn->connect_error);
        }
    }

    public function select($table, $conditions = []) {
        $query = "SELECT * FROM $table";
        $params = [];
        if (!empty($conditions)) {
            $query .= " WHERE " . join(" AND ", array_map(function($key) {
                return "$key = ?";
            }, array_keys($conditions)));
            $params = array_values($conditions);
        }
        
        $stmt = $this->conn->prepare($query);
        if ($params) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function update($table, $data, $conditions) {
        // Construindo a parte SET da query SQL
        $setPart = join(", ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($data)));
    
        // Construindo a parte WHERE da query SQL
        $wherePart = join(" AND ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($conditions)));
    
        // Juntando tudo para formar a query de update
        $query = "UPDATE $table SET $setPart WHERE $wherePart";
        
        // Unindo todos os valores em um único array
        $params = array_merge(array_values($data), array_values($conditions));
    
        // Preparando a query
        $stmt = $this->conn->prepare($query);
    
        // Vinculando os parâmetros
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    
        // Executando a query
        $stmt->execute();
    
        // Fechando o statement
        $stmt->close();
    }

    public function insert($table, $data) {
        // Construindo a parte das colunas e dos placeholders da query SQL
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
    
        // Juntando tudo para formar a query de inserção
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        // Preparando a query
        $stmt = $this->conn->prepare($query);
    
        // Vinculando os parâmetros
        $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
    
        // Executando a query
        $stmt->execute();
    
        // Fechando o statement
        $stmt->close();
    }
    
}
