<?php
session_start();

include_once __DIR__ . '/conexao.php';

$conn = conexao();

$cpf = $_POST['cpf'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE cpf = :cpf";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':cpf', $cpf);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);

if ($user) {
    if (password_verify($password, $user->password)) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = [
            'icon' => 'error',
            'title' => 'Erro',
            'text' => 'A senha digitada não confere'
        ];
        header('Location: login.php');
        exit();
    }
} else {
    $_SESSION['error'] = [
        'icon' => 'error',
        'title' => 'Erro',
        'text' => 'Usuário não cadastrado'
    ];
    header('Location: login.php');
    exit();
}
