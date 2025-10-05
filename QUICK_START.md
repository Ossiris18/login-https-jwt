#  Guía Rápida: Cómo Ejecutar tu Página JWT cada vez

##  Pasos Obligatorios (Cada Vez que Quieras Usar la Página)

### 1. **Iniciar XAMPP** 
```
1. Abrir XAMPP Control Panel
2. Hacer clic en "Start" en Apache 
3. Hacer clic en "Start" en MySQL 
4. Verificar que ambos estén en verde (Running)
```

### 2. **Abrir Terminal en tu Proyecto**
```bash
# Navegar al directorio del proyecto
cd c:\xampp\htdocs\loginJWT
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

### 4. **Verificar que Todo Esté Funcionando**
Deberías ver este mensaje:
```
Servidor HTTPS iniciado en: https://localhost:3443
SSL/TLS habilitado
Servidor HTTP iniciado en puerto 3000 (redirige a HTTPS)
Accede a tu aplicación en: https://localhost:3443
```

##  URLs Disponibles

Una vez que todo esté ejecutándose:

- ** Login JWT**: https://localhost:3443/index.php
- ** Panel JWT (Nuevo)**: https://localhost:3443/vista/menu_jwt.php  
- ** Panel Clásico**: https://localhost:3443/vista/menu.php
- ** Redirección HTTP**: http://localhost:3000 (redirige a HTTPS)

## 🛠️ Comandos Útiles

### **Verificar Estado**
```bash
node manager.js status
```

### **Regenerar Certificados SSL** (si hay problemas)
```bash
npm run generate-certs
```

### **Setup Completo** (si algo falla)
```bash
npm run setup
```

### **Detener Servidor**
```bash
Ctrl + C en la terminal donde está corriendo
```

## 🔧 Solución de Problemas Comunes

### **❌ Error: "Puerto 80 ocupado"**
```bash
# Verificar que XAMPP Apache esté corriendo
# Si no está, iniciarlo desde XAMPP Control Panel
```

### **❌ Error: "PowerShell execution policy"**
```bash
# Usar cmd en lugar de PowerShell:
cmd /c "npm start"
```

### **❌ Error: "No se pueden cargar certificados"**
```bash
npm run generate-certs
npm start
```

### **❌ Error: "Puerto 3443 ocupado"**
```bash
# Cambiar puerto en server.js o matar proceso:
netstat -ano | findstr :3443
taskkill /PID [número_pid] /F
```

## 📋 Checklist Rápido

Antes de cada uso, verifica:

- [ ] ✅ XAMPP Apache iniciado
- [ ] ✅ XAMPP MySQL iniciado  
- [ ] ✅ Terminal abierta en `c:\xampp\htdocs\loginJWT`
- [ ] ✅ Ejecutar `cmd /c "npm start"`
- [ ] ✅ Ver mensaje de confirmación
- [ ] ✅ Ir a https://localhost:3443

## 🎯 Proceso Completo (Paso a Paso)

### **Inicio Rápido (30 segundos):**
```bash
1. Abrir XAMPP → Start Apache + MySQL
2. Abrir terminal → cd c:\xampp\htdocs\loginJWT  
3. Ejecutar → cmd /c "npm start"
4. Abrir navegador → https://localhost:3443
```

### **Primera vez del día:**
```bash
1. Verificar estado → node manager.js status
2. Si todo está verde → npm start
3. Si hay problemas → npm run setup
```

## 🔄 Para Desarrollo Continuo

Si vas a estar desarrollando, usa:
```bash
npm run dev
```
Esto reinicia automáticamente el servidor cuando cambies archivos.

## 🛑 Para Detener Todo

1. **Detener servidor HTTPS**: `Ctrl + C` en la terminal
2. **Detener XAMPP**: Hacer clic en "Stop" en Apache y MySQL

---

## 💡 Tip Pro

Crea un archivo batch (.bat) para automatizar el inicio:

```batch
@echo off
echo Iniciando LoginJWT con HTTPS y JWT...
cd /d "c:\xampp\htdocs\loginJWT"
cmd /c "npm start"
pause
```

Guarda esto como `start-loginJWT.bat` en tu escritorio para inicio rápido.

---

**¡Tu página JWT está lista para usar! 🎉**

Recuerda: Solo necesitas hacer estos pasos cada vez que reinicies tu computadora o cierres los servicios.