import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;
Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class PacienteLista {
  final int     idPaciente;
  final String  nombre;
  final String  expediente;
  final String  telefono;
  final String  estado;
  final String? dni;
  final String? correo;
  final String? fechaNacimiento;
  final String? sexo;
  final int?    idExpediente;

  const PacienteLista({
    required this.idPaciente,
    required this.nombre,
    required this.expediente,
    required this.telefono,
    required this.estado,
    this.dni,
    this.correo,
    this.fechaNacimiento,
    this.sexo,
    this.idExpediente,
  });

  factory PacienteLista.fromJson(Map<String, dynamic> j) => PacienteLista(
    idPaciente:      j['id_paciente']       ?? 0,
    nombre:          j['nombre_completo']   ??
                     '${j['nombre'] ?? ''} ${j['apellidos'] ?? ''}'.trim(),
    expediente:      j['numero_expediente'] ?? '',
    telefono:        j['telefono']          ?? '',
    estado:          j['estado']            ?? 'activo',
    dni:             j['dni']               as String?,
    correo:          j['correo']            as String?,
    fechaNacimiento: j['fecha_nacimiento']  as String?,
    sexo:            j['sexo']              as String?,
    idExpediente:    j['id_expediente']     as int?,
  );
}

class PacientesResult {
  final List<PacienteLista> pacientes;
  final int total;
  final int pages;
  const PacientesResult({required this.pacientes, required this.total, required this.pages});
  factory PacientesResult.empty() => const PacientesResult(pacientes: [], total: 0, pages: 1);
}

class PacientesService {
  static Future<PacientesResult> listar({
    String q = '', String estado = 'activo', int page = 1,
  }) async {
    try {
      var url = '$_kBase/pacientes/listar.php?page=$page&limit=20&estado=$estado';
      if (q.isNotEmpty) url += '&q=${Uri.encodeComponent(q)}';
      final res = await http.get(Uri.parse(url), headers: _h).timeout(const Duration(seconds: 10));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return PacientesResult(
            pacientes: (body['pacientes'] as List).map((e) => PacienteLista.fromJson(e)).toList(),
            total:     body['total']  ?? 0,
            pages:     body['pages']  ?? 1,
          );
        }
      }
    } catch (_) {}
    return PacientesResult.empty();
  }
}
