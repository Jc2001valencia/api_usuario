<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Vendedor.php';

$db = new Database();
$vendedor = new Vendedor($db->conn);

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST' && isset($data['action'])) {
    switch ($data['action']) {
        case 'registrar':
            // Validar que todos los campos requeridos estén presentes
            $requiredFields = ['nombre', 'descripcion', 'telefono', 'id_usu', 'id_tipotienda', 'imagen', 'ubicacion'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                echo json_encode(["error" => "Datos incompletos.", "faltantes" => $missingFields]);
                exit;
            }

            if ($vendedor->registrar(
                $data['nombre'],
                $data['descripcion'],
                $data['telefono'],
                $data['id_usu'],
                $data['id_tipotienda'],
                $data['imagen'],
                $data['ubicacion']
            )) {
                echo json_encode(["message" => "Vendedor registrado correctamente."]);
            } else {
                echo json_encode(["error" => "Error al registrar vendedor."]);
            }
            break;

        default:
            echo json_encode(["error" => "Acción no válida."]);
            break;
    }
} elseif ($method == 'GET') {
    if (isset($_GET['id_vendedor'])) {
        echo json_encode($vendedor->obtenerVendedor($_GET['id_vendedor']));
    } else {
        echo json_encode($vendedor->listarVendedores());
    }
} elseif ($method == 'PUT') {
    // Validar que los campos necesarios estén presentes
    $requiredFields = ['id_vendedor', 'nombre', 'descripcion', 'telefono', 'id_usu', 'id_tipotienda', 'imagen', 'ubicacion'];
    $missingFields = [];

    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        echo json_encode(["error" => "Datos incompletos.", "faltantes" => $missingFields]);
        exit;
    }

    if ($vendedor->actualizarVendedor(
        $data['id_vendedor'],
        $data['nombre'],
        $data['descripcion'],
        $data['telefono'],
        $data['id_usu'],
        $data['id_tipotienda'],
        $data['imagen'],
        $data['ubicacion']
    )) {
        echo json_encode(["message" => "Vendedor actualizado correctamente."]);
    } else {
        echo json_encode(["error" => "Error al actualizar vendedor."]);
    }
} elseif ($method == 'DELETE') {
    if (!isset($data['id_vendedor'])) {
        echo json_encode(["error" => "Falta el ID del vendedor para eliminar."]);
        exit;
    }

    if ($vendedor->eliminarVendedor($data['id_vendedor'])) {
        echo json_encode(["message" => "Vendedor eliminado correctamente."]);
    } else {
        echo json_encode(["error" => "Error al eliminar vendedor."]);
    }
} else {
    echo json_encode(["error" => "Método no permitido."]);
}