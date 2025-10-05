@echo off
title LoginJWT - Servidor HTTPS con JWT
echo.
echo ===============================================
echo    LoginJWT - Servidor HTTPS con JWT
echo ===============================================
echo.
echo Verificando requisitos...

:: Verificar si estamos en el directorio correcto
if not exist "package.json" (
    echo ERROR: No se encontro package.json
    echo Asegurate de ejecutar este archivo desde c:\xampp\htdocs\loginJWT
    pause
    exit /b 1
)

echo ✅ Directorio correcto encontrado
echo.

:: Verificar Node.js
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js no esta instalado
    echo Por favor instala Node.js desde https://nodejs.org
    pause
    exit /b 1
)

echo ✅ Node.js detectado
echo.

:: Verificar si XAMPP está corriendo (puerto 80)
echo Verificando XAMPP...
netstat -an | find ":80 " | find "LISTENING" >nul
if %errorlevel% neq 0 (
    echo ⚠️  XAMPP Apache no parece estar ejecutandose
    echo.
    echo Por favor:
    echo 1. Abre XAMPP Control Panel
    echo 2. Inicia Apache
    echo 3. Inicia MySQL
    echo 4. Presiona cualquier tecla para continuar
    pause
) else (
    echo ✅ XAMPP Apache ejecutandose
)

echo.
echo Iniciando servidor HTTPS con JWT...
echo.
echo 🌐 URLs disponibles una vez iniciado:
echo    • Login: https://localhost:3443/index.php
echo    • Panel JWT: https://localhost:3443/vista/menu_jwt.php
echo    • Panel Clasico: https://localhost:3443/vista/menu.php
echo.
echo 🛑 Para detener: Presiona Ctrl+C
echo.

:: Iniciar servidor
npm start

echo.
echo Servidor detenido.
pause