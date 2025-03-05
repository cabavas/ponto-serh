<?php
function conexao() {
    $host = 'db';
    $dbname = 'ponto_serh';
    $username = 'root';
    $password = 'root';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die('Erro na conexão com o banco de dados: ' . $e->getMessage());
    }
}
