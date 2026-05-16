# Bodega FET — Sistema de Gestión de Inventario
Sistema web de gestión de inventario desarrollado para la Fundación Escuela Tecnológica de Neiva (FET). Permite controlar entradas y salidas de productos, gestionar proveedores, usuarios y devoluciones desde una interfaz limpia y responsiva.


# Funcionalidades

Autenticación con sesiones PHP, recuperación de contraseña por pregunta de seguridad y registro de usuarios.
Dashboard con estadísticas en tiempo real: total de productos, valor del inventario, movimientos del día y alertas de stock.
Productos: CRUD completo con código, categoría, proveedor, stock mínimo, precio y unidad de medida.
Movimientos: registro de entradas y salidas con validación de stock disponible e historial de los últimos 50 registros.
Devoluciones: flujo de aprobación en dos pasos (registro → aprobación admin), con actualización automática de stock.
Proveedores: gestión de contactos y datos comerciales.
Usuarios: administración de cuentas con roles admin y bodeguero (solo accesible para administradores).
Alertas de stock para productos agotados o por debajo del mínimo definido.


# Tecnologías
CapaTecnologíaBackendPHP 8+ (procedural con MySQLi)Base de datosMySQL / MariaDBFrontendHTML5, CSS3 (variables CSS, Flexbox, Grid)FuentesGoogle Fonts — Montserrat + InterServidorApache / Nginx con soporte PHP
No requiere frameworks externos ni dependencias de Composer.

# Instalación
Requisitos previos

PHP 8.0 o superior
MySQL 5.7+ / MariaDB 10.3+
Servidor web (Apache con mod_rewrite o Nginx)

Pasos

Clonar el repositorio en el directorio raíz del servidor:

bash   git clone https://github.com/tu-usuario/inventario-fet.git /var/www/html/Inventario_FET

Crear la base de datos importando el esquema:

bash   mysql -u root -p < database.sql

Configurar la conexión en conexion.php:

php   $servidor = "127.0.0.1";
   $usuario  = "tu_usuario_mysql";
   $password = "tu_contraseña";
   $bd       = "inventario_fet";

Acceder en el navegador:

   http://localhost/Inventario_FET/

# Credenciales por defecto

⚠️ Cambia la contraseña inmediatamente después del primer acceso.

CampoValorCorreoadmin@fet.edu.coContraseñapasswordRolAdministrador
Para generar un hash seguro para una nueva contraseña:
bashphp -r "echo password_hash('TuContraseña', PASSWORD_DEFAULT);"
Luego actualiza el campo password del usuario admin directamente en la base de datos.

# Estructura del proyecto
Inventario_FET/
├── conexion.php        # Configuración de BD y funciones de sesión/rol
├── index.php           # Login
├── registro.php        # Registro de nuevos usuarios
├── recuperar.php       # Recuperación de contraseña por pregunta de seguridad
├── logout.php          # Cierre de sesión
├── header.php          # Sidebar, topbar y estilos globales
├── footer.php          # Cierre del layout HTML
├── dashboard.php       # Página principal con estadísticas
├── productos.php       # CRUD de productos
├── movimientos.php     # Registro de entradas y salidas
├── devoluciones.php    # Gestión de devoluciones
├── proveedores.php     # CRUD de proveedores
├── usuarios.php        # Gestión de usuarios (solo admin)
└── database.sql        # Esquema y datos iniciales de la BD

# Roles de usuario
RolPermisosadminAcceso completo: productos, movimientos, devoluciones, proveedores, usuarios, aprobación de devolucionesbodegueroProductos, movimientos, devoluciones (registro, no aprobación) y proveedores

# Consideraciones de seguridad

Las contraseñas se almacenan con password_hash() (bcrypt).
Las consultas usan sentencias preparadas (mysqli_prepare) para prevenir inyección SQL.
Las salidas HTML se escapan con htmlspecialchars().
El acceso a secciones restringidas valida rol y sesión activa en cada petición.
Para producción, se recomienda mover conexion.php fuera del document root o protegerlo con .htaccess.


# Licencia
Proyecto desarrollado con fines educativos para la Fundación Escuela Tecnológica de Neiva. Uso interno institucional.

Sistema de Gestión de Bodega · FET Neiva © 2026
