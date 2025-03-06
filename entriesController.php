<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

include_once __DIR__ . '/conexao.php';

$conn = conexao();

$schedules = [
    'Rosa' => [
        'default' => ['start' => '06:00', 'end' => '10:00', 'hours' => 4],
    ],
    'Moraes' => [
        'weekday' => ['start' => '14:00', 'end' => '22:00', 'hours' => 8],
        'saturday' => ['start' => '08:00', 'end' => '12:00', 'hours' => 4],
    ],
    'Liss' => [
        'monday-thursday' => ['start' => '08:00', 'end' => '18:00', 'hours' => 10],
        'friday' => ['start' => '08:00', 'end' => '17:00', 'hours' => 9],
    ]
];

function getExpectedHours($userName, $date) {
    global $schedules;
    
    $dayOfWeek = date('N', strtotime($date)); // 1 (Monday) to 7 (Sunday)
    
    switch($userName) {
        case 'Rosa':
            return $schedules['Rosa']['default']['hours'] * 60;
        
        case 'Moraes':
            if ($dayOfWeek == 6) { // Saturday
                return $schedules['Moraes']['saturday']['hours'] * 60;
            }
            return $dayOfWeek < 6 ? $schedules['Moraes']['weekday']['hours'] * 60 : 0;
            
        case 'Liss':
            if ($dayOfWeek == 5) { // Friday
                return $schedules['Liss']['friday']['hours'] * 60;
            }
            return ($dayOfWeek >= 1 && $dayOfWeek <= 4) ? $schedules['Liss']['monday-thursday']['hours'] * 60 : 0;
            
        default:
            return 480; // Default 8 hours for other employees
    }
}

if ($user->isAdmin) {
    $sql = "SELECT e.*, u.name as user_name 
            FROM entries e 
            JOIN users u ON e.id_user = u.id 
            ORDER BY e.entry DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM entries WHERE id_user = :id_user ORDER BY entry DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user', $user->id);
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_OBJ);

$groupedRecords = [];
foreach ($records as $record) {
    $date = date('Y-m-d', strtotime($record->entry));
    $userName = $user->isAdmin ? $record->user_name : $user->name;
    $time = date('H:i:s', strtotime($record->entry));

    if (!isset($groupedRecords[$date][$userName])) {
        $groupedRecords[$date][$userName] = ['entrada' => null, 'saida' => null, 'times' => []];
    }

    $groupedRecords[$date][$userName]['times'][] = $time;
}

// Process the collected times
foreach ($groupedRecords as $date => $users) {
    foreach ($users as $userName => $data) {
        sort($data['times']); // Sort times chronologically
        $groupedRecords[$date][$userName]['entrada'] = $data['times'][0];
        $groupedRecords[$date][$userName]['saida'] = $data['times'][1] ?? null;
    }
}




$hoursBalance = [];
$currentMonth = date('Y-m');

$firstRecord = reset($records);
$currentMonth = date('Y-m', strtotime($firstRecord->entry));

foreach ($groupedRecords as $date => $userRecords) {
    // Only process records from current month
    if (substr($date, 0, 7) !== $currentMonth) {
        continue;
    }
    
    foreach ($userRecords as $userName => $times) {
        if (!isset($hoursBalance[$userName])) {
            $hoursBalance[$userName] = 0;
        }

        if ($times['entrada'] && $times['saida']) {
            $entrada = strtotime($date . ' ' . $times['entrada']);
            $saida = strtotime($date . ' ' . $times['saida']);
            $workedMinutes = ($saida - $entrada) / 60;

            $expectedMinutes = getExpectedHours($userName, $date);
            $balance = $workedMinutes - $expectedMinutes;

            $hoursBalance[$userName] += $balance;
        }
    }
}

// After calculating the hours balance
$currentMonth = date('Y-m');

foreach ($hoursBalance as $userName => $balance) {
    $sql = "INSERT INTO hours_balance (user_id, month, balance) 
            SELECT u.id, :month, :balance 
            FROM users u 
            WHERE u.name = :userName
            ON DUPLICATE KEY UPDATE balance = :balance";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':month' => $currentMonth,
        ':balance' => $balance,
        ':userName' => $userName
    ]);
}

// Retrieve only current month balances for display
$sql = "SELECT u.name, hb.balance 
        FROM hours_balance hb 
        JOIN users u ON u.id = hb.user_id 
        WHERE hb.month = :currentMonth";
        
$stmt = $conn->prepare($sql);
$stmt->execute([':currentMonth' => $currentMonth]);
$hoursBalance = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'balance', 'name');
