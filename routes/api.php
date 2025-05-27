<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/vendedor.php';

$db        = new Database();
$usuario   = new Usuario($db->conn);
$vendedor  = new Vendedor($db->conn);

header("Content-Type: application/json");

$data   = json_decode(file_get_contents("php://input"), true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'registrar':
            if (isset($data['email'], $data['password'])) {
                $resultado = $usuario->registrar($data['email'], $data['password']); // Guardar la respuesta
        
                echo json_encode($resultado); // Devolver directamente la respuesta con el ID
            } else {
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        case 'login':
            if (isset($data['email'], $data['password'])) {
                $mailService = new MailService();
                $resultado = $usuario->iniciarSesion($data['email'], $data['password'], $mailService);
                echo json_encode($resultado);
            } else {
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        case 'validar_token_2fa':
            if (isset($data['id_usuario'], $data['token_2fa'])) {
                $resultado = $usuario->validarToken2FA($data['id_usuario'], $data['token_2fa']);
                if (isset($resultado['estado']) && $resultado['estado'] === "correcto") {
                    echo json_encode(["message" => "Autenticación 2FA exitosa."]);
                } else {
                    echo json_encode(["error" => "Token inválido."]);
                }
            } else {
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        case 'olvidar_contrasena':
            if (isset($data['email'])) {
                $respuesta = $usuario->olvidarContrasena($data['email']);
                echo json_encode($respuesta);
            } else {
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

        /** CRUD PARA VENDEDORES **/
        case 'crear_vendedor':
            // Leer y decodificar el JSON recibido
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Registrar en el log del servidor para depuración
            error_log("Datos recibidos: " . print_r($data, true));
        
            // Definir los campos requeridos
            $campos_requeridos = ['nombre', 'descripcion', 'telefono', 'id_usu', 'id_tipotienda', 'imagen', 'ubicacion'];
        
            // Identificar qué datos faltan
            $campos_faltantes = [];
            foreach ($campos_requeridos as $campo) {
                if (empty($data[$campo])) {
                    $campos_faltantes[] = $campo;
                }
            }
        
            if (empty($campos_faltantes)) {
                // Intentar crear el vendedor
                $resultado = $vendedor->crearVendedor(
                    $data['nombre'],
                    $data['descripcion'],
                    $data['telefono'],
                    $data['id_usu'],
                    $data['id_tipotienda'],
                    $data['imagen'],
                    $data['ubicacion']
                );
        
                // Enviar respuesta JSON con el resultado
                echo json_encode(["success" => true, "mensaje" => "Vendedor creado exitosamente.", "data" => $resultado]);
            } else {
                // Enviar respuesta con error detallado
                echo json_encode([
                    "error" => "Datos incompletos.",
                    "faltantes" => $campos_faltantes
                ]);
            }
            break;
        
        

        case 'listar_vendedores':
            $vendedores = $vendedor->listarVendedores();
            echo json_encode($vendedores);
            break;

        case 'obtener_vendedor':
            if (isset($data['id_vendedor'])) {
                $resultado = $vendedor->obtenerVendedor($data['id_vendedor']);
                if ($resultado) {
                    echo json_encode($resultado);
                } else {
                    echo json_encode(["error" => "Vendedor no encontrado."]);
                }
            } else {
                echo json_encode(["error" => "Datos incompletos."]);
            }
            break;

            case 'actualizar_vendedor':
                if (isset($data['id_vendedor'], $data['nombre'], $data['descripcion'], $data['id_ubicacion'], $data['telefono'], $data['id_usu'], $data['id_tipotienda'], $data['imagen'])) {
                    if ($vendedor->actualizarVendedor($data['id_vendedor'], $data['nombre'], $data['descripcion'], $data['id_ubicacion'], $data['telefono'], $data['id_usu'], $data['id_tipotienda'], $data['imagen'])) {
                        echo json_encode(["message" => "Vendedor actualizado correctamente."]);
                    } else {
                        echo json_encode(["error" => "Error al actualizar vendedor."]);
                    }
                } else {
                    echo json_encode(["error" => "Datos incompletos."]);
                }
                break;
            
            

                case 'eliminar_vendedor':
                    if (isset($data['id_vendedor'])) {
                        if ($vendedor->eliminarVendedor($data['id_vendedor'])) {
                            echo json_encode(["message" => "Vendedor eliminado correctamente."]);
                        } else {
                            echo json_encode(["error" => "Error al eliminar vendedor."]);
                        }
                    } else {
                        echo json_encode(["error" => "Datos incompletos."]);
                    }
                    break;

        default:
            echo json_encode(["error" => "Acción no válida."]);
            break;
    }
} else {
    echo json_encode(["error" => "Método no permitido."]);
}