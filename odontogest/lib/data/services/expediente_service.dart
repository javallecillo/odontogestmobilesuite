// ── ExpedienteService — todas las llamadas API del expediente clínico ──
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;

Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

// ── Modelos ──────────────────────────────────────────────────

class PacienteResumen {
  final int    idPaciente;
  final int?   idExpediente;
  final String nombre;
  final String? fechaNacimiento;
  final String? sexo;
  final String? telefono;
  final String? correo;
  final String? numExpediente;
  final String? observaciones;
  final List<String> alergias;
  final List<String> enfermedades;
  final Map<String, int> resumen;

  const PacienteResumen({
    required this.idPaciente,
    required this.nombre,
    this.idExpediente,
    this.fechaNacimiento,
    this.sexo,
    this.telefono,
    this.correo,
    this.numExpediente,
    this.observaciones,
    this.alergias = const [],
    this.enfermedades = const [],
    this.resumen = const {},
  });

  factory PacienteResumen.fromJson(Map<String, dynamic> j) => PacienteResumen(
        idPaciente:      j['id_paciente']          ?? 0,
        idExpediente:    j['id_expediente'],
        nombre:          j['nombre_completo']       ?? 'Paciente',
        fechaNacimiento: j['fecha_nacimiento'],
        sexo:            j['sexo'],
        telefono:        j['telefono'],
        correo:          j['correo'],
        numExpediente:   j['numero_expediente'],
        observaciones:   j['observaciones'],
        alergias:        List<String>.from(j['alergias']     ?? []),
        enfermedades:    List<String>.from(j['enfermedades'] ?? []),
        resumen:         Map<String, int>.from(
          (j['resumen'] as Map?)?.map((k, v) => MapEntry(k, (v as num).toInt())) ?? {},
        ),
      );
}

class BusquedaPaciente {
  final int    idPaciente;
  final int?   idExpediente;
  final String nombre;
  final String? telefono;
  final String? numExpediente;

  const BusquedaPaciente({
    required this.idPaciente,
    required this.nombre,
    this.idExpediente,
    this.telefono,
    this.numExpediente,
  });

  factory BusquedaPaciente.fromJson(Map<String, dynamic> j) => BusquedaPaciente(
        idPaciente:    j['id_paciente']      ?? 0,
        idExpediente:  j['id_expediente'],
        nombre:        j['nombre_completo']  ?? '',
        telefono:      j['telefono'],
        numExpediente: j['numero_expediente'],
      );
}

class CondicionDiente {
  final String condicion;
  final String color;
  const CondicionDiente({required this.condicion, required this.color});
  factory CondicionDiente.fromJson(Map<String, dynamic> j) =>
      CondicionDiente(condicion: j['condicion'] ?? '', color: j['color'] ?? '#FF0000');
}

class Receta {
  final int    idReceta;
  final String medicamento;
  final String dosis;
  final String frecuencia;
  final String duracion;
  final String? notas;
  final String fecha;
  final String? odontologo;

  const Receta({
    required this.idReceta,
    required this.medicamento,
    required this.dosis,
    required this.frecuencia,
    required this.duracion,
    this.notas,
    required this.fecha,
    this.odontologo,
  });

  factory Receta.fromJson(Map<String, dynamic> j) => Receta(
        idReceta:    j['id_receta']    ?? 0,
        medicamento: j['medicamento']  ?? '',
        dosis:       j['dosis']        ?? '',
        frecuencia:  j['frecuencia']   ?? '',
        duracion:    j['duracion']     ?? '',
        notas:       j['notas'],
        fecha:       j['fecha_emision'] ?? '',
        odontologo:  j['odontologo'],
      );
}

class Tratamiento {
  final int    idHistorial;
  final String tratamiento;
  final String? notas;
  final String fechaInicio;
  final String? fechaFin;
  final double costo;
  final String estado;
  final String? odontologo;

