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
    // Query for all users' records
    $sql = "SELECT e.*, u.name as user_name 
            FROM entries e 
            JOIN users u ON e.id_user = u.id 
            ORDER BY e.entry DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Keep existing query for regular users
    $sql = "SELECT * FROM entries WHERE id_user = :id_user ORDER BY entry DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_user', $user->id);
}

$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_OBJ);

// Modify the grouping logic to include user names for admin
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
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function registerTime() {
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
</body>

</html>
