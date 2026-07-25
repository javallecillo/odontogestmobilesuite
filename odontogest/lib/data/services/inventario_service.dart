import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../core/session/app_session.dart';

import '../../core/app_config.dart';
const String _kBase = AppConfig.apiBase;
Map<String, String> get _h => {
  'Authorization': 'Bearer ${AppSession.instance.token}',
  'Content-Type': 'application/json',
};

class Producto {
  final int    idProducto;
  final String nombre;
  final int    stock;
  final int    stockMinimo;
  final double precioCosto;
  final double precioVenta;
  final String? unidadMedida;
  final String estado;
  final String nivelStock; // ok | bajo | agotado
  final String? proveedor;

  const Producto({
    required this.idProducto, required this.nombre, required this.stock,
    required this.stockMinimo, required this.precioCosto, required this.precioVenta,
    this.unidadMedida, required this.estado, required this.nivelStock, this.proveedor,
  });

  factory Producto.fromJson(Map<String, dynamic> j) => Producto(
    idProducto:   j['id_producto']   ?? 0,
    nombre:       j['nombre']        ?? '',
    stock:        j['stock']         ?? 0,
    stockMinimo:  j['stock_minimo']  ?? 5,
    precioCosto:  double.tryParse(j['precio_costo']?.toString() ?? '0') ?? 0,
    precioVenta:  double.tryParse(j['precio_venta']?.toString() ?? '0') ?? 0,
    unidadMedida: j['unidad_medida'],
    estado:       j['estado']        ?? 'activo',
    nivelStock:   j['nivel_stock']   ?? 'ok',
    proveedor:    j['proveedor'],
  );
}

class ResumenInventario {
  final int totalProductos;
  final int agotados;
  final int stockBajo;
  const ResumenInventario({required this.totalProductos, required this.agotados, required this.stockBajo});
  factory ResumenInventario.fromJson(Map<String, dynamic> j) => ResumenInventario(
    totalProductos: j['total_productos'] ?? 0,
    agotados:       j['agotados']        ?? 0,
    stockBajo:      j['stock_bajo']      ?? 0,
  );
  factory ResumenInventario.empty() => const ResumenInventario(totalProductos: 0, agotados: 0, stockBajo: 0);
}

class InventarioResult {
  final List<Producto>    productos;
  final ResumenInventario resumen;
  const InventarioResult({required this.productos, required this.resumen});
  factory InventarioResult.empty() => InventarioResult(productos: [], resumen: ResumenInventario.empty());
}

class InventarioService {
  static Future<InventarioResult> listar({
    String q = '', bool soloBajos = false,
  }) async {
    try {
      var url = '$_kBase/inventario/listar.php?limit=50';
      if (q.isNotEmpty) url += '&q=${Uri.encodeComponent(q)}';
      if (soloBajos)    url += '&stock_bajo=1';
      final res = await http.get(Uri.parse(url), headers: _h).timeout(const Duration(seconds: 10));
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        if (body['success'] == true) {
          return InventarioResult(
            productos: (body['productos'] as List).map((e) => Producto.fromJson(e)).toList(),
            resumen:   ResumenInventario.fromJson(body['resumen'] ?? {}),
          );
        }
      }
    } catch (_) {}
    return InventarioResult.empty();
  }
}
