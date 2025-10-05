<?php
/**
 * Endpoint AJAX de validación de login con JWT
 * Flujo: index.php (form) -> POST fetch -> valida_login.php -> JWT token -> JS maneja token
 * Este script ahora:
 *  - Valida credenciales del usuario
 *  - Genera token JWT real en lugar de sesiones PHP
 *  - Devuelve access_token y refresh_token
 *  - Implementa seguridad JWT estándar
 */

// Headers de seguridad y CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://localhost:3443');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../modelo/conexion.php';
require_once __DIR__ . '/../../modelo/JWTAuth.php';

// 1. Normalizar y validar entrada
$email_raw = isset($_POST['email']) ? trim($_POST['email']) : '';
$email = strtolower($email_raw);
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';

// 2. Validaciones de entrada
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.mx$/i', $email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'validation_error',
        'message' => 'El correo debe ser válido y terminar en .mx',
        'code' => 'INVALID_EMAIL'
    ]);
    exit;
}

if (!is_string($pass) || strlen($pass) !== 10) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'validation_error',
        'message' => 'La contraseña debe tener exactamente 10 caracteres',
        'code' => 'INVALID_PASSWORD_LENGTH'
    ]);
    exit;
}

try {
    // 3. Verificar credenciales en base de datos
    $pdo = Database::connect();
    $stmt = $pdo->prepare('SELECT id, correo, nombre, apaterno, amaterno, pass, activo FROM usuarios WHERE correo = :correo LIMIT 1');
    $stmt->execute([':correo' => $email]);
    $user = $stmt->fetch();

    // 4. Verificar si el usuario existe y está activo
    if (!$user) {
        // Delay para prevenir ataques de timing
        usleep(500000); // 0.5 segundos
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'error' => 'authentication_failed',
            'message' => 'Credenciales inválidas',
            'code' => 'INVALID_CREDENTIALS'
        ]);
        exit;
    }

    if ((int)$user['activo'] !== 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'error' => 'account_disabled',
            'message' => 'Cuenta desactivada. Contacta al administrador.',
            'code' => 'ACCOUNT_DISABLED'
        ]);
        exit;
    }

    // 5. Verificar contraseña
    if (!password_verify($pass, $user['pass'])) {
        // Delay para prevenir ataques de timing
        usleep(500000); // 0.5 segundos
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'error' => 'authentication_failed',
            'message' => 'Credenciales inválidas',
            'code' => 'INVALID_CREDENTIALS'
        ]);
        exit;
    }

    // 6. Generar tokens JWT
    $userData = [
        'id' => $user['id'],
        'email' => $user['correo'],
        'name' => $user['nombre'],
        'apaterno' => $user['apaterno'] ?? '',
        'amaterno' => $user['amaterno'] ?? ''
    ];

    $tokens = JWTAuth::generateToken($userData);

    // 7. Log del login exitoso (opcional)
    error_log("Login exitoso para usuario: {$user['correo']} (ID: {$user['id']})");

    // 8. Respuesta exitosa con tokens
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Autenticación exitosa',
        'data' => [
            'user' => [
                'id' => $user['id'],
                'email' => $user['correo'],
                'name' => $user['nombre'],
                'full_name' => trim($user['nombre'] . ' ' . $user['apaterno'] . ' ' . $user['amaterno'])
            ],
            'tokens' => $tokens
        ],
        'redirect' => '/loginjwt/vista/menu_jwt.php'
    ]);

} catch (Exception $e) {
    // Log del error
    error_log('Error en login: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'server_error',
        'message' => 'Error interno del servidor',
        'code' => 'INTERNAL_ERROR'
    ]);
}
?>