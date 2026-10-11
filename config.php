<?php
// config.php - Conexión a la base de datos y creación de tablas

define('DB_HOST', 'mysql-vanne.alwaysdata.net');
define('DB_USER', 'vanne');
define('DB_PASS', 'clase1234');
define('DB_NAME', 'vanne_bibloteca');

function getConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            // Crear tablas si no existen
            createTables($pdo);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    return $pdo;
}

function createTables($pdo) {
    // Tabla usuarios
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        cedula VARCHAR(20) PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        telefono VARCHAR(20)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabla libros
    $pdo->exec("CREATE TABLE IF NOT EXISTS libros (
        isbn VARCHAR(20) PRIMARY KEY,
        titulo VARCHAR(200) NOT NULL,
        autor VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabla prestamos
    $pdo->exec("CREATE TABLE IF NOT EXISTS prestamos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha_prestamo DATE NOT NULL,
        cedula_usuario VARCHAR(20) NOT NULL,
        FOREIGN KEY (cedula_usuario) REFERENCES usuarios(cedula) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabla prestamo_libros (relación muchos a muchos)
    $pdo->exec("CREATE TABLE IF NOT EXISTS prestamo_libros (
        id_prestamo INT NOT NULL,
        isbn VARCHAR(20) NOT NULL,
        PRIMARY KEY (id_prestamo, isbn),
        FOREIGN KEY (id_prestamo) REFERENCES prestamos(id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (isbn) REFERENCES libros(isbn) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Función auxiliar para mensajes
function setMessage($type, $text) {
    $_SESSION['message'] = ['type' => $type, 'text' => $text];
}

function showMessage() {
    if (isset($_SESSION['message'])) {
        $msg = $_SESSION['message'];
        $alertClass = $msg['type'] === 'success' ? 'alert-success' : 'alert-danger';
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($msg['text']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['message']);
    }
}

session_start();
?>
