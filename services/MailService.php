<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php'; // Ruta correcta al autoload

class MailService {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP de Mailtrap
            $this->mail->isSMTP();
            $this->mail->Host = 'sandbox.smtp.mailtrap.io'; // ✅ CORREGIDO
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'c1d196e92b2f2f'; // ✅ Asegúrate de que sea correcto
            $this->mail->Password = '47b1b36a85b03d';       // ✅ Copia exactamente como aparece en Mailtrap
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 2525; // ✅ Mailtrap usa 2525

            // Remitente
            $this->mail->setFrom('jcvm2001valencia@gmail.com', 'Juan Camilo');
        } catch (Exception $e) {
            echo "Error al configurar PHPMailer: " . $e->getMessage();
        }
    }

    public function enviarCorreo($destinatario, $asunto, $cuerpo) {
        try {
            $this->mail->clearAddresses(); // 🔄 Evitar acumulación si se reusa el objeto
            $this->mail->addAddress($destinatario);
            $this->mail->Subject = $asunto;
            $this->mail->isHTML(true);
            $this->mail->Body = $cuerpo;

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
?>