// DashboardScreen — pantalla de inicio.
// Carga métricas y citas del día desde la API PHP.
import 'package:flutter/material.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/session/app_session.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../data/services/dashboard_service.dart';
import '../../../data/services/notificaciones_service.dart';
import '../../agenda/views/nueva_cita_screen.dart';
import '../../expedientes/views/buscar_paciente_screen.dart';
import 'notificaciones_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  // ── Estado ───────────────────────────────────────────────────
  bool               _loading   = true;
  DashboardMetricas  _metricas  = DashboardMetricas.empty();
  List<CitaHoy>      _citas     = [];
  int                _noLeidas  = 0;

  @override
  void initState() {
    super.initState();
    _cargarDatos();
  }

  Future<void> _cargarDatos() async {
    setState(() => _loading = true);
    // Genera alertas de citas y carga datos en paralelo
    final results = await Future.wait([
      DashboardService.fetchMetricas(),
      DashboardService.fetchCitasHoy(),
      NotificacionesService.generarAlertas().then((_) =>
          NotificacionesService.listar()),
    ]);
    if (!mounted) return;
    final notifResult = results[2] as NotificacionesResult;
    setState(() {
      _metricas = results[0] as DashboardMetricas;
      _citas    = results[1] as List<CitaHoy>;
      _noLeidas = notifResult.noLeidas;
      _loading  = false;
    });
  }

  void _abrirNotificaciones() {
    Navigator.push(context,
      MaterialPageRoute(builder: (_) => const NotificacionesScreen()),
    ).then((_) {
      // Refresca badge al volver
      NotificacionesService.listar().then((r) {
        if (mounted) setState(() => _noLeidas = r.noLeidas);
      });
    });
  }

  // ── UI ────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: _cargarDatos,
        child: CustomScrollView(
          slivers: [
            _buildHeader(context),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  if (_loading) ...[
                    const SizedBox(height: 40),
                    const Center(
                      child: CircularProgressIndicator(color: AppColors.primary),
                    ),
                  ] else ...[
                    _buildStatRow(),
                    const SizedBox(height: 16),
                    _buildAccesosRapidos(context),
                    const SizedBox(height: 16),
                    _buildCitasHoy(context),
                  ],
                ]),
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const NuevaCitaScreen()),
        ).then((_) => _cargarDatos()), // refresca al volver
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: Text('Nueva Cita',
            style: AppTypography.buttonSmall(color: Colors.white)),
      ),
    );
  }

  // ── Header ───────────────────────────────────────────────────
  Widget _buildHeader(BuildContext context) {
    final nombre = AppSession.instance.nombre ?? 'Usuario';
    final rol    = AppSession.instance.rol    ?? '';
    final hoy    = _fechaHoy();

    return SliverAppBar(
      expandedHeight: 120,
      pinned: true,
      automaticallyImplyLeading: false,
      backgroundColor: AppColors.primary,
      // Sin title — el flexibleSpace lo maneja todo
      actions: [
        Stack(
          children: [
            IconButton(
              icon: const Icon(Icons.notifications_outlined, color: Colors.white),
              onPressed: _abrirNotificaciones,
            ),
            if (_noLeidas > 0)
              Positioned(
                right: 6, top: 6,
                child: Container(
                  padding: const EdgeInsets.all(3),
                  decoration: const BoxDecoration(
                      color: Colors.red, shape: BoxShape.circle),
                  constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                  child: Text(
                    _noLeidas > 9 ? '9+' : '$_noLeidas',
                    style: const TextStyle(color: Colors.white,
                        fontSize: 9, fontWeight: FontWeight.bold),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(width: 4),
      ],
      flexibleSpace: FlexibleSpaceBar(
        collapseMode: CollapseMode.pin,
        background: Container(
          decoration: const BoxDecoration(gradient: AppGradients.primary),
          padding: const EdgeInsets.fromLTRB(16, 48, 56, 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    radius: 20,
                    backgroundColor: Colors.white.withAlpha(51),
                    child: Text(
                      nombre.isNotEmpty ? nombre[0].toUpperCase() : 'U',
                      style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 18),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(nombre,
                            style: AppTypography.titleSmall(color: Colors.white)),
                        Text('$rol · $hoy',
                            style: AppTypography.caption(color: Colors.white70)),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Métricas ─────────────────────────────────────────────────
  Widget _buildStatRow() {
    final stats = [
      _Stat('Citas hoy',  _metricas.citasHoy.toString(),       AppColors.primary, Icons.calendar_today),
      _Stat('Atendidas',  _metricas.atendidas.toString(),       AppColors.success, Icons.check_circle_outline),
      _Stat('Pendientes', _metricas.pendientes.toString(),      AppColors.warning, Icons.pending_outlined),
    ];

    return Row(
      children: stats.asMap().entries.map((e) {
        final s = e.value;
        return Expanded(
          child: AppCard(
            margin: EdgeInsets.only(right: e.key < stats.length - 1 ? 8 : 0, bottom: 0),
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(s.icon, color: s.color, size: 22),
                const SizedBox(height: 6),
                Text(s.value,
                    style: AppTypography.headlineSmall(color: s.color)),
                Text(s.label,
                    style:
                        AppTypography.captionXs(color: AppColors.textMuted)),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }

  // ── Accesos rápidos ───────────────────────────────────────────
  Widget _buildAccesosRapidos(BuildContext context) {
    final accesos = [
      _Acceso('Odontograma', Icons.grid_view_rounded, AppColors.primary,
          () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const BuscarPacienteScreen()))),
      _Acceso('Buscar Paciente', Icons.person_search_outlined, AppColors.success,
          () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const BuscarPacienteScreen()))),
      _Acceso('Nueva Cita', Icons.calendar_month_outlined, AppColors.warning,
          () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const NuevaCitaScreen()))),
      _Acceso('Expediente', Icons.folder_open_outlined, AppColors.info,
          () => Navigator.push(context,
              MaterialPageRoute(
                  builder: (_) => const BuscarPacienteScreen()))),
    ];

    return AppCard(
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Accesos rápidos',
              style: AppTypography.label(color: AppColors.textDark)),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: accesos.map((a) => _AccesoWidget(acceso: a)).toList(),
          ),
        ],
      ),
    );
  }

  // ── Lista citas del día ───────────────────────────────────────
  Widget _buildCitasHoy(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Citas de hoy',
                style: AppTypography.label(color: AppColors.textDark)),
            TextButton(
              onPressed: () {},
              child: Text('Ver todas',
                  style:
                      AppTypography.labelSmall(color: AppColors.primary)),
            ),
          ],
        ),
        const SizedBox(height: 4),
        if (_citas.isEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 20),
            child: Center(
              child: Text('Sin citas para hoy',
                  style: AppTypography.body(color: AppColors.textMuted)),
            ),
          )
        else
          ..._citas.map((c) => _CitaCard(cita: c, context: context)),
      ],
    );
  }

  // ── Helpers ───────────────────────────────────────────────────
  String _fechaHoy() {
    final now = DateTime.now();
    const dias = [
      'Lunes', 'Martes', 'Miércoles', 'Jueves',
      'Viernes', 'Sábado', 'Domingo'
    ];
    const meses = [
      '', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
      'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
    ];
    return '${dias[now.weekday - 1]} ${now.day} ${meses[now.month]}';
  }
}

