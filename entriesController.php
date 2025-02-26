<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include_once __DIR__ . '/conexao.php';

$conn = conexao();

// Updated SQL query using entry timestamp instead of date
$sql = "SELECT id, entry FROM entries WHERE id_user = :id_user ORDER BY entry ASC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_user', $user->id);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_OBJ);