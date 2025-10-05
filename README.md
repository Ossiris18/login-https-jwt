## Instalación Rápida

### Requisitos
- **XAMPP** (PHP 8.1+, MySQL)
- **Node.js** (v16+)
- **Composer**

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

### Iniciar el sistema
```bash
# 1. Iniciar XAMPP (Apache + MySQL)
# 2. Iniciar servidor HTTPS
npm start
```

**🌐 Acceso:** https://localhost:3443
