<?php
require_once 'config.php';
$pdo = getConnection();

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'crear') {
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($cedula && $nombre) {
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (cedula, nombre, telefono) VALUES (?, ?, ?)");
                $stmt->execute([$cedula, $nombre, $telefono]);
                setMessage('success', 'Usuario creado correctamente.');
            } catch (PDOException $e) {
                setMessage('error', 'Error al crear usuario: ' . ($e->getCode() == 23000 ? 'La cédula ya existe.' : $e->getMessage()));
            }
        } else {
            setMessage('error', 'Cédula y nombre son obligatorios.');
        }
    } elseif ($action === 'editar') {
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($cedula && $nombre) {
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, telefono = ? WHERE cedula = ?");
                $stmt->execute([$nombre, $telefono, $cedula]);
                setMessage('success', 'Usuario actualizado correctamente.');
            } catch (PDOException $e) {
                setMessage('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'eliminar') {
        $cedula = $_POST['cedula'] ?? '';
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE cedula = ?");
            $stmt->execute([$cedula]);
            setMessage('success', 'Usuario eliminado correctamente.');
        } catch (PDOException $e) {
            setMessage('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }
    header('Location: usuarios.php');
    exit;
}

// Obtener usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY nombre")->fetchAll();

// Para edición
$editUser = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE cedula = ?");
    $stmt->execute([$_GET['editar']]);
    $editUser = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Biblioteca</title>
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
                    <li class="nav-item"><a class="nav-link active" href="usuarios.php">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="libros.php">Libros</a></li>
                    <li class="nav-item"><a class="nav-link" href="prestamos.php">Préstamos</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4"><i class="bi bi-people"></i> Gestión de Usuarios</h1>
        <?php showMessage(); ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <?= $editUser ? 'Editar Usuario' : 'Nuevo Usuario' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $editUser ? 'editar' : 'crear' ?>">
                            <div class="mb-3">
                                <label class="form-label">Cédula *</label>
                                <input type="text" name="cedula" class="form-control" required
                                       value="<?= htmlspecialchars($editUser['cedula'] ?? '') ?>"
                                       <?= $editUser ? 'readonly' : '' ?>>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" class="form-control" required
                                       value="<?= htmlspecialchars($editUser['nombre'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control"
                                       value="<?= htmlspecialchars($editUser['telefono'] ?? '') ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <?= $editUser ? 'Actualizar' : 'Crear' ?>
                            </button>
                            <?php if ($editUser): ?>
                                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Lista de Usuarios (<?= count($usuarios) ?>)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre</th>
                                        <th>Teléfono</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($usuarios)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">No hay usuarios registrados</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($usuarios as $u): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($u['cedula']) ?></td>
                                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                                <td><?= htmlspecialchars($u['telefono'] ?? '-') ?></td>
                                                <td>
                                                    <a href="usuarios.php?editar=<?= urlencode($u['cedula']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                        <input type="hidden" name="action" value="eliminar">
                                                        <input type="hidden" name="cedula" value="<?= htmlspecialchars($u['cedula']) ?>">
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
