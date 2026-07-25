// OdontogramScreen — Odontograma FDI interactivo.
// Multi-condición por diente + brackets + guardado en BD via API.
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../data/services/expediente_service.dart';

// ── Condiciones disponibles ───────────────────────────────────
enum ToothCondition {
  sano, caries, extraccion, corona, obturacion,
  ausente, implante, fractura, bracket,
}

extension ToothConditionExt on ToothCondition {
  String get label => switch (this) {
    ToothCondition.sano       => 'Sano',
    ToothCondition.caries     => 'Caries',
    ToothCondition.extraccion => 'Extracción',
    ToothCondition.corona     => 'Corona',
    ToothCondition.obturacion => 'Obturación',
    ToothCondition.ausente    => 'Ausente',
    ToothCondition.implante   => 'Implante',
    ToothCondition.fractura   => 'Fractura',
    ToothCondition.bracket    => 'Bracket',
  };

  Color get color => switch (this) {
    ToothCondition.sano       => const Color(0xFF4CAF50),
    ToothCondition.caries     => const Color(0xFFE53935),
    ToothCondition.extraccion => const Color(0xFF212121),
    ToothCondition.corona     => const Color(0xFFFFD600),
    ToothCondition.obturacion => const Color(0xFF1565C0),
    ToothCondition.ausente    => const Color(0xFF9E9E9E),
    ToothCondition.implante   => const Color(0xFF7B1FA2),
    ToothCondition.fractura   => const Color(0xFFFF6F00),
    ToothCondition.bracket    => const Color(0xFF00BCD4),
  };

  IconData get icon => switch (this) {
    ToothCondition.sano       => Icons.check_circle_outline,
    ToothCondition.caries     => Icons.warning_amber_outlined,
    ToothCondition.extraccion => Icons.close,
    ToothCondition.corona     => Icons.workspace_premium_outlined,
    ToothCondition.obturacion => Icons.square_outlined,
    ToothCondition.ausente    => Icons.remove_circle_outline,
    ToothCondition.implante   => Icons.anchor_outlined,
    ToothCondition.fractura   => Icons.bolt_outlined,
    ToothCondition.bracket    => Icons.linear_scale,
  };

  String get apiKey => label; // coincide con los valores que espera el PHP
}

// ── Screen ───────────────────────────────────────────────────

class OdontogramScreen extends StatefulWidget {
  final int    idExpediente;
  final String nombrePaciente;

  const OdontogramScreen({
    super.key,
    required this.idExpediente,
    required this.nombrePaciente,
  });

  @override
  State<OdontogramScreen> createState() => _OdontogramScreenState();
}

class _OdontogramScreenState extends State<OdontogramScreen> {
  // Mapa pieza_dental → condiciones seleccionadas
  final Map<int, Set<ToothCondition>> _teeth = {};
  ToothCondition _activeTool = ToothCondition.caries;
  int?  _selectedTooth;
  bool  _loading  = true;
  bool  _saving   = false;
  bool  _modified = false;

