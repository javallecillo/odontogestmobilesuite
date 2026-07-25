import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;
Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class Notificacion {
  final int    id;
  final String titulo;
  final String mensaje;
  final bool   leida;
  final String fecha;

  const Notificacion({
    required this.id, required this.titulo, required this.mensaje,
    required this.leida, required this.fecha,
  });

  factory Notificacion.fromJson(Map<String, dynamic> j) => Notificacion(
    id:      j['id_notificacion'] ?? 0,
    titulo:  j['titulo']          ?? '',
    mensaje: j['mensaje']         ?? '',
    leida:   (j['leida'] == 1 || j['leida'] == true),
    fecha:   j['fecha']           ?? '',
  );
}

class NotificacionesResult {
  final List<Notificacion> lista;
  final int noLeidas;
  const NotificacionesResult({required this.lista, required this.noLeidas});
  factory NotificacionesResult.empty() =>
      const NotificacionesResult(lista: [], noLeidas: 0);
}

class NotificacionesService {
  /// Retorna lista + conteo de no leídas
  static Future<NotificacionesResult> listar({
    bool soloNoLeidas = false,
  }) async {
    try {
      final url = '$_kBase/notificaciones/listar.php'
          '${soloNoLeidas ? '?solo_no_leidas=1' : ''}';
      final res = await http.get(Uri.parse(url), headers: _h)
          .timeout(const Duration(seconds: 8));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          final lista = (body['notificaciones'] as List)
              .map((e) => Notificacion.fromJson(e))
              .toList();
          return NotificacionesResult(
            lista:    lista,
            noLeidas: body['total_no_leidas'] ?? 0,
          );
        }
      }
    } catch (_) {}
    return NotificacionesResult.empty();
  }

  static Future<bool> marcarLeida(int id) async {
    try {
      final res = await http.post(
        Uri.parse('$_kBase/notificaciones/marcar_leida.php'),
        headers: _h,
        body: jsonEncode({'id_notificacion': id}),
      ).timeout(const Duration(seconds: 6));
      return res.statusCode == 200;
    } catch (_) { return false; }
  }

  static Future<bool> marcarTodasLeidas() async {
    try {
      final res = await http.post(
        Uri.parse('$_kBase/notificaciones/marcar_leida.php'),
        headers: _h,
        body: jsonEncode({'todas': true}),
      ).timeout(const Duration(seconds: 6));
      return res.statusCode == 200;
    } catch (_) { return false; }
  }

  /// Dispara la generación de alertas de citas (hoy + mañana)
  static Future<void> generarAlertas() async {
    try {
      await http.post(
        Uri.parse('$_kBase/notificaciones/generar_citas.php'),
        headers: _h,
      ).timeout(const Duration(seconds: 8));
    } catch (_) {}
  }
}
