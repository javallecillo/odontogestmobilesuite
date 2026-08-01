<?php
// ── POST /expediente/fotos/subir.php ─────────────────────────
// Multipart form-data: { id_expediente, descripcion?, foto (file) }
// Guarda el archivo en /uploads/expedientes/{id_expediente}/
// Registra en imagenes + expediente_fotos

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

getAuthUser();

$idExp      = (int)($_POST['id_expediente'] ?? 0);
$descripcion= trim($_POST['descripcion']    ?? '');

if (!$idExp) error(400, 'id_expediente requerido');
if (empty($_FILES['foto'])) error(400, 'Archivo foto requerido');

$file = $_FILES['foto'];
if ($file['error'] !== UPLOAD_ERR_OK) error(400, 'Error al subir el archivo');

// Validar tipo MIME
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$mime    = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed)) error(400, 'Solo se permiten imágenes JPG, PNG, WEBP o GIF');

// Limitar tamaño: 5 MB
if ($file['size'] > 5 * 1024 * 1024) error(400, 'La imagen no debe superar 5 MB');

// Crear carpeta destino
$uploadDir = __DIR__ . '/../../uploads/expedientes/' . $idExp . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Nombre único
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('foto_', true) . '.' . strtolower($ext);
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    error(500, 'No se pudo guardar el archivo en el servidor');
}

// URL pública relativa (XAMPP sirve desde htdocs/odontogest_api)
$url = '/odontogest_api/uploads/expedientes/' . $idExp . '/' . $filename;

try {
    $db = getDB();

    // Obtener id_kv para 'expedientes'/'fotos'
    $kv = $db->prepare("SELECT id_kv_img FROM kv_img WHERE kv_key = 'expedientes' LIMIT 1");
    $kv->execute();
    $kvRow   = $kv->fetch();
    $idKv    = $kvRow ? (int)$kvRow['id_kv_img'] : 1;

    $db->beginTransaction();

    // Insertar en imagenes
    $insImg = $db->prepare("
        INSERT INTO imagenes (id_kv_img, url, nombre_archivo, mime_type)
        VALUES (:kv, :url, :nombre, :mime)
    ");
    $insImg->execute([
        ':kv'     => $idKv,
        ':url'    => $url,
        ':nombre' => $filename,
        ':mime'   => $mime,
    ]);
    $idImagen = (int)$db->lastInsertId();

    // Vincular al expediente
    $insEf = $db->prepare("
        INSERT INTO expediente_fotos (id_expediente, id_imagen, descripcion)
        VALUES (:eid, :img, :desc)
    ");
    $insEf->execute([
        ':eid'  => $idExp,
        ':img'  => $idImagen,
        ':desc' => $descripcion ?: null,
    ]);

    $db->commit();

    ok([
        'id_imagen'  => $idImagen,
        'url'        => $url,
        'nombre'     => $filename,
        'mensaje'    => 'Foto subida correctamente',
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    // Si falló BD, eliminar archivo subido
    if (file_exists($destPath)) unlink($destPath);
    error(500, $e->getMessage());
}
