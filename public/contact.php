<?php
// Configuración
$destinatario = "contacto@iaparatodoslatam.com";
$respuesta_json = ["success" => false, "message" => ""];

// Solo procesar POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    
    // Obtener y sanitizar datos
    $nombre = strip_tags(trim($_POST["nombre"] ?? ""));
    $correo = filter_var(trim($_POST["correo"] ?? ""), FILTER_SANITIZE_EMAIL);
    $telefono = strip_tags(trim($_POST["telefono"] ?? ""));
    $organizacion = strip_tags(trim($_POST["organizacion"] ?? ""));
    $tipo_interes = strip_tags(trim($_POST["tipo_interes"] ?? ""));
    $mensaje = htmlspecialchars(trim($_POST["mensaje"] ?? ""));

    // Validar campos requeridos
    if (empty($nombre) || empty($correo) || empty($tipo_interes) || empty($mensaje)) {
        $respuesta_json["message"] = "Por favor rellena todos los campos obligatorios.";
        echo json_encode($respuesta_json);
        exit;
    }

    // Validar email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $respuesta_json["message"] = "El correo no es válido.";
        echo json_encode($respuesta_json);
        exit;
    }

    // Preparar contenido del email
    $asunto = "Nuevo mensaje de contacto: $nombre";
    $cuerpo = "Has recibido un nuevo mensaje desde el sitio web.\n\n";
    $cuerpo .= "Nombre: $nombre\n";
    $cuerpo .= "Correo: $correo\n";
    if (!empty($telefono)) $cuerpo .= "Teléfono: $telefono\n";
    if (!empty($organizacion)) $cuerpo .= "Organización: $organizacion\n";
    $cuerpo .= "Tipo de interés: $tipo_interes\n";
    $cuerpo .= "\nMensaje:\n$mensaje\n";

    // Cabeceras
    $headers = "From: Formulario Web <noreply@iaparatodoslatam.com>\r\n";
    $headers .= "Reply-To: $correo\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Enviar email
    if (mail($destinatario, $asunto, $cuerpo, $headers)) {
        $respuesta_json["success"] = true;
        $respuesta_json["message"] = "¡Mensaje enviado con éxito! Nos contactaremos pronto.";
    } else {
        $respuesta_json["message"] = "Hubo un error al enviar. Intenta de nuevo.";
    }

    echo json_encode($respuesta_json);
    exit;
}

// Si no es POST, retornar error
header("Content-Type: application/json", true, 405);
echo json_encode(["success" => false, "message" => "Método no permitido"]);
?>
