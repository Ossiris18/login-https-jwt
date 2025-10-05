@echo off
title Diagnostico LoginJWT
echo.
echo ================================================
echo           DIAGNOSTICO LOGINJWT
echo ================================================
echo.

echo 1. Verificando Node.js...
node --version
if %errorlevel% neq 0 (
    echo ❌ Node.js no instalado
    goto :error
)
echo ✅ Node.js OK
echo.

echo 2. Verificando directorio...
if not exist "server.js" (
    echo ❌ No estas en el directorio correcto
    echo Debes ejecutar desde: c:\xampp\htdocs\loginJWT
    goto :error
)
echo ✅ Directorio correcto
echo.

echo 3. Verificando XAMPP...
netstat -an | find ":80 " | find "LISTENING" >nul
if %errorlevel% neq 0 (
    echo ⚠️ XAMPP Apache no detectado en puerto 80
    echo Por favor inicia Apache en XAMPP Control Panel
) else (
    echo ✅ XAMPP Apache ejecutandose
)
echo.

echo 4. Verificando certificados SSL...
if exist "ssl\private-key.pem" (
    echo ✅ Certificados SSL encontrados
) else (
    echo ❌ Certificados SSL no encontrados
    echo Generando certificados...
    npm run generate-certs
)
echo.

echo 5. Verificando puertos disponibles...
netstat -an | find ":3443" >nul
if %errorlevel% equ 0 (
    echo ⚠️ Puerto 3443 ya esta en uso
    echo Matando procesos anteriores...
    for /f "tokens=5" %%a in ('netstat -ano ^| find ":3443"') do taskkill /PID %%a /F >nul 2>&1
)

netstat -an | find ":3000" >nul
if %errorlevel% equ 0 (
    echo ⚠️ Puerto 3000 ya esta en uso
    echo Matando procesos anteriores...
    for /f "tokens=5" %%a in ('netstat -ano ^| find ":3000"') do taskkill /PID %%a /F >nul 2>&1
)
echo ✅ Puertos limpiados
echo.

echo 6. Iniciando servidor...
echo.
echo 🚀 Iniciando LoginJWT Server...
echo.
echo URLs para probar:
echo   • http://localhost:3000 (HTTP - redirige a HTTPS)
echo   • https://localhost:3443 (HTTPS principal)
echo   • https://127.0.0.1:3443 (IP directa)
echo.
echo 💡 Si el navegador muestra "No seguro":
echo   1. Haz clic en "Avanzado"
echo   2. Haz clic en "Continuar a localhost (no seguro)"
echo.
echo 🛑 Para detener: Presiona Ctrl+C
echo.

timeout /t 3 >nul
node server.js
goto :end

:error
echo.
echo ❌ Error detectado. Por favor corrige los problemas arriba.
pause
exit /b 1

:end
echo.
echo Servidor detenido.
pause