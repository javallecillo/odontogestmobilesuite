import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;
Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class Factura {
  final int    idFactura;
  final String numero;
  final String paciente;
  final double subtotal;
  final double impuesto;
  final double descuento;
  final double total;
  final String tasaImpuesto;
  final String metodoPago;
  final String estado;
  final String fechaEmision;
  final String? fechaPago;

  const Factura({
    required this.idFactura, required this.numero, required this.paciente,
    required this.subtotal, required this.impuesto, required this.descuento,
    required this.total, required this.tasaImpuesto, required this.metodoPago,
    required this.estado, required this.fechaEmision, this.fechaPago,
  });

  factory Factura.fromJson(Map<String, dynamic> j) => Factura(
    idFactura:    j['id_factura']     ?? 0,
    numero:       j['numero_factura'] ?? '',
    paciente:     j['paciente']       ?? '',
    subtotal:     double.tryParse(j['subtotal']?.toString() ?? '0') ?? 0,
    impuesto:     double.tryParse(j['impuesto']?.toString() ?? '0') ?? 0,
    descuento:    double.tryParse(j['descuento']?.toString() ?? '0') ?? 0,
    total:        double.tryParse(j['total']?.toString() ?? '0') ?? 0,
    tasaImpuesto: j['tasa_impuesto']  ?? '15',
    metodoPago:   j['metodo_pago']    ?? 'efectivo',
    estado:       j['estado']         ?? 'emitida',
    fechaEmision: j['fecha_emision']  ?? '',
    fechaPago:    j['fecha_pago'],
  );
}

class ResumenFacturacion {
  final int    totalFacturas;
  final double totalCobrado;
  final double totalPendiente;
  const ResumenFacturacion({required this.totalFacturas, required this.totalCobrado, required this.totalPendiente});
  factory ResumenFacturacion.fromJson(Map<String, dynamic> j) => ResumenFacturacion(
    totalFacturas:   j['total_facturas']   ?? 0,
    totalCobrado:    double.tryParse(j['total_cobrado']?.toString()    ?? '0') ?? 0,
    totalPendiente:  double.tryParse(j['total_pendiente']?.toString()  ?? '0') ?? 0,
  );
  factory ResumenFacturacion.empty() => const ResumenFacturacion(totalFacturas: 0, totalCobrado: 0, totalPendiente: 0);
}

class FacturacionResult {
  final List<Factura>      facturas;
  final ResumenFacturacion resumen;
  const FacturacionResult({required this.facturas, required this.resumen});
  factory FacturacionResult.empty() => FacturacionResult(facturas: [], resumen: ResumenFacturacion.empty());
}

class FacturacionService {
  static Future<FacturacionResult> listar({String estado = 'all'}) async {
    try {
      final res = await http.get(
        Uri.parse('$_kBase/facturacion/listar.php?estado=$estado&limit=30'),
        headers: _h,
      ).timeout(const Duration(seconds: 10));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return FacturacionResult(
            facturas: (body['facturas'] as List).map((e) => Factura.fromJson(e)).toList(),
            resumen:  ResumenFacturacion.fromJson(body['resumen'] ?? {}),
          );
        }
      }
    } catch (_) {}
    return FacturacionResult.empty();
  }
}
