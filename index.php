<?php
// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
require __DIR__ . '/conexao.php';

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
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1">
    <title>Ponto - SERH</title>
    <meta content="Ponto Serh" name="description">
    <meta content="SERH" name="keywords">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="SERH">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f786d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Favicons -->
    <link href="assets/logo.ico" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg" style="background-color: #0f786d;">
        <div class="container">
            <h5 class="navbar-brand text-white" href="#">Sistema de Ponto - SERH</h5>
            <div class="dropdown">
                <button class="btn btn-outline-white dropdown-toggle text-white" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo htmlspecialchars($user->name); ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="userMenu">
                    <li><a class="dropdown-item" href="alterar_senha.php">Alterar Senha</a></li>
                    <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Registro de Ponto</h5>
                        <?php if (!$user->isAdmin): ?>
                            <button style="background-color: #0f786d" class="btn text-white" onclick="registerTime()">
                                Registrar Ponto
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php foreach ($groupedRecords as $date => $userRecords): ?>
                            <div class="date-group mb-4">
                                <h6 class="border-bottom pb-2 text-muted">
                                    <?php echo date('d/m/Y', strtotime($date)); ?>
                                </h6>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <?php if ($user->isAdmin): ?>
                                                <th>Funcionário</th>
                                            <?php endif; ?>
                                            <th>Entrada</th>
                                            <th>Saída</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userRecords as $userName => $times): ?>
                                            <tr>
                                                <?php if ($user->isAdmin): ?>
                                                    <td><?php echo htmlspecialchars($userName); ?></td>
                                                <?php endif; ?>
                                                <td><?php echo $times['entrada'] ?? '-'; ?></td>
                                                <td><?php echo $times['saida'] ?? '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
            <?php if ($user->isAdmin): ?>
                <?php
                // Add this before the hour balance table
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
                ?>
                <div class="col-md-3">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Funcionário</th>
            <th>Banco de Horas</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($hoursBalance as $userName => $minutes): ?>
            <tr>
                <td><?php echo htmlspecialchars($userName); ?></td>
                <td><?php 
                    $hours = floor(abs($minutes) / 60);
                    $mins = abs($minutes) % 60;
                    echo ($minutes >= 0 ? '+' : '-') . sprintf("%02d:%02d", $hours, $mins);
                ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function registerTime() {
            if (!navigator.onLine) {
                // Store in IndexedDB
                const entry = {
                    timestamp: new Date().toISOString(),
                    pending: true
                };

                // Sync when back online
                window.addEventListener('online', () => {
                    // Send pending entries to server
                    fetch('registrarPonto.php', {
                            method: 'POST',
                            body: JSON.stringify(entry)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove from IndexedDB
                                window.location.reload();
                            }
                        });
                });

                Swal.fire({
                    title: 'Ponto Registrado!',
                    text: 'Será sincronizado quando houver conexão',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Original online behavior
            fetch('registrarPonto.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Ponto Registrado!',
                            text: 'Horário registrado com sucesso',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                });
        }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registered');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <style>
        html,
        body {
            overscroll-behavior-y: none;
            height: 100%;
            width: 100%;
            position: fixed;
            overflow-y: auto;
        }
    </style>

</body>

</html>