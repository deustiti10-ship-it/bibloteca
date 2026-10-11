<?php
require_once 'config.php';
$pdo = getConnection();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear') {
        $isbn = trim($_POST['isbn'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');

        if ($isbn && $titulo && $autor) {
            try {
                $stmt = $pdo->prepare("INSERT INTO libros (isbn, titulo, autor) VALUES (?, ?, ?)");
                $stmt->execute([$isbn, $titulo, $autor]);
                setMessage('success', 'Libro creado correctamente.');
            } catch (PDOException $e) {
                setMessage('error', 'Error al crear libro: ' . ($e->getCode() == 23000 ? 'El ISBN ya existe.' : $e->getMessage()));
            }
        } else {
            setMessage('error', 'Todos los campos son obligatorios.');
        }
    } elseif ($action === 'editar') {
        $isbn = trim($_POST['isbn'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');

        if ($isbn && $titulo && $autor) {
            try {
                $stmt = $pdo->prepare("UPDATE libros SET titulo = ?, autor = ? WHERE isbn = ?");
                $stmt->execute([$titulo, $autor, $isbn]);
                setMessage('success', 'Libro actualizado correctamente.');
            } catch (PDOException $e) {
                setMessage('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'eliminar') {
        $isbn = $_POST['isbn'] ?? '';
        try {
            $stmt = $pdo->prepare("DELETE FROM libros WHERE isbn = ?");
            $stmt->execute([$isbn]);
            setMessage('success', 'Libro eliminado correctamente.');
        } catch (PDOException $e) {
            setMessage('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    header('Location: libros.php');
    exit;
}

// Obtener libros
$libros = $pdo->query("SELECT * FROM libros ORDER BY titulo")->fetchAll();

// Para edición
$editLibro = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM libros WHERE isbn = ?");
    $stmt->execute([$_GET['editar']]);
    $editLibro = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libros - Biblioteca</title>
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
                    <li class="nav-item"><a class="nav-link active" href="libros.php">Libros</a></li>
                    <li class="nav-item"><a class="nav-link" href="prestamos.php">Préstamos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4"><i class="bi bi-book"></i> Gestión de Libros</h1>
        <?php showMessage(); ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <?= $editLibro ? 'Editar Libro' : 'Nuevo Libro' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $editLibro ? 'editar' : 'crear' ?>">
                            <div class="mb-3">
                                <label class="form-label">ISBN *</label>
                                <input type="text" name="isbn" class="form-control" required
                                       value="<?= htmlspecialchars($editLibro['isbn'] ?? '') ?>"
                                       <?= $editLibro ? 'readonly' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Título *</label>
                                <input type="text" name="titulo" class="form-control" required
                                       value="<?= htmlspecialchars($editLibro['titulo'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Autor *</label>
                                <input type="text" name="autor" class="form-control" required
                                       value="<?= htmlspecialchars($editLibro['autor'] ?? '') ?>">
                            </div>
                            <button type="submit" class="btn btn-info text-white">
                                <?= $editLibro ? 'Actualizar' : 'Crear' ?>
                            </button>
                            <?php if ($editLibro): ?>
                                <a href="libros.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Catálogo de Libros (<?= count($libros) ?>)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($libros)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No hay libros registrados</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($libros as $l): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($l['isbn']) ?></td>
                                                <td><?= htmlspecialchars($l['titulo']) ?></td>
                                                <td><?= htmlspecialchars($l['autor']) ?></td>
                                                <td>
                                                    <a href="libros.php?editar=<?= urlencode($l['isbn']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este libro?');">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="isbn" value="<?= htmlspecialchars($l['isbn']) ?>">
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