  const Tratamiento({
    required this.idHistorial,
    required this.tratamiento,
    this.notas,
    required this.fechaInicio,
    this.fechaFin,
    required this.costo,
    required this.estado,
    this.odontologo,
  });

  factory Tratamiento.fromJson(Map<String, dynamic> j) => Tratamiento(
        idHistorial:  j['id_historial'] ?? 0,
        tratamiento:  j['tratamiento']  ?? '',
        notas:        j['notas'],
        fechaInicio:  j['fecha_inicio'] ?? '',
        fechaFin:     j['fecha_fin'],
        costo:        double.tryParse(j['costo']?.toString() ?? '0') ?? 0,
        estado:       j['estado']       ?? 'en_proceso',
        odontologo:   j['odontologo'],
      );
}

class FotoExpediente {
  final int    id;
  final String url;
  final String? descripcion;
  final String fecha;

  const FotoExpediente({
    required this.id,
    required this.url,
    this.descripcion,
    required this.fecha,
  });

  factory FotoExpediente.fromJson(Map<String, dynamic> j) => FotoExpediente(
        id:          j['id']          ?? 0,
        url:         j['url']         ?? '',
        descripcion: j['descripcion'],
        fecha:       j['created_at']  ?? '',
      );
}

// ── Service ──────────────────────────────────────────────────

class ExpedienteService {
  static const _timeout = Duration(seconds: 10);

