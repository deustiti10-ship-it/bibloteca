# Sistema de Gestión de Préstamos de Biblioteca

Aplicación web en **PHP + MySQL** para gestionar préstamos de libros en una biblioteca.

## Características

- Gestión de **Usuarios**: cédula, nombre y teléfono.
- Gestión de **Libros**: título, autor y código ISBN.
- Gestión de **Préstamos**: fecha, usuario y uno o varios libros.
- Las tablas de la base de datos se crean **automáticamente** al acceder por primera vez.
- Interfaz moderna con Bootstrap 5.

## Requisitos

- PHP 7.4 o superior (recomendado 8.x)
- Extensión PDO MySQL habilitada
- Base de datos MySQL / MariaDB

## Credenciales de conexión (ya configuradas)

```
Host:     mysql-vanne.alwaysdata.net
Usuario:  vanne
Clave:    clase1234
Base de datos: vanne_bibloteca
```

## Instalación en AlwaysData (o cualquier hosting PHP)

1. Sube todos los archivos de esta carpeta al directorio público de tu sitio (por ejemplo `www` o la carpeta del sitio).
2. Asegúrate de que el sitio esté configurado como tipo **PHP**.
3. Accede a la URL del sitio. Las tablas se crearán automáticamente.

## Estructura de archivos

```
biblioteca/
├── config.php      # Conexión a BD y creación de tablas
├── index.php       # Panel principal (dashboard)
├── usuarios.php    # CRUD de usuarios
├── libros.php      # CRUD de libros
├── prestamos.php   # Gestión de préstamos
└── README.md       # Este archivo
```

## Tablas creadas

- `usuarios` (cedula PK, nombre, telefono)
- `libros` (isbn PK, titulo, autor)
- `prestamos` (id PK, fecha_prestamo, cedula_usuario FK)
- `prestamo_libros` (id_prestamo + isbn PK, relación muchos a muchos)

## Uso

1. Registra usuarios en **Usuarios**.
2. Registra libros en **Libros**.
3. Crea préstamos seleccionando usuario y uno o más libros.

¡Listo para usar!
