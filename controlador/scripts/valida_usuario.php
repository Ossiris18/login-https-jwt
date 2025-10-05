
<?php
/**
 * Endpoint para registro de usuarios con JWT
 * Actualizado para manejar respuestas JSON y validaciones consistentes
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

require_once __DIR__ . '/../../modelo/conexion.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'method_not_allowed',
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Sanitizar y validar datos
$correo_raw = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$correo = strtolower(filter_var($correo_raw, FILTER_SANITIZE_EMAIL));
$pass = $_POST['pass'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$apaterno = trim($_POST['apaterno'] ?? '');
$amaterno = trim($_POST['amaterno'] ?? '');

// Validaciones básicas
if (empty($correo) || empty($pass) || empty($nombre) || empty($apaterno) || empty($amaterno)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'Todos los campos son obligatorios'
    ]);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'El formato del correo electrónico no es válido'
    ]);
    exit;
}

if (!preg_match('/\.mx$/i', $correo)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'El correo debe terminar en .mx'
    ]);
    exit;
}

// Validación de contraseña (mismas reglas que el login)
if (strlen($pass) > 10) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'La contraseña no puede tener más de 10 caracteres'
    ]);
    exit;
}

if (empty($pass)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'La contraseña no puede estar vacía'
    ]);
    exit;
}

if (!ctype_upper($pass[0])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'La primera letra debe ser mayúscula'
    ]);
    exit;
}

if (strpos($pass, '$') === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'La contraseña debe contener al menos un signo de pesos ($)'
    ]);
    exit;
}

// Verificar que los números estén al final (si existen)
$tieneNumeros = preg_match('/\d/', $pass);
if ($tieneNumeros) {
    $primerNumeroPos = -1;
    for ($i = 0; $i < strlen($pass); $i++) {
        if (is_numeric($pass[$i])) {
            $primerNumeroPos = $i;
            break;
        }
    }
    
    for ($i = $primerNumeroPos; $i < strlen($pass); $i++) {
        if (!is_numeric($pass[$i])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'validation_error',
                'message' => 'Los números deben estar todos al final de la contraseña'
            ]);
            exit;
        }
    }
}

if (!preg_match('/^[A-Za-z\$0-9]+$/', $pass)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'validation_error',
        'message' => 'Solo se permiten letras, signo de pesos ($) y números'
    ]);
    exit;
}

try {
    // Conectar a la base de datos
    $pdo = Database::connect();

    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->execute([':correo' => $correo]);
    
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'user_exists',
            'message' => 'El correo electrónico ya está registrado'
        ]);
        exit;
    }

    // Encriptar contraseña
    $password_hash = password_hash($pass, PASSWORD_DEFAULT);

    // Insertar usuario
    $sql = "INSERT INTO usuarios (correo, pass, nombre, apaterno, amaterno, fecha_registro, activo) 
            VALUES (:correo, :pass, :nombre, :apaterno, :amaterno, NOW(), 1)";

    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        ':correo' => $correo,
        ':pass' => $password_hash,
        ':nombre' => $nombre,
        ':apaterno' => $apaterno,
        ':amaterno' => $amaterno
    ]);

    if ($resultado) {
        $userId = $pdo->lastInsertId();
        
        error_log("Usuario registrado exitosamente: {$correo} (ID: {$userId})");
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user_id' => $userId,
                'email' => $correo,
                'name' => $nombre
            ]
        ]);
    } else {
        throw new Exception('Error al insertar usuario en la base de datos');
    }

} catch (Exception $e) {
    error_log('Error en registro: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'server_error',
        'message' => 'Error interno del servidor'
    ]);
}
?>