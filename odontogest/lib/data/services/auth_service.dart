// AuthService — comunicación con el endpoint POST /auth/login
// El rol es determinado por el backend, nunca por el cliente.
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../core/app_config.dart';
import '../../../core/session/app_session.dart';

const String _kBaseUrl = AppConfig.apiBase;
Map<String, String> get _hAuth => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class AuthResult {
  final bool    success;
  final String? token;
  final String? rol;
  final String? nombre;
  final int?    idUsuario;
  final String? usuario;
  final String? correo;
  final String? telefono;
  final String? errorMsg;

  const AuthResult({
    required this.success,
    this.token,
    this.rol,
    this.nombre,
    this.idUsuario,
    this.usuario,
    this.correo,
    this.telefono,
    this.errorMsg,
  });
}

class AuthService {
  static const _timeout = Duration(seconds: 10);

  /// Login: retorna AuthResult con token + datos de perfil.
  static Future<AuthResult> login({
    required String usuario,
    required String contrasena,
  }) async {
    try {
      final response = await http.post(
        Uri.parse('$_kBaseUrl/auth/login.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'usuario': usuario, 'contrasena': contrasena}),
      ).timeout(_timeout);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return AuthResult(
          success:    true,
          token:      body['token']      as String?,
          rol:        body['rol']        as String?,
          nombre:     body['nombre']     as String?,
          idUsuario:  body['id_usuario'] as int?,
          usuario:    body['usuario']    as String?,
          correo:     body['correo']     as String?,
          telefono:   body['telefono']   as String?,
        );
      }
      return AuthResult(
        success:  false,
        errorMsg: body['mensaje'] as String? ?? 'Error desconocido',
      );
    } on Exception catch (e) {
      return AuthResult(
        success:  false,
        errorMsg: 'No se pudo conectar con el servidor.\n$e',
      );
    }
  }

  /// Actualizar datos de perfil del usuario autenticado.
  /// Envía sólo los campos que no sean nulos.
  static Future<Map<String, dynamic>> actualizarPerfil({
    String? nombre,
    String? usuario,
    String? correo,
    String? telefono,
    String? nuevaContrasena,
    String? contrasenaActual,
  }) async {
    try {
      final payload = <String, dynamic>{};
      if (nombre   != null && nombre.isNotEmpty)   payload['nombre']    = nombre;
      if (usuario  != null && usuario.isNotEmpty)  payload['usuario']   = usuario;
      if (correo   != null && correo.isNotEmpty)   payload['correo']    = correo;
      if (telefono != null && telefono.isNotEmpty) payload['telefono']  = telefono;
      if (nuevaContrasena  != null && nuevaContrasena.isNotEmpty) {
        payload['nueva_contrasena']    = nuevaContrasena;
        payload['contrasena_actual']   = contrasenaActual ?? '';
      }

      final res = await http.put(
        Uri.parse('$_kBaseUrl/usuarios/perfil.php'),
        headers: _hAuth,
        body: jsonEncode(payload),
      ).timeout(_timeout);

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
