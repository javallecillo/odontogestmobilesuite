<?php
/**
 * NotificacionesController — Endpoints AJAX para el dropdown del menú
 * obtener()     GET  → JSON lista + total sin leer
 * marcarLeida() POST JSON → marcar una notificación leída
 * marcarTodas() POST JSON → marcar todas leídas
 */
class NotificacionesController {

    public function obtener(): void {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $db = Conexion::getInstance();
        $s  = $db->prepare(
            'SELECT id_notificacion, titulo, mensaje, tipo, leida, fecha
             FROM notificaciones
             WHERE id_usuario = :id OR id_usuario IS NULL
             ORDER BY leida ASC, fecha DESC
             LIMIT 20'
        );
        $s->execute([':id' => Auth::id()]);
        $lista = $s->fetchAll(PDO::FETCH_ASSOC);

        $noLeidas = (int)array_sum(array_column($lista, 'leida') === array_map(fn() => '0', $lista)
            ? [] : array_filter(array_column($lista, 'leida'), fn($v) => $v == 0));

        // Contar sin leer directamente
        $sNL = $db->prepare(
            'SELECT COUNT(*) FROM notificaciones
             WHERE (id_usuario = :id OR id_usuario IS NULL) AND leida = 0'
        );
        $sNL->execute([':id' => Auth::id()]);
        $noLeidas = (int)$sNL->fetchColumn();

        echo json_encode(['notificaciones' => $lista, 'total_no_leidas' => $noLeidas]);
        exit;
    }

    public function marcarLeida(): void {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $body = json_decode(file_get_contents('php://input'), true);
        $id   = (int)($body['id'] ?? 0);
        if ($id) {
            $db = Conexion::getInstance();
            // Restringir al usuario actual para evitar IDOR
            $db->prepare('UPDATE notificaciones SET leida=1 WHERE id_notificacion=:id AND (id_usuario=:uid OR id_usuario IS NULL)')
               ->execute([':id' => $id, ':uid' => Auth::id()]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function marcarTodas(): void {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');

        $db = Conexion::getInstance();
        $db->prepare('UPDATE notificaciones SET leida=1 WHERE (id_usuario=:id OR id_usuario IS NULL) AND leida=0')
           ->execute([':id' => Auth::id()]);

        echo json_encode(['success' => true]);
        exit;
    }
}
