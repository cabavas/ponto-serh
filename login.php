<?php
session_start();
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
    <main id="secaoprincipal">
        <div class="container-fluid" style="background-color: #0f786d">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                            <div class="card bg-success-subtle">
                                <div class="d-flex justify-content-center py-3">
                                    <a href="index.php" class="d-flex align-items-center w-auto">
                                        <img src="assets/logo.avif" class="img-fluid" style="width: 200px;"
                                            alt="">
                                    </a>
                                </div>

                                <div class="card-body">

                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Sistema de ponto - SERH</h5>
                                    </div>

                                    <form class="row g-3" novalidate name="formLogin"
                                        action="loginController.php" method="POST">

                                        <div class="col-12">
                                            <label for="nome" class="form-label">Login</label>
                                            <div class="input-group has-validation">

                                                <input type="text" name="nome" class="form-control" id="nome" required>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="password" class="form-label">Senha</label>
                                            <input type="password" name="password" class="form-control" id="password" required>
                                        </div>

                                        <div class="col">
                                            <div class="col-12">
                                                <button class="btn w-100 mt-3 text-white" style="background-color: #04a9a9;" type="submit">Entrar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

        </div>
    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>


    <?php
    if (isset($_SESSION['error'])) : ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['error']['title']; ?>',
                text: '<?php echo $_SESSION['error']['text']; ?>',
                icon: '<?php echo $_SESSION['error']['icon']; ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                <?php unset($_SESSION['error']); ?>
                window.location.href = 'login.php';
            });
        </script>
    <?php endif; ?>

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