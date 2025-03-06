<?php
// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

require __DIR__ . '/entriesController.php';
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
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Registro de Ponto</h5>
                        <?php if (!$user->isAdmin): ?>
                            <button style="background-color: #0f786d" class="btn text-white" onclick="registerTime()">
                                Registrar Ponto
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div id="pagination-container">
                            <?php
                            $daysPerPage = 6;
                            $allDates = array_keys($groupedRecords);
                            $totalPages = ceil(count($allDates) / $daysPerPage);
                            $currentPage = 1;

                            for ($page = 1; $page <= $totalPages; $page++):
                                $startIndex = ($page - 1) * $daysPerPage;
                                $pageDates = array_slice($allDates, $startIndex, $daysPerPage);
                            ?>
                                <div class="page" data-page="<?php echo $page; ?>" <?php echo $page !== 1 ? 'style="display:none"' : ''; ?>>
                                    <?php foreach ($pageDates as $date): ?>
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
                                                    <?php foreach ($groupedRecords[$date] as $userName => $times): ?>
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
                            <?php endfor; ?>
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item"><a class="page-link" href="#" id="prev" style="color:#0f786d; font-weight: 500;">Anterior</a></li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item"><a class="page-link" href="#" data-page="<?php echo $i; ?>" style="color:#0f786d; font-weight: 500;"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <li class="page-item"><a class="page-link" href="#" id="next" style="color:#0f786d; font-weight: 500;">Próximo</a></li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
            <?php if ($user->isAdmin): ?>
                <div class="col-md-3">
                    <div class="card p-2">
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
                                            echo ($minutes >= 0 ? '+' : '-') . sprintf("%02dh:%02dm", $hours, $mins);
                                            ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let currentPage = 1;
            const totalPages = <?php echo $totalPages; ?>;

            function showPage(pageNum) {
                $('.page').hide();
                $(`[data-page="${pageNum}"]`).show();
                currentPage = pageNum;
                updatePaginationState();
            }

            function updatePaginationState() {
                $('#prev').toggleClass('disabled', currentPage === 1);
                $('#next').toggleClass('disabled', currentPage === totalPages);
                $('.page-link[data-page]').parent().removeClass('active');
                $(`.page-link[data-page="${currentPage}"]`).parent().addClass('active');
            }

            $('.page-link[data-page]').click(function(e) {
                e.preventDefault();
                showPage(parseInt($(this).data('page')));
            });

            $('#prev').click(function(e) {
                e.preventDefault();
                if (currentPage > 1) showPage(currentPage - 1);
            });

            $('#next').click(function(e) {
                e.preventDefault();
                if (currentPage < totalPages) showPage(currentPage + 1);
            });

            updatePaginationState();
        });
    </script>
</body>

</html>