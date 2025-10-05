<?php
/**
 * Endpoint para obtener lista de usuarios
 * Requiere autenticación JWT
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

require_once __DIR__ . '/../../modelo/conexion.php';
require_once __DIR__ . '/../../modelo/JWTAuth.php';

try {
    // Verificar autenticación JWT
    $userData = JWTAuth::requireAuth();
    
    // Obtener todos los usuarios
    $pdo = Database::connect();
    $stmt = $pdo->query("
        SELECT id, correo, nombre, apaterno, amaterno, pass, fecha_registro, activo 
        FROM usuarios 
        ORDER BY id DESC
    ");
    $users = $stmt->fetchAll();
    
    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Lista de usuarios obtenida exitosamente',
        'data' => [
            'users' => $users,
            'total' => count($users),
            'requested_by' => $userData->email
        ]
    ]);

} catch (Exception $e) {
    error_log('Error en get_users: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'server_error',
        'message' => 'Error interno del servidor',
        'code' => 'INTERNAL_ERROR'
    ]);
}
?>