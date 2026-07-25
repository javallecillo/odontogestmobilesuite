<?php
/**
 * DashboardModel — Métricas para el panel web
 *
 * Tabla real en BD:
 *   factura      (no "facturas")   → estado ENUM('emitida','pagada','anulada')
 *   producto     (no "inventario") → stock, stock_minimo, estado('activo','inactivo','agotado')
 *   citas        → fecha_cita DATETIME (sin columna hora separada)
 *   pacientes    → nombre + apellidos (no nombre_completo), estado('activo','inactivo','fallecido')
 *   odontologos  → nombre + apellidos
 */
class DashboardModel {

    public static function metricas(): array {
        $db = Conexion::getInstance();

        /* ── Citas hoy ──────────────────────────────────── */
        $sCitas = $db->query("
            SELECT
                COUNT(*)                              AS total_hoy,
                SUM(estado IN ('pendiente','confirmada','en_curso')) AS pendientes,
                SUM(estado = 'atendida')              AS atendidas,
                SUM(estado = 'cancelada')             AS canceladas,
                SUM(estado = 'confirmada')            AS confirmadas
            FROM citas
            WHERE DATE(fecha_cita) = CURDATE()
        ");
        $citas = $sCitas->fetch(PDO::FETCH_ASSOC) ?: [];

        /* ── Pacientes activos ──────────────────────────── */
        $sPac = $db->query("SELECT COUNT(*) FROM pacientes WHERE estado = 'activo'");
        $pacientes = (int)$sPac->fetchColumn();

        /* ── Facturas pendientes (estado='emitida' = pendiente de cobro) ── */
        $sFact = $db->query("
            SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS monto
            FROM factura
            WHERE estado = 'emitida'
              AND DATE(fecha_emision) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $facturas = $sFact->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'monto'=>0];

        /* ── Productos con stock bajo ───────────────────── */
        $sStock = $db->query("
            SELECT COUNT(*) FROM producto
            WHERE stock <= stock_minimo AND estado = 'activo'
        ");
        $stockBajo = (int)$sStock->fetchColumn();

        /* ── Usuarios activos ───────────────────────────── */
        $sUsr = $db->query("SELECT COUNT(*) FROM usuarios WHERE estado = 'activo'");
        $usuarios = (int)$sUsr->fetchColumn();

        /* ── Próximas citas (hoy + mañana, max 8) ───────── */
        $sProx = $db->prepare("
            SELECT
                c.id_cita,
                DATE(c.fecha_cita)     AS fecha,
                TIME(c.fecha_cita)     AS hora,
                c.estado,
                CONCAT(p.nombre,' ',p.apellidos)    AS paciente,
                CONCAT(o.nombre,' ',o.apellidos)    AS odontologo,
                s.nombre                             AS servicio
            FROM citas c
            JOIN pacientes   p ON p.id_paciente  = c.id_paciente
            JOIN odontologos o ON o.id_odontologo = c.id_odontologo
            LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
            WHERE DATE(c.fecha_cita) BETWEEN CURDATE()
                                         AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              AND c.estado NOT IN ('cancelada','no_asistio')
            ORDER BY c.fecha_cita
            LIMIT 8
        ");
        $sProx->execute();
        $proximas = $sProx->fetchAll(PDO::FETCH_ASSOC);

        /* ── Gráfico: citas por estado hoy ─────────────── */
        $sGraf = $db->query("
            SELECT estado, COUNT(*) AS total
            FROM citas
            WHERE DATE(fecha_cita) = CURDATE()
            GROUP BY estado
        ");
        $grafData = $sGraf->fetchAll(PDO::FETCH_KEY_PAIR);

        /* ── Últimas 5 facturas ─────────────────────────── */
        $sFactRec = $db->query("
            SELECT f.id_factura, f.estado,
                   f.total,
                   DATE(f.fecha_emision) AS fecha,
                   CONCAT(p.nombre,' ',p.apellidos) AS paciente
            FROM factura f
            JOIN pacientes p ON p.id_paciente = f.id_paciente
            ORDER BY f.id_factura DESC
            LIMIT 5
        ");
        $ultimasFacturas = $sFactRec->fetchAll(PDO::FETCH_ASSOC);

        /* ── Productos con stock crítico (alertas) ───────── */
        $sAlerta = $db->query("
            SELECT p.id_producto, p.nombre, p.stock, p.stock_minimo
            FROM producto p
            WHERE p.stock <= p.stock_minimo AND p.estado = 'activo'
            ORDER BY (p.stock / GREATEST(p.stock_minimo,1)) ASC
            LIMIT 10
        ");
        $alertasInventario = $sAlerta->fetchAll(PDO::FETCH_ASSOC);

        return [
            /* KPIs */
            'citas_hoy'          => (int)($citas['total_hoy']  ?? 0),
            'citas_pendientes'   => (int)($citas['pendientes'] ?? 0),
            'citas_atendidas'    => (int)($citas['atendidas']  ?? 0),
            'citas_canceladas'   => (int)($citas['canceladas'] ?? 0),
            'pacientes_activos'  => $pacientes,
            'facturas_pendientes'=> (int)($facturas['total']   ?? 0),
            'monto_pendiente'    => (float)($facturas['monto'] ?? 0),
            'stock_bajo'         => $stockBajo,
            'usuarios'           => $usuarios,
            /* Listas */
            'proximas_citas'     => $proximas,
            'ultimas_facturas'   => $ultimasFacturas,
            'alertas_inventario' => $alertasInventario,
            /* Gráfico barras */
            'graf_pendiente'     => (int)($grafData['pendiente']  ?? 0),
            'graf_confirmada'    => (int)($grafData['confirmada'] ?? 0),
            'graf_atendida'      => (int)($grafData['atendida']   ?? 0),
            'graf_cancelada'     => (int)($grafData['cancelada']  ?? 0),
        ];
    }
}
