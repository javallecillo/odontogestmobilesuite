import 'dart:async';
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/gradient_app_bar.dart';
import '../../../data/services/inventario_service.dart';

class InventarioScreen extends StatefulWidget {
  const InventarioScreen({super.key});
  @override
  State<InventarioScreen> createState() => _InventarioScreenState();
}

class _InventarioScreenState extends State<InventarioScreen> {
  final _searchCtrl = TextEditingController();
  Timer? _debounce;
  String _query    = '';
  bool   _soloBajos = false;

  List<Producto>    _productos = [];
  ResumenInventario _resumen   = ResumenInventario.empty();
  bool              _loading   = true;

  @override
  void initState() { super.initState(); _cargar(); }

  @override
  void dispose() { _searchCtrl.dispose(); _debounce?.cancel(); super.dispose(); }

  Future<void> _cargar() async {
    setState(() => _loading = true);
    final r = await InventarioService.listar(q: _query, soloBajos: _soloBajos);
    if (mounted) setState(() {
      _productos = r.productos;
      _resumen   = r.resumen;
      _loading   = false;
    });
  }

  void _onSearch(String v) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () { _query = v; _cargar(); });
  }

  Color _colorNivel(String n) => switch(n) {
    'agotado' => AppColors.error,
    'bajo'    => Colors.orange,
    _         => AppColors.success,
  };

  IconData _iconNivel(String n) => switch(n) {
    'agotado' => Icons.warning_rounded,
    'bajo'    => Icons.warning_amber_rounded,
    _         => Icons.check_circle_outline,
  };

  String _labelNivel(String n) => switch(n) {
    'agotado' => 'Agotado',
    'bajo'    => 'Stock bajo',
    _         => 'OK',
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const GradientAppBar(title: 'Inventario'),
      body: Column(
        children: [
          // Resumen
          if (!_loading) _buildResumen(),

          // Buscador
          Container(
            color: AppColors.surface,
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 4),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    style: AppTypography.body(color: AppColors.textDark),
                    onChanged: _onSearch,
                    decoration: InputDecoration(
                      hintText: 'Buscar producto...',
                      prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.textMuted),
                      suffixIcon: _query.isNotEmpty
                        ? IconButton(icon: const Icon(Icons.clear, size: 18),
                            onPressed: () { _searchCtrl.clear(); _onSearch(''); })
                        : null,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                FilterChip(
                  label: const Text('Stock bajo', style: TextStyle(fontSize: 12)),
                  selected: _soloBajos,
                  selectedColor: Colors.orange.withOpacity(0.2),
                  checkmarkColor: Colors.orange,
                  onSelected: (v) { setState(() => _soloBajos = v); _cargar(); },
                  visualDensity: VisualDensity.compact,
                ),
              ],
            ),
          ),
          const SizedBox(height: 4),

          Expanded(
            child: RefreshIndicator(
              onRefresh: _cargar,
              child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _productos.isEmpty
                  ? ListView(children: [
                      SizedBox(
                        height: 300,
                        child: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Icon(Icons.inventory_2_outlined, size: 60, color: AppColors.border),
                          const SizedBox(height: 12),
                          Text('Sin productos', style: AppTypography.body(color: AppColors.textMuted)),
                        ])),
                      )
                    ])
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(12, 8, 12, 80),
                      itemCount: _productos.length,
                      itemBuilder: (_, i) => _ProductoCard(
                        p: _productos[i],
                        colorNivel: _colorNivel(_productos[i].nivelStock),
                        iconNivel:  _iconNivel(_productos[i].nivelStock),
                        labelNivel: _labelNivel(_productos[i].nivelStock),
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResumen() {
    final alertas = _resumen.agotados + _resumen.stockBajo;
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
      child: Row(
        children: [
          _ResCard('Productos', '${_resumen.totalProductos}', AppColors.primary),
          const SizedBox(width: 8),
          _ResCard('Agotados', '${_resumen.agotados}', AppColors.error),
          const SizedBox(width: 8),
          _ResCard('Stock bajo', '${_resumen.stockBajo}', Colors.orange),
          if (alertas > 0) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.error.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(children: [
                const Icon(Icons.notifications_active, size: 16, color: AppColors.error),
                const SizedBox(width: 4),
                Text('$alertas alertas', style: const TextStyle(color: AppColors.error, fontSize: 12, fontWeight: FontWeight.w600)),
              ]),
            ),
          ],
        ],
      ),
    );
  }
}

class _ResCard extends StatelessWidget {
  const _ResCard(this.label, this.valor, this.color);
  final String label, valor;
  final Color  color;
  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: TextStyle(fontSize: 11, color: color)),
          const SizedBox(height: 2),
          Text(valor, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: color)),
        ]),
      ),
    );
  }
}

class _ProductoCard extends StatelessWidget {
  const _ProductoCard({
    required this.p, required this.colorNivel,
    required this.iconNivel, required this.labelNivel,
  });
  final Producto p;
  final Color    colorNivel;
  final IconData iconNivel;
  final String   labelNivel;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 1,
      child: IntrinsicHeight(
        child: Row(
          children: [
            Container(
              width: 6,
              decoration: BoxDecoration(
                color: colorNivel,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(12), bottomLeft: Radius.circular(12)),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Text(p.nombre, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                        const SizedBox(height: 2),
                        if (p.proveedor != null)
                          Text(p.proveedor!, style: AppTypography.caption(color: AppColors.textMuted)),
                        const SizedBox(height: 4),
                        Row(children: [
                          Icon(iconNivel, size: 14, color: colorNivel),
                          const SizedBox(width: 4),
                          Text(labelNivel, style: TextStyle(color: colorNivel, fontSize: 12, fontWeight: FontWeight.w500)),
                        ]),
                      ]),
                    ),
                    // Stock + precio
                    Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                      RichText(text: TextSpan(children: [
                        TextSpan(text: '${p.stock}', style: TextStyle(
                          fontSize: 22, fontWeight: FontWeight.bold, color: colorNivel)),
                        TextSpan(text: ' ${p.unidadMedida ?? 'u'}',
                          style: const TextStyle(fontSize: 12, color: AppColors.textMuted)),
                      ])),
                      Text('mín: ${p.stockMinimo}',
                        style: AppTypography.caption(color: AppColors.textMuted)),
                      const SizedBox(height: 4),
                      Text('L. ${p.precioVenta.toStringAsFixed(2)}',
                        style: const TextStyle(fontWeight: FontWeight.w600, color: AppColors.primary, fontSize: 13)),
                    ]),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
