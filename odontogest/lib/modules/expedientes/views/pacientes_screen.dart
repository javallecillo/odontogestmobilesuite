import 'dart:async';
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/gradient_app_bar.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../data/services/pacientes_service.dart';
import 'buscar_paciente_screen.dart';
import 'expediente_paciente_screen.dart';

class PacientesScreen extends StatefulWidget {
  const PacientesScreen({super.key});
  @override
  State<PacientesScreen> createState() => _PacientesScreenState();
}

class _PacientesScreenState extends State<PacientesScreen> {
  final _searchCtrl   = TextEditingController();
  Timer?  _debounce;
  String  _query      = '';
  String  _filtroEstado = 'activo';

  List<PacienteLista> _pacientes = [];
  int    _total   = 0;
  int    _page    = 1;
  int    _pages   = 1;
  bool   _loading = true;
  bool   _loadingMore = false;

  final _scroll = ScrollController();

  @override
  void initState() {
    super.initState();
    _cargar();
    _scroll.addListener(_onScroll);
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _debounce?.cancel();
    _scroll.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 200 &&
        !_loadingMore && _page < _pages) {
      _cargarMas();
    }
  }

  Future<void> _cargar({bool reset = true}) async {
    if (reset) {
      setState(() { _loading = true; _page = 1; _pacientes = []; });
    }
    final PacientesResult result = await PacientesService.listar(q: _query, estado: _filtroEstado, page: 1);
    if (mounted) {
      setState(() {
        _pacientes = result.pacientes;
        _total     = result.total;
        _pages     = result.pages;
        _loading   = false;
        _page      = 1;
      });
    }
  }

  Future<void> _cargarMas() async {
    if (_loadingMore || _page >= _pages) return;
    setState(() => _loadingMore = true);
    final PacientesResult result = await PacientesService.listar(q: _query, estado: _filtroEstado, page: _page + 1);
    if (mounted) {
      setState(() {
        _pacientes.addAll(result.pacientes);
        _page++;
        _loadingMore = false;
      });
    }
  }

  void _onSearch(String v) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () {
      setState(() => _query = v);
      _cargar();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: const GradientAppBar(title: 'Pacientes'),
      body: Column(
        children: [
          _buildSearch(),
          _buildStats(),
          Expanded(child: _buildList()),
        ],
      ),
    );
  }

  Widget _buildSearch() {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 6),
      child: TextField(
        controller: _searchCtrl,
        style: AppTypography.body(color: AppColors.textDark),
        onChanged: _onSearch,
        decoration: InputDecoration(
          hintText: 'Buscar por nombre o expediente...',
          prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.textMuted),
          suffixIcon: _query.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 18),
                  onPressed: () { _searchCtrl.clear(); _onSearch(''); },
                )
              : null,
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
        ),
      ),
    );
  }

  Widget _buildStats() {
    return Container(
      color: AppColors.surface,
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
      child: Row(
        children: [
          _StatChip('$_total total', AppColors.textMuted),
          const SizedBox(width: 8),
          ...(<Map<String, String>>[
            {'key': 'activo',   'label': 'Activos'},
            {'key': 'inactivo', 'label': 'Inactivos'},
            {'key': 'all',      'label': 'Todos'},
          ]).map((t) =>
            Padding(
              padding: const EdgeInsets.only(right: 6),
              child: ChoiceChip(
                label: Text(t['label']!, style: const TextStyle(fontSize: 11)),
                selected: _filtroEstado == t['key'],
                selectedColor: AppColors.primary.withOpacity(0.15),
                onSelected: (_) { setState(() => _filtroEstado = t['key']!); _cargar(); },
                visualDensity: VisualDensity.compact,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildList() {
    if (_loading) return const Center(child: CircularProgressIndicator());
    if (_pacientes.isEmpty) {
      return Center(
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Icon(Icons.person_search, size: 64, color: AppColors.border),
          const SizedBox(height: 12),
          Text('No se encontraron pacientes', style: AppTypography.body(color: AppColors.textMuted)),
          const SizedBox(height: 12),
          TextButton.icon(
            icon: const Icon(Icons.refresh),
            label: const Text('Reintentar'),
            onPressed: _cargar,
          ),
        ]),
      );
    }

    return RefreshIndicator(
      onRefresh: _cargar,
      child: ListView.builder(
        controller: _scroll,
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
        itemCount: _pacientes.length + (_loadingMore ? 1 : 0),
        itemBuilder: (_, i) {
          if (i == _pacientes.length) {
            return const Padding(
              padding: EdgeInsets.all(20),
              child: Center(child: CircularProgressIndicator()),
            );
          }
          return _PacienteCard(p: _pacientes[i]);
        },
      ),
    );
  }
}

class _PacienteCard extends StatelessWidget {
  const _PacienteCard({required this.p});
  final PacienteLista p;

  @override
  Widget build(BuildContext context) {
    final isActivo = p.estado == 'activo';
    return AppCard(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => ExpedientePacienteScreen(
            idPaciente:     p.idPaciente,
            nombrePaciente: p.nombre,
          ),
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: isActivo ? AppColors.primaryLight : AppColors.background,
            child: Text(
              p.nombre.isNotEmpty ? p.nombre[0].toUpperCase() : '?',
              style: AppTypography.titleSmall(
                  color: isActivo ? AppColors.primary : AppColors.textMuted),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(p.nombre,
                  style: AppTypography.bodyMedium(color: AppColors.textDark)),
              const SizedBox(height: 2),
              Row(children: [
                if (p.expediente.isNotEmpty)
                  Text(p.expediente,
                      style: AppTypography.caption(color: AppColors.textMuted)),
                if (p.dni != null && p.dni!.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                    decoration: BoxDecoration(
                      color: AppColors.primaryLight,
                      borderRadius: BorderRadius.circular(4)),
                    child: Text('DNI: ${p.dni}',
                        style: AppTypography.badge(color: AppColors.primary)),
                  ),
                ],
              ]),
              if (p.telefono.isNotEmpty)
                Text(p.telefono,
                    style: AppTypography.caption(color: AppColors.textMuted)),
            ]),
          ),
          Column(crossAxisAlignment: CrossAxisAlignment.end, children: [
            StatusBadge(
              label: isActivo ? 'Activo' : 'Inactivo',
              color: isActivo ? AppColors.success : AppColors.textMuted,
            ),
            const SizedBox(height: 4),
            const Icon(Icons.chevron_right, color: AppColors.textMuted, size: 18),
          ]),
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip(this.label, this.color);
  final String label;
  final Color  color;
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(color: color.withAlpha(20), borderRadius: BorderRadius.circular(8)),
      child: Text(label, style: AppTypography.badge(color: color)),
    );
  }
}
