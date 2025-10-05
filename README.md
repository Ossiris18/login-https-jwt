## Instalación Rápida

### Puertos
- HTTPS Server: 3443
- HTTP Redirect: 3000
- XAMPP Apache: 80
- MySQL: 3306


### Requisitos
- XAMPP (Apache + MySQL)
- Node.js v16+
- Composer
- Certificados SSL

### Comandos
```bash
composer install
npm install
node server.js
```

### Configuración
```bash
# 1. Instalar dependencias PHP
composer install

# 2. Instalar dependencias Node.js
npm install

# 3. Generar certificados SSL
npm run generate-certs

# 4. Crear base de datos 'loginjwt' en MySQL
# 5. Importar estructura desde modelo/usuarios.sql
```
### Script mysql
```sql
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS loginjwt;
USE loginjwt;

-- Crear la tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apaterno VARCHAR(50) NOT NULL,
    amaterno VARCHAR(50) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

-- Insertar un registro de ejemplo
INSERT INTO usuarios (correo, pass, nombre, apaterno, amaterno, fecha_registro, activo)
VALUES (
    'alfaro@ejemplo.mx',
    '$2y$10$mYddklb45JAQdf6pK5Ovg.rUeKr4Jirn5H9pPPDzjQTLHzLKhxzdu', -- Contraseña encriptada con bcrypt
    'Alfaro',
    'Perez',
    'Lopez',
    '2025-10-04 21:03:52',
    1
);

```

### Iniciar el sistema
```bash
# 1. Iniciar XAMPP (Apache + MySQL)
# 2. Iniciar servidor HTTPS
npm start
```

**🌐 Acceso:** https://localhost:3443 o https://localhost/loginjwt/
