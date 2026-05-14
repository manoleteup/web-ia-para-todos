<?php

declare(strict_types=1);

/**
 * IA Para Todos LATAM — Contact form handler.
 * Objetivo: PHP 8.1+ (incl. 8.5). Sin filtros deprecados.
 *
 * Honeypot, time-trap, rate limit por IP, anti header-injection, validación.
 */
ob_start();

error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");

/**
 * @param array<string, mixed> $payload
 */
function respond(array $payload, int $status = 200): never {
    if (ob_get_length()) {
        @ob_clean();
    }
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=utf-8");
        header("Cache-Control: no-store");
        http_response_code($status);
    }
    if ($status !== 204) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    exit;
}

register_shutdown_function(static function (): void {
    $err = error_get_last();
    if ($err === null) {
        return;
    }
    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($err["type"], $fatal, true)) {
        return;
    }
    if (ob_get_length()) {
        @ob_clean();
    }
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=utf-8");
        http_response_code(500);
    }
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor. Escribe a contacto@iaparatodoslatam.com o por WhatsApp.",
        "debug"   => $err["message"] ?? "",
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * Normaliza correo sin FILTER_SANITIZE_EMAIL (deprecado en PHP 8.1, removido en 9.0).
 */
function normalize_email(string $raw): string {
    $s = trim($raw);
    if ($s === "") {
        return "";
    }
    // Quitar espacios internos y caracteres de control (correo válido no los usa)
    $s = preg_replace('/[\x00-\x1F\x7F]/', "", $s) ?? $s;
    return preg_replace('/\s+/', "", $s) ?? $s;
}

function has_header_injection(string $value): bool {
    if (preg_match("/[\r\n]/", $value) === 1) {
        return true;
    }
    $banned = ["content-type:", "mime-version:", "bcc:", "cc:", "to:", "from:"];
    $lower = strtolower($value);
    foreach ($banned as $tag) {
        if (str_contains($lower, $tag)) {
            return true;
        }
    }
    return false;
}

function text_len(string $s): int {
    if (function_exists("mb_strlen")) {
        return mb_strlen($s, "UTF-8");
    }
    return strlen($s);
}

function rate_limited(string $ip, int $max = 5, int $window_seconds = 3600): bool {
    $bucket = __DIR__ . "/.contact_rate_" . hash("sha256", $ip) . ".json";
    $now = time();

    /** @var list<int> $entries */
    $entries = [];
    if (is_readable($bucket)) {
        $raw = @file_get_contents($bucket);
        if ($raw !== false && $raw !== "") {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $t) {
                    if (is_int($t)) {
                        $entries[] = $t;
                    }
                }
            }
        }
    }

    $cutoff = $now - $window_seconds;
    $fresh = [];
    foreach ($entries as $t) {
        if ($t > $cutoff) {
            $fresh[] = $t;
        }
    }

    if (count($fresh) >= $max) {
        return true;
    }

    $fresh[] = $now;
    @file_put_contents($bucket, json_encode($fresh, JSON_THROW_ON_ERROR), LOCK_EX);
    return false;
}

function client_ip(): string {
    $keys = ["HTTP_CF_CONNECTING_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR"];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k]) && is_string($_SERVER[$k])) {
            $parts = explode(",", $_SERVER[$k]);
            return trim($parts[0]);
        }
    }
    return "0.0.0.0";
}

/* --- Procesamiento --- */

