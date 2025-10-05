# 🔧 Solución: "No puedo abrir la página en mi navegador"

## 🚨 Problemas Comunes y Soluciones

### **1. Certificado SSL Auto-firmado (Más Común)**

**Síntoma**: El navegador muestra "Su conexión no es privada" o "Conexión no segura"

**Solución**:
1. **Chrome/Edge**:
   - Haz clic en "Avanzado"
   - Haz clic en "Continuar a localhost (no seguro)"
   
2. **Firefox**:
   - Haz clic en "Avanzado"
   - Haz clic en "Aceptar el riesgo y continuar"

3. **Safari**:
   - Haz clic en "Mostrar detalles"
   - Haz clic en "Visitar este sitio web"

### **2. Servidor No Iniciado Correctamente**

**Verificar**:
```bash
# 1. Verificar que XAMPP esté ejecutándose
# Abrir XAMPP Control Panel → Apache y MySQL deben estar en verde

# 2. Verificar estado del servidor
cd c:\xampp\htdocs\loginJWT
node manager.js status
```

**Reiniciar Servidor**:
```bash
# Detener cualquier proceso anterior
taskkill /f /im node.exe

# Iniciar servidor
cd c:\xampp\htdocs\loginJWT
node server.js
```

### **3. Puertos Bloqueados/Ocupados**

**Verificar puertos**:
```bash
netstat -an | findstr :3443
netstat -an | findstr :3000
```

**Si están ocupados**:
```bash
# Encontrar proceso usando el puerto
netstat -ano | findstr :3443

# Matar proceso (reemplaza PID con el número encontrado)
taskkill /PID [número] /F
```

### **4. Firewall de Windows**

**Solución**:
1. Abrir "Configuración de Windows"
2. Ir a "Actualización y seguridad" → "Seguridad de Windows"
3. Ir a "Firewall y protección de red"
4. Hacer clic en "Permitir una aplicación a través del firewall"
5. Buscar "Node.js" y asegurarse de que esté permitido

### **5. Antivirus Bloqueando**

**Solución**:
- Temporalmente deshabilitar antivirus
- Agregar excepción para `c:\xampp\htdocs\loginJWT`
- Agregar excepción para Node.js

## 🔍 Diagnóstico Paso a Paso

### **Paso 1: Verificar Servicios Básicos**
```bash
# Verificar XAMPP
http://localhost/dashboard/

# Debe mostrar el panel de XAMPP
```

### **Paso 2: Verificar Servidor Node.js**
```bash
# En terminal:
cd c:\xampp\htdocs\loginJWT
node server.js

# Debe mostrar:
🔒 Servidor HTTPS iniciado en: https://localhost:3443
```

### **Paso 3: Probar URLs Alternativas**

1. **HTTP (redirige a HTTPS)**:
   ```
   http://localhost:3000
   ```

2. **IP directa**:
   ```
   https://127.0.0.1:3443
   ```

3. **Sin JWT (página original)**:
   ```
   https://localhost:3443/vista/menu.php
   ```

### **Paso 4: Probar en Modo Incógnito**
- Chrome: Ctrl + Shift + N
- Firefox: Ctrl + Shift + P
- Edge: Ctrl + Shift + N

## 🛠️ Soluciones Avanzadas

### **Regenerar Certificados SSL**
```bash
cd c:\xampp\htdocs\loginJWT
rmdir /s /q ssl
npm run generate-certs
node server.js
```

### **Cambiar Puerto (si 3443 está ocupado)**
```javascript
// En server.js, cambiar:
const HTTPS_PORT = 3443; // por ejemplo, a 8443
```

### **Usar HTTP Solo (para pruebas)**
```bash
# Crear servidor HTTP simple
node -e "
const express = require('express');
const app = express();
app.use(express.static('.'));
app.listen(8080, () => console.log('HTTP en http://localhost:8080'));
"
```

## 🎯 Prueba Rápida: URLs a Probar

Prueba estas URLs **en orden** hasta que una funcione:

1. **http://localhost:3000** (HTTP, redirige a HTTPS)
2. **https://localhost:3443** (HTTPS principal)
3. **https://127.0.0.1:3443** (IP en lugar de localhost)
4. **http://localhost/loginJWT/** (Directo a XAMPP, sin Node.js)

## 🔄 Proceso de Diagnóstico Completo

### **Comando de Diagnóstico Automático**
```bash
cd c:\xampp\htdocs\loginJWT
echo "=== DIAGNÓSTICO LOGINJWT ==="
echo "1. Verificando Node.js..."
node --version
echo "2. Verificando puertos..."
netstat -an | findstr ":80\|:3000\|:3443"
echo "3. Verificando procesos..."
tasklist | findstr "httpd\|mysqld\|node"
echo "4. Verificando certificados..."
dir ssl
echo "=== FIN DIAGNÓSTICO ==="
```

## 💡 Soluciones por Navegador

### **Chrome**
```
1. Ir a chrome://flags/#allow-insecure-localhost
2. Habilitar "Allow invalid certificates for resources loaded from localhost"
3. Reiniciar Chrome
```

### **Firefox**
```
1. Ir a about:config
2. Buscar security.mixed_content.block_active_content
3. Cambiar a false
4. Reiniciar Firefox
```

### **Edge**
```
1. Ir a edge://flags/#allow-insecure-localhost
2. Habilitar "Allow invalid certificates for resources loaded from localhost"
3. Reiniciar Edge
```

## 🆘 Si Nada Funciona

### **Solución de Emergencia: Modo HTTP Puro**
```bash
# 1. Detener servidor Node.js
Ctrl + C

# 2. Acceder directamente via XAMPP
http://localhost/loginJWT/index.php

# 3. Usar versión sin HTTPS
# (funcional pero menos segura)
```

### **Reinstalar Dependencias**
```bash
cd c:\xampp\htdocs\loginJWT
rmdir /s /q node_modules
rmdir /s /q ssl
npm install
npm run generate-certs
npm start
```

---

## ✅ Lista de Verificación Final

- [ ] XAMPP Apache ejecutándose (verde)
- [ ] XAMPP MySQL ejecutándose (verde)
- [ ] Terminal muestra: "🔒 Servidor HTTPS iniciado en: https://localhost:3443"
- [ ] Firewall permite Node.js
- [ ] Antivirus no bloquea la carpeta
- [ ] Navegador permite certificados locales
- [ ] Probado en modo incógnito

**Si sigues estos pasos, tu página debería funcionar correctamente.** 🎉