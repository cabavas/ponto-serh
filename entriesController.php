<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include_once __DIR__ . '/conexao.php';

$conn = conexao();

if ($user->isAdmin) {
    $sql = "SELECT e.*, u.name as user_name 
            FROM entries e 
            JOIN users u ON e.id_user = u.id 
            ORDER BY e.entry ASC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM entries WHERE id_user = :id_user ORDER BY entry ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user', $user->id);
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_OBJ);

$groupedRecords = [];
foreach ($records as $record) {
    $date = date('Y-m-d', strtotime($record->entry));
    $userName = $user->isAdmin ? $record->user_name : $user->name;

    if (!isset($groupedRecords[$date][$userName])) {
        $groupedRecords[$date][$userName] = ['entrada' => null, 'saida' => null];
    }

    if ($groupedRecords[$date][$userName]['entrada'] === null) {
        $groupedRecords[$date][$userName]['entrada'] = date('H:i:s', strtotime($record->entry));
    } else {
        $groupedRecords[$date][$userName]['saida'] = date('H:i:s', strtotime($record->entry));
    }
}

$hoursBalance = [];
foreach ($groupedRecords as $date => $userRecords) {
    foreach ($userRecords as $userName => $times) {
        if (!isset($hoursBalance[$userName])) {
            $hoursBalance[$userName] = 0;
        }

        if ($times['entrada'] && $times['saida']) {
            $entrada = strtotime($date . ' ' . $times['entrada']);
            $saida = strtotime($date . ' ' . $times['saida']);
            $workedMinutes = ($saida - $entrada) / 60;

            // Assuming 8 hours workday (480 minutes)
            $expectedMinutes = 480;
            $balance = $workedMinutes - $expectedMinutes;

            $hoursBalance[$userName] += $balance;
        }
    }
}
