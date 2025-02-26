<?php

function conexao() {
    $host = "db";
    $user = "root";
    $pass = "root";
    $dbname = "ponto_serh";
    $port = 3306;
    
    try {
        $conn = new PDO("mysql:host=$host;port=$port;dbname=" . $dbname, $user, $pass);
        $conn->exec("SET time_zone = '-03:00'");
        return $conn;
    } catch (PDOException $err) {
        echo "Erro: Conexão com banco de dados não realizado com sucesso. Erro gerado " . $err->getMessage();
    }
}
