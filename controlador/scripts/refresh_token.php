<?php
/**
 * Endpoint para renovar access token usando refresh token
 */

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

require_once __DIR__ . '/../../modelo/JWTAuth.php';

try {
    // Obtener refresh token del body
    $input = json_decode(file_get_contents('php://input'), true);
    $refreshToken = $input['refresh_token'] ?? '';

    if (empty($refreshToken)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'missing_token',
            'message' => 'Refresh token requerido',
            'code' => 'MISSING_REFRESH_TOKEN'
        ]);
        exit;
    }

    // Intentar renovar el token
    $newTokens = JWTAuth::refreshAccessToken($refreshToken);

    if (!$newTokens) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'invalid_token',
            'message' => 'Refresh token inválido o expirado',
            'code' => 'INVALID_REFRESH_TOKEN'
        ]);
        exit;
    }

    // Respuesta exitosa con nuevos tokens
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Token renovado exitosamente',
        'data' => [
            'tokens' => $newTokens
        ]
    ]);

} catch (Exception $e) {
    error_log('Error en refresh token: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'server_error',
        'message' => 'Error interno del servidor',
        'code' => 'INTERNAL_ERROR'
    ]);
}
?>