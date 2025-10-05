<?php
/**
 * Endpoint protegido para obtener información del usuario actual
 * Requiere token JWT válido en header Authorization
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://localhost:3443');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../modelo/JWTAuth.php';
require_once __DIR__ . '/../../modelo/conexion.php';

try {
    // Verificar autenticación (esto automáticamente maneja errores 401)
    $userToken = JWTAuth::requireAuth();

    // Obtener datos actualizados del usuario desde la base de datos
    $pdo = Database::connect();
    $stmt = $pdo->prepare('SELECT id, correo, nombre, apaterno, amaterno, fecha_registro, activo FROM usuarios WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userToken->id]);
    $userData = $stmt->fetch();

    if (!$userData) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'user_not_found',
            'message' => 'Usuario no encontrado',
            'code' => 'USER_NOT_FOUND'
        ]);
        exit;
    }

    if ((int)$userData['activo'] !== 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'account_disabled',
            'message' => 'Cuenta desactivada',
            'code' => 'ACCOUNT_DISABLED'
        ]);
        exit;
    }

    // Respuesta exitosa con datos del usuario
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Datos del usuario obtenidos exitosamente',
        'data' => [
            'user' => [
                'id' => (int)$userData['id'],
                'email' => $userData['correo'],
                'name' => $userData['nombre'],
                'apaterno' => $userData['apaterno'] ?? '',
                'amaterno' => $userData['amaterno'] ?? '',
                'full_name' => trim($userData['nombre'] . ' ' . $userData['apaterno'] . ' ' . $userData['amaterno']),
                'registered_at' => $userData['fecha_registro'],
                'is_active' => (bool)$userData['activo']
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log('Error en user profile: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'server_error',
        'message' => 'Error interno del servidor',
        'code' => 'INTERNAL_ERROR'
    ]);
}
?>