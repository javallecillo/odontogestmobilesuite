import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/gradient_app_bar.dart';
import '../../../data/services/facturacion_service.dart';

class FacturacionScreen extends StatefulWidget {
  const FacturacionScreen({super.key});
  @override
  State<FacturacionScreen> createState() => _FacturacionScreenState();
}

class _FacturacionScreenState extends State<FacturacionScreen> {
  String              _filtro   = 'all';
  List<Factura>       _facturas = [];
  ResumenFacturacion  _resumen  = ResumenFacturacion.empty();
  bool                _loading  = true;

  @override
  void initState() { super.initState(); _cargar(); }

  Future<void> _cargar() async {
    setState(() => _loading = true);
    final r = await FacturacionService.listar(estado: _filtro);
    if (mounted) setState(() {
      _facturas = r.facturas;
      _resumen  = r.resumen;
      _loading  = false;
    });
  }

  Color _colorEstado(String e) => switch(e) {
    'pagada'  => AppColors.success,
    'anulada' => AppColors.error,
    _         => AppColors.primary,
  };

  String _labelEstado(String e) => switch(e) {
    'emitida' => 'Emitida',
    'pagada'  => 'Pagada',
    'anulada' => 'Anulada',
    _         => e,
  };

  String _fmtLps(double v) => 'L. ${v.toStringAsFixed(2)}';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const GradientAppBar(title: 'Facturación'),
      body: Column(
        children: [
          // Resumen cards
          if (!_loading) _buildResumen(),

          // Filtros
          Container(
            color: AppColors.surface,
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 8),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: ['all','emitida','pagada','anulada'].map((f) =>
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: ChoiceChip(
                      label: Text(f == 'all' ? 'Todas' : _labelEstado(f), style: const TextStyle(fontSize: 12)),
                      selected: _filtro == f,
                      selectedColor: AppColors.primary.withOpacity(0.15),
                      onSelected: (_) { setState(() => _filtro = f); _cargar(); },
                      visualDensity: VisualDensity.compact,
                    ),
                  ),
                ).toList(),
              ),
            ),
          ),

          Expanded(
            child: RefreshIndicator(
              onRefresh: _cargar,
              child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _facturas.isEmpty
                  ? ListView(children: [
                      SizedBox(
                        height: 300,
                        child: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                          Icon(Icons.receipt_long, size: 60, color: AppColors.border),
                          const SizedBox(height: 12),
                          Text('Sin facturas', style: AppTypography.body(color: AppColors.textMuted)),
                        ])),
                      )
                    ])
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(12, 12, 12, 80),
                      itemCount: _facturas.length,
                      itemBuilder: (_, i) => _FacturaCard(
                        f: _facturas[i],
                        colorEstado: _colorEstado(_facturas[i].estado),
                        labelEstado: _labelEstado(_facturas[i].estado),
                        fmtLps: _fmtLps,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResumen() {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 12),
      child: Row(
        children: [
          _ResCard('Total facturas', '${_resumen.totalFacturas}', AppColors.primary),
          const SizedBox(width: 8),
          _ResCard('Cobrado', _fmtLps(_resumen.totalCobrado), AppColors.success),
          const SizedBox(width: 8),
          _ResCard('Pendiente', _fmtLps(_resumen.totalPendiente), AppColors.error),
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
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: TextStyle(fontSize: 11, color: color)),
          const SizedBox(height: 4),
          Text(valor, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: color)),
        ]),
      ),
    );
  }
}

class _FacturaCard extends StatelessWidget {
  const _FacturaCard({
    required this.f, required this.colorEstado,
    required this.labelEstado, required this.fmtLps,
  });
  final Factura  f;
  final Color    colorEstado;
  final String   labelEstado;
  final String Function(double) fmtLps;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      elevation: 2,
      child: IntrinsicHeight(
        child: Row(
          children: [
            Container(
              width: 6,
              decoration: BoxDecoration(
                color: colorEstado,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(12), bottomLeft: Radius.circular(12)),
              ),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Row(children: [
                    Expanded(child: Text(f.numero,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14))),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: colorEstado.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(labelEstado,
                        style: TextStyle(color: colorEstado, fontSize: 11, fontWeight: FontWeight.w600)),
                    ),
                  ]),
                  const SizedBox(height: 4),
                  Text(f.paciente, style: AppTypography.body(color: AppColors.textDark)),
                  const SizedBox(height: 4),
                  Row(children: [
                    const Icon(Icons.calendar_today, size: 13, color: AppColors.textMuted),
                    const SizedBox(width: 4),
                    Text(f.fechaEmision, style: AppTypography.caption(color: AppColors.textMuted)),
                    const Spacer(),
                    Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
                      Text(fmtLps(f.total),
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.primary)),
                      if (f.tasaImpuesto != '0')
                        Text('ISV ${f.tasaImpuesto}%',
                          style: AppTypography.caption(color: AppColors.textMuted)),
                    ]),
                  ]),
                  const SizedBox(height: 4),
                  Text('Pago: ${f.metodoPago}', style: AppTypography.caption(color: AppColors.textMuted)),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
