<?php
class Database {
    // 1. Credenciales de la Base de Datos
    const HOST = 'localhost';
    const DBNAME = 'loginjwt';
    const PORT = '3306'; //puerto de MySQL
    const USERNAME = 'root';
    const PASSWORD = '';
    
    public static function connect() {
        try {
            $pdo = new PDO(
                "mysql:host=" . self::HOST . ";port=" . self::PORT . ";dbname=" . self::DBNAME . ";charset=utf8",
                self::USERNAME,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            // No imprimir mensajes aquí para no romper respuestas JSON
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Error de conexión: " . $e->getMessage());
        }
    }
}
?>
