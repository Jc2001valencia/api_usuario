<?php
require_once __DIR__ . '/../config/db.php';

class Vendedor {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crearVendedor($nombre, $descripcion, $telefono, $id_usu, $id_tipotienda, $imagen, $ubicacion) {
        try {
            $query = "INSERT INTO vendedor (nombre, descripcion, telefono, id_usu, id_tipotienda, imagen, ubicacion) 
                      VALUES (:nombre, :descripcion, :telefono, :id_usu, :id_tipotienda, :imagen, :ubicacion)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindValue(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindValue(":telefono", $telefono, PDO::PARAM_STR);
            $stmt->bindValue(":id_usu", $id_usu, PDO::PARAM_INT);
            $stmt->bindValue(":id_tipotienda", $id_tipotienda, PDO::PARAM_INT);
            $stmt->bindValue(":imagen", $imagen, PDO::PARAM_STR);
            $stmt->bindValue(":ubicacion", $ubicacion, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return ["message" => "Vendedor registrado correctamente."];
            } else {
                return ["error" => "Error al registrar el vendedor."];
            }
        } catch (Exception $e) {
            return ["error" => "Excepción: " . $e->getMessage()];
        }
    }

    public function listarVendedores() {
        try {
            $query = $this->conn->prepare("
                SELECT v.*, u.email FROM vendedor v JOIN usuario u ON v.id_usu = u.id_usuario;u
            ");
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["error" => "Excepción: " . $e->getMessage()];
        }
    }
    

    public function obtenerVendedor($id) {
        try {
            $query = $this->conn->prepare("SELECT * FROM vendedor WHERE id_vendedor = :id");
            $query->bindValue(":id", $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC) ?: ["error" => "Vendedor no encontrado."];
        } catch (Exception $e) {
            return ["error" => "Excepción: " . $e->getMessage()];
        }
    }

    public function actualizarVendedor($id, $nombre, $descripcion, $telefono, $id_usu, $id_tipotienda, $imagen, $ubicacion) {
        try {
            $query = "UPDATE vendedor SET nombre = :nombre, descripcion = :descripcion, telefono = :telefono, 
                      id_usu = :id_usu, id_tipotienda = :id_tipotienda, imagen = :imagen, ubicacion = :ubicacion
                      WHERE id_vendedor = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindValue(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindValue(":telefono", $telefono, PDO::PARAM_STR);
            $stmt->bindValue(":id_usu", $id_usu, PDO::PARAM_INT);
            $stmt->bindValue(":id_tipotienda", $id_tipotienda, PDO::PARAM_INT);
            $stmt->bindValue(":imagen", $imagen, PDO::PARAM_STR);
            $stmt->bindValue(":ubicacion", $ubicacion, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return ["message" => "Vendedor actualizado correctamente."];
            } else {
                return ["error" => "Error al actualizar el vendedor."];
            }
        } catch (Exception $e) {
            return ["error" => "Excepción: " . $e->getMessage()];
        }
    }

    public function eliminarVendedor($id) {
        try {
            $query = "DELETE FROM vendedor WHERE id_vendedor = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ["message" => "Vendedor eliminado correctamente."];
            } else {
                return ["error" => "Error al eliminar el vendedor."];
            }
        } catch (Exception $e) {
            return ["error" => "Excepción: " . $e->getMessage()];
        }
    }
}
?>