// ── Cita card ──────────────────────────────────────────────────
class _CitaCard extends StatelessWidget {
  const _CitaCard({required this.cita, required this.context});
  final CitaHoy    cita;
  final BuildContext context;

  Color _colorEstado(String estado) => switch (estado) {
    'atendida'  => AppColors.success,
    'en_curso'  => AppColors.primary,
    'cancelada' => AppColors.error,
    _           => AppColors.warning,  // pendiente, confirmada
  };

  String _labelEstado(String estado) => switch (estado) {
    'atendida'   => 'Atendida',
    'en_curso'   => 'En curso',
    'confirmada' => 'Confirmada',
    'cancelada'  => 'Cancelada',
    _            => 'Pendiente',
  };

  @override
  Widget build(BuildContext context) {
    final color = _colorEstado(cita.estado);
    return AppCard(
      onTap: () => Navigator.push(context,
          MaterialPageRoute(
              builder: (_) => const BuscarPacienteScreen())),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          Container(
            padding:
                const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: AppColors.primary.withAlpha(20),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(cita.hora,
                style: AppTypography.labelSmall(color: AppColors.primary)),
          ),
          const SizedBox(width: 10),
          CircleAvatar(
            radius: 16,
            backgroundColor: AppColors.primaryLight,
            child: Text(
              cita.paciente.isNotEmpty ? cita.paciente[0] : 'P',
              style: AppTypography.labelSmall(color: AppColors.primary),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(cita.paciente,
                    style:
                        AppTypography.bodyMedium(color: AppColors.textDark)),
                Text(cita.servicio,
                    style: AppTypography.captionXs(
                        color: AppColors.textMuted)),
              ],
            ),
          ),
          StatusBadge(
              label: _labelEstado(cita.estado), color: color),
        ],
      ),
    );
  }
}

// ── Widgets auxiliares ────────────────────────────────────────
class _AccesoWidget extends StatelessWidget {
  const _AccesoWidget({required this.acceso});
  final _Acceso acceso;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: acceso.onTap,
      child: Column(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: acceso.color.withAlpha(20),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(acceso.icon, color: acceso.color, size: 26),
          ),
          const SizedBox(height: 6),
          Text(acceso.label,
              style:
                  AppTypography.captionXs(color: AppColors.textDark),
              textAlign: TextAlign.center),
        ],
      ),
    );
  }
}

// ── Data models ───────────────────────────────────────────────
class _Stat {
  const _Stat(this.label, this.value, this.color, this.icon);
  final String label, value;
  final Color  color;
  final IconData icon;
}

class _Acceso {
  const _Acceso(this.label, this.icon, this.color, this.onTap);
  final String       label;
  final IconData     icon;
  final Color        color;
  final VoidCallback onTap;
}
