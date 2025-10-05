<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/modelo/JWTAuth.php';

try {
    // Obtener el token del header Authorization
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : null;
    
    if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        throw new Exception('Token de autorización no encontrado');
    }
    
    $token = $matches[1];
    $userData = JWTAuth::verifyToken($token);
    
    if ($userData) {
        echo json_encode([
            'success' => true,
            'message' => '✅ JWT válido y funcionando correctamente',
            'user_data' => $userData,
            'timestamp' => date('Y-m-d H:i:s'),
            'token_info' => [
                'user_id' => $userData->sub,
                'email' => $userData->user_data->email,
                'issued_at' => date('Y-m-d H:i:s', $userData->iat),
                'expires_at' => date('Y-m-d H:i:s', $userData->exp)
            ]
        ]);
    } else {
        throw new Exception('Token inválido');
    }
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => '❌ JWT inválido o expirado',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>