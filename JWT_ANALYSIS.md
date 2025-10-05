# ANÁLISIS JWT - Estado Actual del Sistema

## Arquitectura Implementada

### Stack Tecnológico
- **Frontend**: JavaScript vanilla con JWT Manager
- **Backend**: PHP 8.1+ con Firebase JWT library
- **Servidor**: Node.js con Express y HTTPS
- **Base de datos**: MySQL con PDO
- **Seguridad**: SSL/TLS, CORS, Rate Limiting

## Base de Datos

### Tabla usuarios
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    correo VARCHAR(100) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apaterno VARCHAR(50) NOT NULL,
    amaterno VARCHAR(50) NOT NULL,
    fecha_registro DATETIME NOT NULL,
    activo TINYINT(1) DEFAULT 1
);
```

## Configuración

### Variables Principales
- **JWT Secret Key**: `LoginJWT_SecureKey_2024_Change_This_In_Production_Environment_b53866d47c91`
- **Access Token Expiration**: 3600 segundos (1 hora)
- **Refresh Token Expiration**: 604800 segundos (7 días)
- **Database**: `loginjwt` en MySQL localhost

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

### Acceso
- URL: https://localhost:3443
