<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../services/MailService.php';

class Usuario
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    private function generarToken($length)
    {
        return bin2hex(random_bytes($length / 2)); // Token seguro
    }

    public function registrar($email, $password)
    {
        try {
            if (empty($email) || empty($password)) {
                return ["error" => "Email y contraseña son obligatorios."];
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["error" => "Formato de email inválido."];
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query           = "INSERT INTO Usuario (email, password) VALUES (:email, :password)";
            $stmt            = $this->conn->prepare($query);

            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->bindValue(":password", $hashed_password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();

                if ($user_id > 0) {
                    return [
                        "estado"  => "correcto",

                        "usuario" => [
                            "id_usuario" => $user_id,
                            "email"      => $email,
                        ],
                    ];
                } else {
                    return ["error" => "No se pudo obtener el ID del usuario."];
                }
            } else {
                return ["error" => "Error al registrar usuario."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        }
    }

    public function iniciarSesion($email, $password, $mailService)
    {
        try {
            if (empty($email) || empty($password)) {
                return ["error" => "Email y contraseña son obligatorios."];
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ["error" => "Formato de email inválido."];
            }

            // Buscar usuario en la base de datos, incluyendo el id_rol
            $query = $this->conn->prepare("SELECT id_usuario, email, password, id_rol FROM Usuario WHERE email = :email");
            $query->bindValue(":email", $email);
            $query->execute();
            $usuario = $query->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Generar un nuevo token 2FA
                $token2FA = rand(100000, 999999);

                // Guardar el token en la base de datos
                $updateQuery = $this->conn->prepare("UPDATE Usuario SET token = :token WHERE id_usuario = :id");
                $updateQuery->bindValue(":token", $token2FA);
                $updateQuery->bindValue(":id", $usuario['id_usuario']);
                $updateQuery->execute();

                // Enviar el token por correo
                $asunto = "Código de verificación de The Closet";
                $cuerpo = "
                    <h2>¡Hola!</h2>
                    <p>Gracias por iniciar sesión en <b>The Closet</b>.</p>
                    <p>Tu código de verificación es:</p>
                    <h1 style='color: #2E86C1; text-align: center;'>$token2FA</h1>
                    <p>Por favor, ingresa este código en la aplicación para continuar con tu acceso.</p>
                    <p>Si no solicitaste este código, por favor ignora este mensaje.</p>
                    <br>
                    <p>Saludos,<br><b> The Closet Quilichao</b></p>
                ";

                $mailEnviado = $mailService->enviarCorreo($usuario['email'], $asunto, $cuerpo);

                if (! $mailEnviado) {
                    return ["error" => "Error al enviar el código 2FA al correo."];
                }

                return [
                    "estado"  => "correcto",
                    "msg"     => "Datos correctos. Código 2FA enviado al correo.",
                    "usuario" => [
                        "id_usuario" => $usuario['id_usuario'],
                        "email"      => $usuario['email'],
                        "id_rol"     => $usuario['id_rol'], // Ahora se devuelve el rol del usuario
                    ],
                ];
            } else {
                return ["error" => "Correo o contraseña incorrectos."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        }
    }

    public function validarToken2FA($id_usuario, $token_2fa)
    {
        try {
            if (empty($id_usuario) || empty($token_2fa)) {
                return ["error" => "ID de usuario y token 2FA son obligatorios."];
            }

            // Verificar si el token 2FA es correcto
            $query = $this->conn->prepare("SELECT id_usuario FROM Usuario WHERE id_usuario = :id_usuario AND token = :token");
            $query->bindValue(":id_usuario", $id_usuario);
            $query->bindValue(":token", $token_2fa);
            $query->execute();
            $usuario = $query->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Token válido, permitir acceso y limpiar el token en la base de datos (opcional)
                $updateQuery = $this->conn->prepare("UPDATE Usuario SET token = NULL WHERE id_usuario = :id_usuario");
                $updateQuery->bindValue(":id_usuario", $id_usuario);
                $updateQuery->execute();

                return [
                    "estado"     => "correcto",
                    "msg"        => "Token válido. Acceso concedido.",
                    "id_usuario" => $id_usuario,
                ];
            } else {
                return ["error" => "Token inválido o expirado."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        }
    }

    public function olvidarContrasena($email)
    {
        try {
            if (empty($email)) {
                return ["error" => "El email es obligatorio."];
            }

            // Verificar si el usuario existe
            $query = $this->conn->prepare("SELECT id_usuario FROM Usuario WHERE email = :email");
            $query->bindValue(":email", $email);
            $query->execute();
            $usuario = $query->fetch(PDO::FETCH_ASSOC);

            if (! $usuario) {
                return ["error" => "El correo electrónico no está registrado."];
            }

            // Generar nueva contraseña de 10 caracteres alfanuméricos
            $nuevaPassword = bin2hex(random_bytes(5));

            // Hashear la nueva contraseña
            $hashedPassword = password_hash($nuevaPassword, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $updateQuery = $this->conn->prepare("UPDATE Usuario SET password = :password WHERE email = :email");
            $updateQuery->bindValue(":password", $hashedPassword);
            $updateQuery->bindValue(":email", $email);

            if ($updateQuery->execute()) {
                // Enviar el correo con la nueva contraseña
                $mailService = new MailService();
                $asunto      = "Recuperación de contraseña - The Closet";
                $cuerpo      = "<p>Hola,</p>
            <p>Hemos generado una nueva contraseña para tu cuenta en <strong>The Closet</strong>:</p>
            <p style='font-size: 18px; font-weight: bold; color: #007bff;'>$nuevaPassword</p>
            <p>Te recomendamos iniciar sesión lo antes posible y cambiar tu contraseña por una de tu elección para mayor seguridad.</p>
            <p>Si no solicitaste este cambio, por favor ignora este mensaje.</p>
            <p>Saludos,<br><strong>The Closet Quilichao</strong></p>";

                if ($mailService->enviarCorreo($email, $asunto, $cuerpo)) {
                    return ["message" => "Contraseña restablecida y enviada por correo."];
                } else {
                    return ["error" => "Contraseña cambiada, pero error al enviar el correo."];
                }
            } else {
                return ["error" => "Error al actualizar la contraseña."];
            }
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        }
    }

}