try {
    $method = $_SERVER["REQUEST_METHOD"] ?? "GET";

    if ($method === "OPTIONS") {
        respond(["success" => true], 204);
    }

    if ($method !== "POST") {
        respond(["success" => false, "message" => "Método no permitido."], 405);
    }

    if (!empty($_POST["website"])) {
        respond(["success" => true]);
    }

    $loaded_at = isset($_POST["loaded_at"]) ? (int) $_POST["loaded_at"] : 0;
    $now_ms = (int) round(microtime(true) * 1000);
    $elapsed = $now_ms - $loaded_at;

    if ($loaded_at <= 0 || $elapsed < 3000 || $elapsed > 86_400_000) {
        respond(["success" => true]);
    }

    $ip = client_ip();
    if (rate_limited($ip)) {
        respond([
            "success" => false,
            "message" => "Has enviado varios mensajes recientemente. Espera unos minutos.",
        ], 429);
    }

    $nombre = isset($_POST["nombre"]) && is_string($_POST["nombre"])
        ? trim(strip_tags($_POST["nombre"])) : "";
    $correo = isset($_POST["correo"]) && is_string($_POST["correo"])
        ? normalize_email($_POST["correo"]) : "";
    $telefono = isset($_POST["telefono"]) && is_string($_POST["telefono"])
        ? trim(strip_tags($_POST["telefono"])) : "";
    $organizacion = isset($_POST["organizacion"]) && is_string($_POST["organizacion"])
        ? trim(strip_tags($_POST["organizacion"])) : "";
    $tipo_interes = isset($_POST["tipo_interes"]) && is_string($_POST["tipo_interes"])
        ? trim(strip_tags($_POST["tipo_interes"])) : "";
    $mensaje = isset($_POST["mensaje"]) && is_string($_POST["mensaje"])
        ? trim(strip_tags($_POST["mensaje"])) : "";

    $fields = [
        "nombre" => $nombre,
        "correo" => $correo,
        "telefono" => $telefono,
        "organizacion" => $organizacion,
        "tipo_interes" => $tipo_interes,
    ];
    foreach ($fields as $k => $v) {
        if (has_header_injection($v)) {
            respond([
                "success" => false,
                "message" => "Hay caracteres no permitidos en el campo " . $k . ".",
            ], 422);
        }
    }

    if ($nombre === "" || $correo === "" || $tipo_interes === "" || $mensaje === "") {
        respond([
            "success" => false,
            "message" => "Por favor completa los campos obligatorios.",
        ], 422);
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        respond([
            "success" => false,
            "message" => "El correo no es válido.",
        ], 422);
    }
    if (text_len($nombre) > 120 || text_len($mensaje) > 5000) {
        respond([
            "success" => false,
            "message" => "Algún campo supera la longitud permitida.",
        ], 422);
    }

    $destinatario = "contacto@iaparatodoslatam.com";
    $asunto = "[Web] Nuevo contacto: " . $nombre;

    $cuerpo = "Nuevo mensaje desde el formulario web de IA Para Todos LATAM.\n";
    $cuerpo .= str_repeat("-", 60) . "\n\n";
    $cuerpo .= "Nombre:         " . $nombre . "\n";
    $cuerpo .= "Correo:         " . $correo . "\n";
    if ($telefono !== "") {
        $cuerpo .= "Teléfono:       " . $telefono . "\n";
    }
    if ($organizacion !== "") {
        $cuerpo .= "Organización:   " . $organizacion . "\n";
    }
    $cuerpo .= "Tipo interés:   " . $tipo_interes . "\n\n";
    $cuerpo .= "Mensaje:\n" . $mensaje . "\n\n";
    $cuerpo .= str_repeat("-", 60) . "\n";
    $cuerpo .= "IP: " . $ip . "\n";
    $ua = isset($_SERVER["HTTP_USER_AGENT"]) && is_string($_SERVER["HTTP_USER_AGENT"])
        ? $_SERVER["HTTP_USER_AGENT"] : "-";
    $cuerpo .= "User-Agent: " . substr($ua, 0, 200) . "\n";

    $from_email = "contacto@iaparatodoslatam.com";
    $from_name = "Formulario Web";

    $headers = [
        "From: " . $from_name . " <" . $from_email . ">",
        "Reply-To: " . $correo,
        "MIME-Version: 1.0",
        "Content-Type: text/plain; charset=UTF-8",
        "X-Mailer: PHP/" . PHP_VERSION,
    ];

    $headers_str = implode("\r\n", $headers);
    $subject_enc = "=?UTF-8?B?" . base64_encode($asunto) . "?=";

    if (!function_exists("mail")) {
        respond([
            "success" => false,
            "message" => "Este servidor no puede enviar correo (mail() no disponible). Escribe a " . $destinatario . ".",
        ], 500);
    }

    $ok = @mail($destinatario, $subject_enc, $cuerpo, $headers_str);
    if (!$ok) {
        $ok = @mail($destinatario, $subject_enc, $cuerpo, $headers_str, "-f" . $from_email);
    }

    if (!$ok) {
        respond([
            "success" => false,
            "message" => "No pudimos enviar el mensaje desde el servidor. Escribe a " . $destinatario . " o por WhatsApp.",
        ], 500);
    }

    respond([
        "success" => true,
        "message" => "¡Mensaje enviado! Nos contactaremos pronto.",
    ]);
} catch (Throwable $e) {
    error_log("[contact.php] " . $e->getMessage());
    respond([
        "success" => false,
        "message" => "Error interno. Escribe a contacto@iaparatodoslatam.com.",
        "debug" => $e->getMessage(),
    ], 500);
}
