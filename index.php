<?php
require_once 'config.php';
$pdo = getConnection();

// Contadores rápidos
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalLibros = $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
$totalPrestamos = $pdo->query("SELECT COUNT(*) FROM prestamos")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca - Sistema de Préstamos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-stat { transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-5px); }
        .navbar-brand { font-weight: 700; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-book"></i> Biblioteca</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="usuarios.php">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="libros.php">Libros</a></li>
                    <li class="nav-item"><a class="nav-link" href="prestamos.php">Préstamos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Sistema de Gestión de Préstamos de Biblioteca</h1>
        <?php showMessage(); ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card card-stat text-white bg-success h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people display-4"></i>
                        <h2 class="card-title mt-2"><?= $totalUsuarios ?></h2>
                        <p class="card-text">Usuarios registrados</p>
                        <a href="usuarios.php" class="btn btn-light btn-sm">Gestionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat text-white bg-info h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-book display-4"></i>
                        <h2 class="card-title mt-2"><?= $totalLibros ?></h2>
                        <p class="card-text">Libros en catálogo</p>
                        <a href="libros.php" class="btn btn-light btn-sm">Gestionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat text-white bg-warning h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-journal-check display-4"></i>
                        <h2 class="card-title mt-2"><?= $totalPrestamos ?></h2>
                        <p class="card-text">Préstamos realizados</p>
                        <a href="prestamos.php" class="btn btn-light btn-sm">Gestionar</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del sistema</h5>
            </div>
            <div class="card-body">
                <p>Este sistema permite gestionar:</p>
                <ul>
                    <li><strong>Usuarios:</strong> Cédula, nombre y teléfono.</li>
                    <li><strong>Libros:</strong> Título, autor y código ISBN.</li>
                    <li><strong>Préstamos:</strong> Fecha de préstamo, usuario y uno o varios libros.</li>
                </ul>
                <p class="mb-0 text-muted">Las tablas de la base de datos se crean automáticamente al acceder por primera vez.</p>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <small>Sistema de Biblioteca &copy; <?= date('Y') ?> | PHP + MySQL</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
