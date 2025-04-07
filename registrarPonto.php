<?php
date_default_timezone_set('America/Sao_Paulo');

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require __DIR__ . '/conexao.php';
$conn = conexao();

$user = $_SESSION['user'];
$stmt = $conn->prepare("INSERT INTO entries (id_user, entry) VALUES (:id_user, :entry)");
$entryTime = date('Y-m-d H:i:s'); 
$stmt->bindParam(':entry', $entryTime);
$stmt->bindParam(':id_user', $user->id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
