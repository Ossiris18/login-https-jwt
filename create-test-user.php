<?php
/**
 * Script para crear usuario de prueba
 * Ejecutar una sola vez para probar el sistema
 */

require_once __DIR__ . '/modelo/conexion.php';

try {
    $pdo = Database::connect();
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Error: La tabla 'usuarios' no existe.\n";
        echo "📝 Ejecuta el archivo modelo/usuarios.sql en phpMyAdmin primero.\n";
        exit(1);
    }
    
    // Verificar si ya existe un usuario de prueba
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->execute([':correo' => 'test@ejemplo.mx']);
    
    if ($stmt->rowCount() > 0) {
        echo "✅ El usuario de prueba ya existe.\n";
        echo "📧 Email: test@ejemplo.mx\n";
        echo "🔑 Contraseña: Test$123\n";
        echo "🌐 Accede en: https://localhost:3443\n";
        exit(0);
    }
    
    // Crear usuario de prueba
    $password = 'Test$123'; // Cumple con todas las reglas
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (correo, pass, nombre, apaterno, amaterno, fecha_registro, activo) 
        VALUES (:correo, :pass, :nombre, :apaterno, :amaterno, NOW(), 1)
    ");
    
    $result = $stmt->execute([
        ':correo' => 'test@ejemplo.mx',
        ':pass' => $passwordHash,
        ':nombre' => 'Usuario',
        ':apaterno' => 'De',
        ':amaterno' => 'Prueba'
    ]);
    
    if ($result) {
        echo "✅ ¡Usuario de prueba creado exitosamente!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📧 Email: test@ejemplo.mx\n";
        echo "🔑 Contraseña: Test$123\n";
        echo "🌐 URL: https://localhost:3443\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📋 Instrucciones:\n";
        echo "1. Abre tu navegador en https://localhost:3443\n";
        echo "2. Acepta el certificado SSL de desarrollo\n";
        echo "3. Usa las credenciales mostradas arriba\n";
        echo "4. ¡Disfruta tu sistema JWT!\n";
    } else {
        echo "❌ Error al crear el usuario de prueba.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "💡 Asegúrate de que MySQL esté ejecutándose en XAMPP.\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "💡 Crea la base de datos 'loginjwt' en phpMyAdmin.\n";
    }
}
?>