  final _upper = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
  final _lower = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];

  @override
  void initState() {
    super.initState();
    _cargarOdontograma();
  }

  // ── Carga desde API ──────────────────────────────────────────
  Future<void> _cargarOdontograma() async {
    setState(() => _loading = true);
    final data = await ExpedienteService.fetchOdontograma(widget.idExpediente);
    if (!mounted) return;

    final Map<int, Set<ToothCondition>> parsed = {};
    data.forEach((pieza, conds) {
      final set = <ToothCondition>{};
      for (final c in conds) {
        final match = ToothCondition.values
            .where((t) => t.label == c.condicion)
            .firstOrNull;
        if (match != null) set.add(match);
      }
      if (set.isNotEmpty) parsed[pieza] = set;
    });

    setState(() {
      _teeth
        ..clear()
        ..addAll(parsed);
      _loading  = false;
      _modified = false;
    });
  }

  // ── Guardar en API ───────────────────────────────────────────
  Future<void> _guardar() async {
    setState(() => _saving = true);

    // Convertir Map<int, Set<ToothCondition>> → Map<int, Set<String>>
    final payload = _teeth.map(
      (k, v) => MapEntry(k, v.map((c) => c.apiKey).toSet()),
    );

    final ok = await ExpedienteService.guardarOdontograma(
        widget.idExpediente, payload);

    if (!mounted) return;
    setState(() { _saving = false; _modified = !ok; });

    ScaffoldMessenger.of(context)
      ..clearSnackBars()
      ..showSnackBar(SnackBar(
        content: Text(ok ? 'Odontograma guardado ✓' : 'Error al guardar'),
        backgroundColor: ok ? AppColors.success : AppColors.error,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ));
  }

  // ── Tap en diente ────────────────────────────────────────────
  void _onTap(int pieza) {
    setState(() {
      _selectedTooth = pieza;
      final set = _teeth.putIfAbsent(pieza, () => {});

      if (_activeTool == ToothCondition.sano) {
        // Sano limpia todas las condiciones
        set.clear();
        if (set.isEmpty) _teeth.remove(pieza);
      } else {
        // Toggle: si ya tiene la condición la quita, si no la agrega
        if (set.contains(_activeTool)) {
          set.remove(_activeTool);
          if (set.isEmpty) _teeth.remove(pieza);
        } else {
          // Bracket coexiste con todo; extracción/ausente limpian el resto
          if (_activeTool == ToothCondition.extraccion ||
              _activeTool == ToothCondition.ausente) {
            set
              ..clear()
              ..add(_activeTool);
          } else {
            set
              ..remove(ToothCondition.extraccion)
              ..remove(ToothCondition.ausente)
              ..add(_activeTool);
          }
        }
      }
      _modified = true;
    });
  }

  // ── Color representativo del diente ──────────────────────────
  Color _primaryColor(int pieza) {
    final conds = _teeth[pieza];
    if (conds == null || conds.isEmpty) return const Color(0xFFFFF9C4);
    // Prioridad visual: extraccion/ausente > resto
    if (conds.contains(ToothCondition.extraccion) ||
        conds.contains(ToothCondition.ausente)) return Colors.grey.shade300;
    return conds.first.color.withAlpha(200);
  }

  bool _isExtracted(int pieza) {
    final c = _teeth[pieza];
    return c != null &&
        (c.contains(ToothCondition.extraccion) ||
            c.contains(ToothCondition.ausente));
  }

  bool _hasBracket(int pieza) =>
      _teeth[pieza]?.contains(ToothCondition.bracket) ?? false;

  // ── BUILD ─────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        automaticallyImplyLeading: false,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Odontograma',
                style: AppTypography.titleSmall(color: Colors.white)),
            Text(widget.nombrePaciente,
                style: AppTypography.caption(color: Colors.white70)),
          ],
        ),
        actions: [
          if (_modified)
            TextButton.icon(
              onPressed: _saving ? null : _guardar,
              icon: _saving
                  ? const SizedBox(
                      width: 16, height: 16,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Icon(Icons.save, color: Colors.white, size: 18),
              label: Text('Guardar',
                  style: AppTypography.labelSmall(color: Colors.white)),
            ),
          IconButton(
            icon: const Icon(Icons.refresh, color: Colors.white),
            onPressed: _cargarOdontograma,
            tooltip: 'Recargar',
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : Column(
              children: [
                Expanded(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                    child: Column(
                      children: [
                        _buildToolSelector(),
                        const SizedBox(height: 12),
                        _buildChart(),
                        const SizedBox(height: 12),
                        if (_selectedTooth != null) _buildToothDetail(),
                        const SizedBox(height: 12),
                        _buildLegend(),
                        const SizedBox(height: 8),
                        _buildSummary(),
                        const SizedBox(height: 16),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  // ── Selector de herramienta ───────────────────────────────────
  Widget _buildToolSelector() => _card(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Herramienta activa:',
                style: AppTypography.label(color: AppColors.primary)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 6, runSpacing: 6,
              children: ToothCondition.values.map((c) {
                final sel = _activeTool == c;
                return GestureDetector(
                  onTap: () => setState(() => _activeTool = c),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 180),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
                    decoration: BoxDecoration(
                      color: sel ? c.color : c.color.withAlpha(30),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: c.color, width: sel ? 2 : 1),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(c.icon, size: 13,
                            color: sel ? Colors.white : c.color),
                        const SizedBox(width: 4),
                        Text(c.label,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: sel ? Colors.white : c.color,
                              fontFamily: 'Inter',
                            )),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
          ],
        ),
      );

  // ── Chart FDI ────────────────────────────────────────────────
  Widget _buildChart() => _card(
        child: Column(
          children: [
            _quadrantLabel('SUPERIOR'),
            const SizedBox(height: 6),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _row(_upper.sublist(0, 8), isUpper: true),
                _midline(),
                _row(_upper.sublist(8), isUpper: true),
              ],
            ),
            const SizedBox(height: 4),
            _jawDivider(),
            const SizedBox(height: 4),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _row(_lower.sublist(0, 8), isUpper: false),
                _midline(),
                _row(_lower.sublist(8), isUpper: false),
              ],
            ),
            const SizedBox(height: 6),
            _quadrantLabel('INFERIOR'),
          ],
        ),
      );

  Widget _row(List<int> teeth, {required bool isUpper}) => Row(
        mainAxisSize: MainAxisSize.min,
        children: teeth.map((t) => _tooth(t, isUpper: isUpper)).toList(),
      );

  Widget _tooth(int pieza, {required bool isUpper}) {
    final selected  = _selectedTooth == pieza;
    final extracted = _isExtracted(pieza);
    final hasBracket = _hasBracket(pieza);
    final conds     = _teeth[pieza] ?? {};
    final bgColor   = _primaryColor(pieza);

    final shape = Container(
      width: 30, height: 38,
      decoration: BoxDecoration(
        color: extracted ? Colors.transparent : bgColor,
        borderRadius: BorderRadius.only(
          topLeft:     Radius.circular(isUpper ? 10 : 4),
          topRight:    Radius.circular(isUpper ? 10 : 4),
          bottomLeft:  Radius.circular(isUpper ? 4 : 10),
          bottomRight: Radius.circular(isUpper ? 4 : 10),
        ),
        border: Border.all(
          color: selected ? AppColors.primary : (extracted ? Colors.grey.shade400 : bgColor),
          width: selected ? 2.5 : 1,
        ),
        boxShadow: selected
            ? [BoxShadow(color: AppColors.primary.withAlpha(80), blurRadius: 6)]
            : [],
      ),
      child: extracted
          ? Center(child: Icon(Icons.close, size: 14, color: Colors.grey.shade600))
          : conds.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: conds
                        .where((c) => c != ToothCondition.bracket)
                        .take(2)
                        .map((c) => Icon(c.icon, size: 10, color: Colors.white))
                        .toList(),
                  ),
                )
              : null,
    );

    return GestureDetector(
      onTap: () => _onTap(pieza),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 130),
        width: 34,
        margin: const EdgeInsets.symmetric(horizontal: 1, vertical: 2),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: isUpper
              ? [
                  _pieceNum(pieza),
                  if (hasBracket) _bracketBar(),
                  const SizedBox(height: 2),
                  shape,
                ]
              : [
                  shape,
                  const SizedBox(height: 2),
                  if (hasBracket) _bracketBar(),
                  _pieceNum(pieza),
                ],
        ),
      ),
    );
  }

  Widget _pieceNum(int n) => Text('$n',
      textAlign: TextAlign.center,
      style: const TextStyle(
          fontSize: 7.5, fontWeight: FontWeight.w700,
          color: AppColors.primary, fontFamily: 'Inter'));

  Widget _bracketBar() => Container(
        height: 4, width: 28,
        margin: const EdgeInsets.symmetric(vertical: 1),
        decoration: BoxDecoration(
          color: ToothCondition.bracket.color,
          borderRadius: BorderRadius.circular(2),
        ),
      );

  Widget _quadrantLabel(String t) => Row(children: [
        Expanded(child: Divider(color: Colors.blueGrey.shade100, thickness: 1)),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: Text(t,
              style: const TextStyle(
                fontSize: 10, fontWeight: FontWeight.w700,
                color: Color(0xFF3B5FBD),
                letterSpacing: 1.2, fontFamily: 'Inter')),
        ),
        Expanded(child: Divider(color: Colors.blueGrey.shade100, thickness: 1)),
      ]);

  Widget _midline() => Container(
        width: 2, height: 60,
        margin: const EdgeInsets.symmetric(horizontal: 2),
        decoration: BoxDecoration(
          color: const Color(0xFF3B5FBD).withAlpha(100),
          borderRadius: BorderRadius.circular(1),
        ),
      );

  Widget _jawDivider() => Container(
        height: 2,
        decoration: BoxDecoration(
          gradient: LinearGradient(colors: [
            Colors.transparent,
            const Color(0xFF3B5FBD).withAlpha(70),
            Colors.transparent,
          ]),
        ),
      );

  // ── Detalle del diente seleccionado ──────────────────────────
  Widget _buildToothDetail() {
    final pieza = _selectedTooth!;
    final conds = _teeth[pieza] ?? {};
    return _card(
      border: Border.all(color: AppColors.primary.withAlpha(60)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.info_outline, color: AppColors.primary, size: 16),
            const SizedBox(width: 6),
            Text('Diente #$pieza',
                style: AppTypography.label(color: AppColors.primary)),
            const Spacer(),
            if (conds.isNotEmpty)
              GestureDetector(
                onTap: () => setState(() {
                  _teeth.remove(pieza);
                  _modified = true;
                }),
                child: Text('Limpiar',
                    style: AppTypography.labelSmall(color: AppColors.error)),
              ),
          ]),
          const SizedBox(height: 8),
          conds.isEmpty
              ? Text('Sin condiciones — diente sano',
                  style: AppTypography.caption(color: AppColors.textMuted))
              : Wrap(
                  spacing: 6, runSpacing: 4,
                  children: conds
                      .map((c) => Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: c.color.withAlpha(30),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: c.color),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(c.icon, size: 12, color: c.color),
                                const SizedBox(width: 4),
                                Text(c.label,
                                    style: TextStyle(
                                      fontSize: 11, color: c.color,
                                      fontWeight: FontWeight.w600,
                                    )),
                              ],
                            ),
                          ))
                      .toList(),
                ),
        ],
      ),
    );
  }

  // ── Leyenda ───────────────────────────────────────────────────
  Widget _buildLegend() => _card(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Leyenda', style: AppTypography.label(color: AppColors.primary)),
            const SizedBox(height: 8),
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              childAspectRatio: 4.5,
              mainAxisSpacing: 4, crossAxisSpacing: 4,
              children: ToothCondition.values.map((c) => Row(
                children: [
                  Container(
                    width: 16, height: 16,
                    decoration: BoxDecoration(
                      color: c.color, borderRadius: BorderRadius.circular(4)),
                    child: Icon(c.icon, size: 10, color: Colors.white),
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(c.label,
                        style: AppTypography.caption(color: AppColors.textDark),
                        overflow: TextOverflow.ellipsis),
                  ),
                ],
              )).toList(),
            ),
          ],
        ),
      );

  // ── Resumen ───────────────────────────────────────────────────
  Widget _buildSummary() {
    if (_teeth.isEmpty) return const SizedBox.shrink();
    final grouped = <ToothCondition, List<int>>{};
    _teeth.forEach((p, conds) {
      for (final c in conds) grouped.putIfAbsent(c, () => []).add(p);
    });

    return _card(
      border: Border.all(color: AppColors.primary.withAlpha(50)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.summarize_outlined, color: AppColors.primary, size: 16),
            const SizedBox(width: 6),
            Text('Resumen', style: AppTypography.label(color: AppColors.primary)),
            const Spacer(),
            Text('${_teeth.length} dientes',
                style: AppTypography.caption(color: AppColors.primary)),
          ]),
          const SizedBox(height: 8),
          ...grouped.entries.map((e) {
            final sorted = List<int>.from(e.value)..sort();
            return Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 10, height: 10,
                    margin: const EdgeInsets.only(top: 2),
                    decoration: BoxDecoration(
                      color: e.key.color, shape: BoxShape.circle),
                  ),
                  const SizedBox(width: 6),
                  Text('${e.key.label}: ',
                      style: AppTypography.caption(color: AppColors.textDark)
                          .copyWith(fontWeight: FontWeight.w600)),
                  Expanded(
                    child: Text(sorted.join(', '),
                        style: AppTypography.caption(color: AppColors.textMuted),
                        overflow: TextOverflow.ellipsis, maxLines: 2),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _card({required Widget child, BoxBorder? border}) => Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: border,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withAlpha(15),
              blurRadius: 6, offset: const Offset(0, 2))
          ],
        ),
        child: child,
      );
}
