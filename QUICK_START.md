### 2. **Abrir Terminal en tu Proyecto**
```bash
# Navegar al directorio del proyecto
cd c:\xampp\htdocs\loginjwt
```

### 3. **Iniciar el Servidor HTTPS con JWT**
```bash
# Opción A: Usar cmd (recomendado para Windows)
cmd /c "npm start"

# Opción B: Si PowerShell funciona
npm start

# Opción C: Para desarrollo con auto-reload
npm run dev
```

##  URLs Disponibles

Una vez que todo esté ejecutándose:

- ** Login JWT**: https://localhost:3443/index.php
- ** Panel JWT (Nuevo)**: https://localhost:3443/vista/menu_jwt.php  
- ** Panel Clásico**: https://localhost:3443/vista/menu.php
- ** Redirección HTTP**: http://localhost:3000 (redirige a HTTPS)
