// ── DashboardService — métricas + citas del día ───────────────
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;

// ── Modelos ───────────────────────────────────────────────────

class DashboardMetricas {
  final int citasHoy;
  final int atendidas;
  final int pendientes;
  final int pacientesTotal;

  const DashboardMetricas({
    required this.citasHoy,
    required this.atendidas,
    required this.pendientes,
    required this.pacientesTotal,
  });

  factory DashboardMetricas.fromJson(Map<String, dynamic> j) =>
      DashboardMetricas(
        citasHoy:       j['citas_hoy']       ?? 0,
        atendidas:      j['atendidas']        ?? 0,
        pendientes:     j['pendientes']       ?? 0,
        pacientesTotal: j['pacientes_total']  ?? 0,
      );

  factory DashboardMetricas.empty() =>
      const DashboardMetricas(citasHoy: 0, atendidas: 0, pendientes: 0, pacientesTotal: 0);
}

class CitaHoy {
  final int    idCita;
  final String hora;
  final String paciente;
  final String servicio;
  final String estado;

  const CitaHoy({
    required this.idCita,
    required this.hora,
    required this.paciente,
    required this.servicio,
    required this.estado,
  });

  factory CitaHoy.fromJson(Map<String, dynamic> j) => CitaHoy(
        idCita:   j['id_cita']  ?? 0,
        hora:     j['hora']     ?? '--:--',
        paciente: j['paciente'] ?? 'Paciente',
        servicio: j['servicio'] ?? 'Sin servicio',
        estado:   j['estado']   ?? 'pendiente',
      );
}

// ── Service ───────────────────────────────────────────────────

class DashboardService {
  static Map<String, String> get _headers => {
    'Authorization': 'Bearer ${AppSession.instance.token}',
    'Content-Type':  'application/json',
  };

  // GET /dashboard/metricas.php
  static Future<DashboardMetricas> fetchMetricas() async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/dashboard/metricas.php'), headers: _headers)
          .timeout(const Duration(seconds: 8));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        if (body['success'] == true) {
          return DashboardMetricas.fromJson(body);
        }
      }
    } catch (_) {}
    return DashboardMetricas.empty();
  }

  // GET /citas/hoy.php
  static Future<List<CitaHoy>> fetchCitasHoy() async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/citas/hoy.php'), headers: _headers)
          .timeout(const Duration(seconds: 8));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        if (body['success'] == true && body['citas'] is List) {
          return (body['citas'] as List)
              .map((e) => CitaHoy.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }
}
