import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../core/app_config.dart';
import '../../core/session/app_session.dart';

const String _kBase = AppConfig.apiBase;

Map<String, String> get _headers => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class CumpleanoRegistro {
  final int idPaciente;
  final String nombre;
  final String fechaNacimiento;
  final String telefono;

  const CumpleanoRegistro({
    required this.idPaciente,
    required this.nombre,
    required this.fechaNacimiento,
    required this.telefono,
  });

  factory CumpleanoRegistro.fromJson(Map<String, dynamic> json) {
    return CumpleanoRegistro(
      idPaciente: json['id_paciente'] ?? 0,
      nombre: (json['nombre'] ?? '').toString(),
      fechaNacimiento: (json['fecha_nacimiento'] ?? '').toString(),
      telefono: (json['telefono'] ?? '').toString(),
    );
  }
}

class CumpleanosService {
  static Future<List<CumpleanoRegistro>> listar() async {
    try {
      final res = await http
          .get(
            Uri.parse('$_kBase/pacientes/listar.php?estado=activo&limit=500'),
            headers: _headers,
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        if (body['success'] == true && body['pacientes'] is List) {
          return (body['pacientes'] as List)
              .map((e) => CumpleanoRegistro.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  static Future<CumpleanoRegistro?> guardar({
    required String nombre,
    required String fechaNacimiento,
    required String telefono,
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$_kBase/pacientes/guardar.php'),
            headers: _headers,
            body: jsonEncode({
              'nombre': nombre,
              'fecha_nacimiento': fechaNacimiento,
              'telefono': telefono,
            }),
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        if (body['success'] == true && body['paciente'] is Map<String, dynamic>) {
          return CumpleanoRegistro.fromJson(body['paciente'] as Map<String, dynamic>);
        }
      }
    } catch (_) {}
    return null;
  }

  static Future<bool> actualizar({
    required int idPaciente,
    required String nombre,
    required String fechaNacimiento,
    required String telefono,
  }) async {
    try {
      final res = await http
          .post(
            Uri.parse('$_kBase/pacientes/guardar.php'),
            headers: _headers,
            body: jsonEncode({
              'id_paciente': idPaciente,
              'nombre': nombre,
              'fecha_nacimiento': fechaNacimiento,
              'telefono': telefono,
            }),
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        return body['success'] == true;
      }
    } catch (_) {}
    return false;
  }

  static Future<bool> eliminar(int idPaciente) async {
    try {
      final res = await http
          .post(
            Uri.parse('$_kBase/pacientes/eliminar.php'),
            headers: _headers,
            body: jsonEncode({'id_paciente': idPaciente}),
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode == 200) {
        final body = jsonDecode(res.body) as Map<String, dynamic>;
        return body['success'] == true;
      }
    } catch (_) {}
    return false;
  }
}