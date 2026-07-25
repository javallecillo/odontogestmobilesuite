import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;
Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class CitaAgenda {
  final int    idCita;
  final String hora;
  final String paciente;
  final String odontologo;
  final String servicio;
  final String estado;
  final String asistencia;
  final int    idPaciente;
  final int?   idExpediente;

  const CitaAgenda({
    required this.idCita, required this.hora, required this.paciente,
    required this.odontologo, required this.servicio, required this.estado,
    required this.asistencia, required this.idPaciente, this.idExpediente,
  });

  factory CitaAgenda.fromJson(Map<String, dynamic> j) => CitaAgenda(
    idCita:       j['id_cita']      ?? 0,
    hora:         j['hora']         ?? '--:--',
    paciente:     j['paciente']     ?? '',
    odontologo:   j['odontologo']   ?? '',
    servicio:     j['servicio']     ?? '',
    estado:       j['estado']       ?? 'pendiente',
    asistencia:   j['asistencia']   ?? 'pendiente',
    idPaciente:   j['id_paciente']  ?? 0,
    idExpediente: j['id_expediente'],
  );
}

class AgendaService {
  static Future<List<CitaAgenda>> listar({String? fecha, String estado = 'all'}) async {
    try {
      final f = fecha ?? DateTime.now().toIso8601String().substring(0, 10);
      final res = await http.get(
        Uri.parse('$_kBase/agenda/listar.php?fecha=$f&estado=$estado'),
        headers: _h,
      ).timeout(const Duration(seconds: 10));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['citas'] as List).map((e) => CitaAgenda.fromJson(e)).toList();
        }
      }
    } catch (_) {}
    return [];
  }

  static Future<bool> cambiarEstado(int idCita, String estado, {String? asistencia}) async {
    try {
      final payload = {'id_cita': idCita, 'estado': estado};
      if (asistencia != null) payload['asistencia'] = asistencia;
      final res = await http.post(
        Uri.parse('$_kBase/agenda/cambiar_estado.php'),
        headers: _h, body: jsonEncode(payload),
      ).timeout(const Duration(seconds: 8));
      return res.statusCode == 200 && (jsonDecode(res.body)['success'] == true);
    } catch (_) { return false; }
  }

  // Lista odontólogos activos para el formulario de nueva cita
  static Future<List<Map<String, dynamic>>> listarOdontologos() async {
    try {
      final res = await http.get(
        Uri.parse('$_kBase/agenda/odontologos.php'),
        headers: _h,
      ).timeout(const Duration(seconds: 8));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return List<Map<String, dynamic>>.from(body['odontologos']);
        }
      }
    } catch (_) {}
    return [];
  }

  // Slots disponibles para una fecha y odontólogo
  static Future<List<Map<String, dynamic>>> slotsDisponibles(
      int idOdontologo, String fecha) async {
    try {
      final res = await http.get(
        Uri.parse('$_kBase/agenda/slots_disponibles.php'
            '?id_odontologo=$idOdontologo&fecha=$fecha'),
        headers: _h,
      ).timeout(const Duration(seconds: 8));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return List<Map<String, dynamic>>.from(body['slots']);
        }
      }
    } catch (_) {}
    return [];
  }

  // Crear nueva cita
  static Future<Map<String, dynamic>> crearCita({
    required int    idPaciente,
    required int    idOdontologo,
    required String fechaCita,   // 'YYYY-MM-DD HH:MM'
    int?            idServicio,
    String?         notas,
  }) async {
    try {
      final payload = <String, dynamic>{
        'id_paciente':   idPaciente,
        'id_odontologo': idOdontologo,
        'fecha_cita':    fechaCita,
        if (idServicio != null) 'id_servicio': idServicio,
        if (notas != null && notas.isNotEmpty) 'notas': notas,
      };
      final res = await http.post(
        Uri.parse('$_kBase/agenda/crear.php'),
        headers: _h, body: jsonEncode(payload),
      ).timeout(const Duration(seconds: 10));
      final body = jsonDecode(res.body);
      return {
        'success': res.statusCode == 200 && body['success'] == true,
        'mensaje': body['mensaje'] ?? body['error'] ?? 'Error desconocido',
      };
    } catch (e) {
      return {'success': false, 'mensaje': e.toString()};
    }
  }
}
