<?php
/**
 * Clase para manejo de JWT (JSON Web Tokens)
 * Implementación real de autenticación basada en tokens
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth {
    
    // Clave secreta para firmar los tokens (EN PRODUCCIÓN debe estar en variables de entorno)
    private static $secretKey = 'LoginJWT_SecureKey_2024_Change_This_In_Production_Environment_b53866d47c91';
    
    // Algoritmo de encriptación
    private static $algorithm = 'HS256';
    
    // Tiempo de expiración del token (1 hora = 3600 segundos)
    private static $expirationTime = 3600;
    
    // Tiempo de expiración del refresh token (7 días)
    private static $refreshExpirationTime = 604800;
    
    /**
     * Generar un token JWT
     * @param array $userData Datos del usuario para incluir en el token
     * @return array Array con access_token y refresh_token
     */
    public static function generateToken($userData) {
        $issuedAt = time();
        $expiration = $issuedAt + self::$expirationTime;
        $refreshExpiration = $issuedAt + self::$refreshExpirationTime;
        
        // Payload del access token
        $payload = [
            'iss' => 'loginJWT-app',              // Issuer (quien emite el token)
            'aud' => 'loginJWT-users',            // Audience (para quien es el token)
            'iat' => $issuedAt,                   // Issued at (cuando fue emitido)
            'exp' => $expiration,                 // Expiration time
            'nbf' => $issuedAt,                   // Not before (válido desde)
            'sub' => $userData['id'],             // Subject (ID del usuario)
            'user_data' => [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'name' => $userData['name'],
                'apaterno' => $userData['apaterno'] ?? '',
                'amaterno' => $userData['amaterno'] ?? ''
            ],
            'type' => 'access_token'
        ];
        
        // Payload del refresh token
        $refreshPayload = [
            'iss' => 'loginJWT-app',
            'aud' => 'loginJWT-users',
            'iat' => $issuedAt,
            'exp' => $refreshExpiration,
            'sub' => $userData['id'],
            'type' => 'refresh_token'
        ];
        
        try {
            $accessToken = JWT::encode($payload, self::$secretKey, self::$algorithm);
            $refreshToken = JWT::encode($refreshPayload, self::$secretKey, self::$algorithm);
            
            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => self::$expirationTime,
                'issued_at' => $issuedAt,
                'expires_at' => $expiration
            ];
        } catch (Exception $e) {
            throw new Exception('Error al generar token: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar y decodificar un token JWT
     * @param string $token El token JWT a verificar
     * @return object|false Datos decodificados del token o false si es inválido
     */
    public static function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            
            // Verificar que sea un access token
            if (!isset($decoded->type) || $decoded->type !== 'access_token') {
                return false;
            }
            
            // Verificar que no haya expirado
            if ($decoded->exp < time()) {
                return false;
            }
            
            return $decoded;
        } catch (Exception $e) {
            // Token inválido, expirado o malformado
            error_log('JWT Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar refresh token
     * @param string $refreshToken El refresh token a verificar
     * @return object|false Datos decodificados del token o false si es inválido
     */
    public static function verifyRefreshToken($refreshToken) {
        try {
            $decoded = JWT::decode($refreshToken, new Key(self::$secretKey, self::$algorithm));
            
            // Verificar que sea un refresh token
            if (!isset($decoded->type) || $decoded->type !== 'refresh_token') {
                return false;
            }
            
            // Verificar que no haya expirado
            if ($decoded->exp < time()) {
                return false;
            }
            
            return $decoded;
        } catch (Exception $e) {
            error_log('JWT Refresh Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extraer token del header Authorization
     * @return string|false El token o false si no se encuentra
     */
    public static function getTokenFromHeader() {
        $headers = getallheaders();
        
        // Buscar el header Authorization
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        } else {
            return false;
        }
        
        // Verificar formato Bearer
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return false;
    }
    
    /**
     * Middleware para verificar autenticación JWT
     * @return object Datos del usuario autenticado
     */
    public static function requireAuth() {
        $token = self::getTokenFromHeader();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'error' => 'unauthorized',
                'message' => 'Token de acceso requerido',
                'code' => 'MISSING_TOKEN'
            ]);
            exit;
        }
        
        $decoded = self::verifyToken($token);
        
        if (!$decoded) {
            http_response_code(401);
            echo json_encode([
                'error' => 'unauthorized', 
                'message' => 'Token inválido o expirado',
                'code' => 'INVALID_TOKEN'
            ]);
            exit;
        }
        
        return $decoded->user_data;
    }
    
    /**
     * Renovar access token usando refresh token
     * @param string $refreshToken
     * @return array|false Nuevo token o false si el refresh token es inválido
     */
    public static function refreshAccessToken($refreshToken) {
        $decoded = self::verifyRefreshToken($refreshToken);
        
        if (!$decoded) {
            return false;
        }
        
        // Buscar datos actuales del usuario en la base de datos
        try {
            require_once __DIR__ . '/conexion.php';
            $pdo = Database::connect();
            $stmt = $pdo->prepare('SELECT id, correo, nombre, apaterno, amaterno FROM usuarios WHERE id = :id AND activo = 1');
            $stmt->execute([':id' => $decoded->sub]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Generar nuevo token
            return self::generateToken([
                'id' => $user['id'],
                'email' => $user['correo'],
                'name' => $user['nombre'],
                'apaterno' => $user['apaterno'],
                'amaterno' => $user['amaterno']
            ]);
            
        } catch (Exception $e) {
            error_log('Refresh token error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener información del usuario desde el token
     * @return array|false Datos del usuario o false si no está autenticado
     */
    public static function getCurrentUser() {
        $token = self::getTokenFromHeader();
        
        if (!$token) {
            return false;
        }
        
        $decoded = self::verifyToken($token);
        
        if (!$decoded) {
            return false;
        }
        
        return (array) $decoded->user_data;
    }
    
    /**
     * Verificar si el usuario actual tiene un rol específico
     * @param array $requiredRoles Array de roles requeridos
     * @return bool
     */
    public static function hasRole($requiredRoles) {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        // Aquí puedes implementar lógica de roles si tu base de datos los maneja
        // Por ahora, todos los usuarios autenticados tienen acceso básico
        return true;
    }
}
?>