  // Listar todos los pacientes activos (para carga inicial)
  static Future<List<BusquedaPaciente>> listarTodos() async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/pacientes/listar.php?estado=activo&limit=100'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['pacientes'] as List)
              .map((e) => BusquedaPaciente.fromJson(e))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // Buscar pacientes por query (API)
  static Future<List<BusquedaPaciente>> buscarPacientes(String q) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/pacientes/buscar.php?q=${Uri.encodeComponent(q)}'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['pacientes'] as List)
              .map((e) => BusquedaPaciente.fromJson(e))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // Resumen del expediente
  static Future<PacienteResumen?> fetchResumen(int idPaciente) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/expediente/resumen.php?id_paciente=$idPaciente'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) return PacienteResumen.fromJson(body);
      }
    } catch (_) {}
    return null;
  }

  // Odontograma — GET
  static Future<Map<int, List<CondicionDiente>>> fetchOdontograma(int idExpediente) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/expediente/odontograma/get.php?id_expediente=$idExpediente'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          final Map<int, List<CondicionDiente>> result = {};
          for (final d in (body['dientes'] as List)) {
            final pieza = d['pieza_dental'] as int;
            result[pieza] = (d['condiciones'] as List)
                .map((c) => CondicionDiente.fromJson(c))
                .toList();
          }
          return result;
        }
      }
    } catch (_) {}
    return {};
  }

  // Odontograma — POST guardar
  static Future<bool> guardarOdontograma(
      int idExpediente, Map<int, Set<String>> dientes) async {
    try {
      final payload = {
        'id_expediente': idExpediente,
        'dientes': dientes.entries
            .map((e) => {'pieza_dental': e.key, 'condiciones': e.value.toList()})
            .toList(),
      };
      final res = await http
          .post(Uri.parse('$_kBase/expediente/odontograma/guardar.php'),
              headers: _h, body: jsonEncode(payload))
          .timeout(_timeout);
      return res.statusCode == 200 &&
          (jsonDecode(res.body)['success'] == true);
    } catch (_) {
      return false;
    }
  }

  // Recetas — GET
  static Future<List<Receta>> fetchRecetas(int idExpediente) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/expediente/recetas/listar.php?id_expediente=$idExpediente'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['recetas'] as List).map((e) => Receta.fromJson(e)).toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // Recetas — POST crear
  static Future<bool> crearReceta(Map<String, dynamic> data) async {
    try {
      final res = await http
          .post(Uri.parse('$_kBase/expediente/recetas/crear.php'),
              headers: _h, body: jsonEncode(data))
          .timeout(_timeout);
      return res.statusCode == 200 && (jsonDecode(res.body)['success'] == true);
    } catch (_) {
      return false;
    }
  }

  // Tratamientos — GET
  static Future<List<Tratamiento>> fetchTratamientos(int idPaciente) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/expediente/tratamientos/listar.php?id_paciente=$idPaciente'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['tratamientos'] as List)
              .map((e) => Tratamiento.fromJson(e))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // Tratamientos — POST crear
  static Future<bool> crearTratamiento(Map<String, dynamic> data) async {
    try {
      final res = await http
          .post(Uri.parse('$_kBase/expediente/tratamientos/crear.php'),
              headers: _h, body: jsonEncode(data))
          .timeout(_timeout);
      return res.statusCode == 200 && (jsonDecode(res.body)['success'] == true);
    } catch (_) {
      return false;
    }
  }

  // Fotos — GET
  static Future<List<FotoExpediente>> fetchFotos(int idExpediente) async {
    try {
      final res = await http
          .get(Uri.parse('$_kBase/expediente/fotos/listar.php?id_expediente=$idExpediente'),
              headers: _h)
          .timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return (body['fotos'] as List)
              .map((e) => FotoExpediente.fromJson(e))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // Fotos — POST subir (multipart, web-compatible via XFile bytes)
  static Future<bool> subirFoto(int idExpediente, XFile foto, String descripcion) async {
    try {
      final bytes = await foto.readAsBytes();
      final req = http.MultipartRequest(
        'POST',
        Uri.parse('$_kBase/expediente/fotos/subir.php'),
      )
        ..headers['Authorization'] = 'Bearer ${AppSession.instance.token}'
        ..fields['id_expediente']  = idExpediente.toString()
        ..fields['descripcion']    = descripcion
        ..files.add(http.MultipartFile.fromBytes(
          'foto', bytes, filename: foto.name));

      final streamed = await req.send().timeout(_timeout);
      final res      = await http.Response.fromStream(streamed);
      return res.statusCode == 200 && (jsonDecode(res.body)['success'] == true);
    } catch (_) {
      return false;
    }
  }

  // Catálogo de tratamientos (para el selector en _FormTratamiento)
  static Future<List<Map<String, dynamic>>> fetchCatalogTratamientos() async {
    try {
      final res = await http.get(
        Uri.parse('$_kBase/expediente/tratamientos/catalogo.php'),
        headers: _h,
      ).timeout(_timeout);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return List<Map<String, dynamic>>.from(body['tratamientos']);
        }
      }
    } catch (_) {}
    // Fallback estático si la API falla
    return [
      {'id_tratamiento': 1,  'descripcion': 'Limpieza dental',         'precio_base': 0},
      {'id_tratamiento': 2,  'descripcion': 'Extracción simple',        'precio_base': 0},
      {'id_tratamiento': 3,  'descripcion': 'Extracción quirúrgica',    'precio_base': 0},
      {'id_tratamiento': 4,  'descripcion': 'Obturación (amalgama)',    'precio_base': 0},
      {'id_tratamiento': 5,  'descripcion': 'Obturación (resina)',      'precio_base': 0},
      {'id_tratamiento': 6,  'descripcion': 'Corona porcelana',         'precio_base': 0},
      {'id_tratamiento': 7,  'descripcion': 'Corona metal-porcelana',   'precio_base': 0},
      {'id_tratamiento': 8,  'descripcion': 'Ortodoncia metálica',      'precio_base': 0},
      {'id_tratamiento': 9,  'descripcion': 'Ortodoncia estética',      'precio_base': 0},
      {'id_tratamiento': 10, 'descripcion': 'Blanqueamiento',           'precio_base': 0},
      {'id_tratamiento': 11, 'descripcion': 'Implante dental',          'precio_base': 0},
      {'id_tratamiento': 12, 'descripcion': 'Endodoncia (conducto)',    'precio_base': 0},
    ];
  }
}
