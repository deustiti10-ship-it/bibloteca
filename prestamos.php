<?php
require_once 'config.php';
$pdo = getConnection();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear') {
        $fecha = $_POST['fecha_prestamo'] ?? date('Y-m-d');
        $cedula = trim($_POST['cedula_usuario'] ?? '');
        $libros = $_POST['libros'] ?? [];

        if ($cedula && !empty($libros)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO prestamos (fecha_prestamo, cedula_usuario) VALUES (?, ?)");
                $stmt->execute([$fecha, $cedula]);
                $idPrestamo = $pdo->lastInsertId();

                $stmtLibro = $pdo->prepare("INSERT INTO prestamo_libros (id_prestamo, isbn) VALUES (?, ?)");
                foreach ($libros as $isbn) {
                    $stmtLibro->execute([$idPrestamo, $isbn]);
                }

                $pdo->commit();
                setMessage('success', 'Préstamo registrado correctamente.');
            } catch (PDOException $e) {
                $pdo->rollBack();
                setMessage('error', 'Error al crear préstamo: ' . $e->getMessage());
            }
        } else {
            setMessage('error', 'Debe seleccionar un usuario y al menos un libro.');
        }
    } elseif ($action === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM prestamos WHERE id = ?");
            $stmt->execute([$id]);
            setMessage('success', 'Préstamo eliminado correctamente.');
        } catch (PDOException $e) {
            setMessage('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    header('Location: prestamos.php');
    exit;
}

// Obtener datos para formularios
$usuarios = $pdo->query("SELECT cedula, nombre FROM usuarios ORDER BY nombre")->fetchAll();
$libros = $pdo->query("SELECT isbn, titulo, autor FROM libros ORDER BY titulo")->fetchAll();

// Obtener préstamos con detalles
$sql = "SELECT p.id, p.fecha_prestamo, p.cedula_usuario, u.nombre AS nombre_usuario,
               GROUP_CONCAT(CONCAT(l.titulo, ' (', l.isbn, ')') SEPARATOR ' | ') AS libros
        FROM prestamos p
        JOIN usuarios u ON p.cedula_usuario = u.cedula
        LEFT JOIN prestamo_libros pl ON p.id = pl.id_prestamo
        LEFT JOIN libros l ON pl.isbn = l.isbn
        GROUP BY p.id
        ORDER BY p.fecha_prestamo DESC, p.id DESC";
$prestamos = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos - Biblioteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="usuarios.php">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="libros.php">Libros</a></li>
                    <li class="nav-item"><a class="nav-link active" href="prestamos.php">Préstamos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4"><i class="bi bi-journal-check"></i> Gestión de Préstamos</h1>
        <?php showMessage(); ?>

        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-warning">
                        Nuevo Préstamo
                    </div>
                    <div class="card-body">
                        <?php if (empty($usuarios) || empty($libros)): ?>
                            <div class="alert alert-warning">
                                Debe registrar al menos un <a href="usuarios.php">usuario</a> y un <a href="libros.php">libro</a> antes de crear préstamos.
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="crear">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de préstamo *</label>
                                    <input type="date" name="fecha_prestamo" class="form-control" required
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuario *</label>
                                    <select name="cedula_usuario" class="form-select" required>
                                        <option value="">Seleccione un usuario</option>
                                        <?php foreach ($usuarios as $u): ?>
                                            <option value="<?= htmlspecialchars($u['cedula']) ?>">
                                                <?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['cedula']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Libros * (seleccione uno o varios)</label>
                                    <select name="libros[]" class="form-select" multiple required size="6">
                                        <?php foreach ($libros as $l): ?>
                                            <option value="<?= htmlspecialchars($l['isbn']) ?>">
                                                <?= htmlspecialchars($l['titulo']) ?> - <?= htmlspecialchars($l['autor']) ?> (<?= htmlspecialchars($l['isbn']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Mantenga Ctrl (o Cmd) para seleccionar varios.</small>
                                </div>
                                <button type="submit" class="btn btn-warning">Registrar Préstamo</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">Historial de Préstamos (<?= count($prestamos) ?>)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Libros</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($prestamos)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No hay préstamos registrados</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($prestamos as $p): ?>
                                            <tr>
                                                <td><?= $p['id'] ?></td>
                                                <td><?= htmlspecialchars($p['fecha_prestamo']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($p['nombre_usuario']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($p['cedula_usuario']) ?></small>
                                                </td>
                                                <td><small><?= htmlspecialchars($p['libros'] ?? 'Sin libros') ?></small></td>
                                                <td>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este préstamo?');">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
