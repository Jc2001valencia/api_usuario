<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Usuario.php';

$db = new Database();
$usuario = new Usuario($db->conn);

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$method = $_SERVER['REQUEST_METHOD'];

function generarToken($length = 32) {
    return bin2hex(random_bytes($length));
}

if ($method == 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'registrar':
            $resultado = $usuario->registrar($data['email'], $data['password']);

            echo json_encode($resultado);
        break;

        case 'iniciar_sesion':
            $user = $usuario->iniciarSesion($data['email'], $data['password']);
            if ($user) {
                echo json_encode([
                    "message" => "Inicio de sesión exitoso.",
                    "token" => $user['token']
                ]);
            } else {
                echo json_encode(["error" => "Credenciales incorrectas."]);
            }
            break;

        case 'generar_token_2fa':
            echo json_encode([
                "message" => "Token 2FA generado.",
                "token_2fa" => generarToken(16)
            ]);
            break;

        case 'olvidar_contraseña':
            echo json_encode([
                "message" => "Token de recuperación generado.",
                "token_recuperacion" => generarToken(20)
            ]);
            break;

        default:
            echo json_encode(["error" => "Acción no válida."]);
            break;
    }
} else {
    echo json_encode(["error" => "Método no permitido o datos insuficientes."]);
}
?>