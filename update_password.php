<?php
session_start();
require_once __DIR__ . '/conexao.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $userId = $_SESSION['user']->id;
    
    try {
        $conn = conexao();
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            $_SESSION['success'] = "Senha alterada com sucesso!";
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['error'] = "Senha atual incorreta!";
            header('Location: alterar_senha.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao alterar senha: " . $e->getMessage();
        header('Location: alterar_senha.php');
        exit();
    }
}
