// ExpedientePacienteScreen — historial clínico con tabs.
// Tabs: Resumen | Odontograma | Recetas | Tratamientos | Fotos
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/constants/app_theme.dart';
import '../../../core/widgets/app_card.dart';
import '../../../core/widgets/status_badge.dart';
import '../../../data/services/expediente_service.dart';
import 'odontogram_screen.dart';

class ExpedientePacienteScreen extends StatefulWidget {
  final int    idPaciente;
  final String nombrePaciente;

  const ExpedientePacienteScreen({
    super.key,
    required this.idPaciente,
    required this.nombrePaciente,
  });

  @override
  State<ExpedientePacienteScreen> createState() =>
      _ExpedientePacienteScreenState();
}

class _ExpedientePacienteScreenState extends State<ExpedientePacienteScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabs;

  // Estado global
  bool              _loadingResumen = true;
  PacienteResumen?  _resumen;

  // Estado por tab
  bool              _loadingRecetas     = true;
  bool              _loadingTratamientos= true;
  bool              _loadingFotos       = true;
  List<Receta>      _recetas      = [];
  List<Tratamiento> _tratamientos = [];
  List<FotoExpediente> _fotos    = [];

  @override
  void initState() {
    super.initState();
    _tabs = TabController(length: 5, vsync: this);
    _tabs.addListener(_onTabChange);
    _cargarResumen();
  }

  @override
  void dispose() {
    _tabs.dispose();
    super.dispose();
  }

  void _onTabChange() {
    if (_tabs.indexIsChanging) return;
    switch (_tabs.index) {
      case 2: _cargarRecetas();      break;
      case 3: _cargarTratamientos(); break;
      case 4: _cargarFotos();        break;
    }
  }

  Future<void> _cargarResumen() async {
    setState(() => _loadingResumen = true);
    final r = await ExpedienteService.fetchResumen(widget.idPaciente);
    if (!mounted) return;
    setState(() { _resumen = r; _loadingResumen = false; });
  }

  Future<void> _cargarRecetas() async {
    final eid = _resumen?.idExpediente;
    if (eid == null) return;
    setState(() => _loadingRecetas = true);
    final r = await ExpedienteService.fetchRecetas(eid);
    if (!mounted) return;
    setState(() { _recetas = r; _loadingRecetas = false; });
  }

  Future<void> _cargarTratamientos() async {
    setState(() => _loadingTratamientos = true);
    final r = await ExpedienteService.fetchTratamientos(widget.idPaciente);
    if (!mounted) return;
    setState(() { _tratamientos = r; _loadingTratamientos = false; });
  }

  Future<void> _cargarFotos() async {
    final eid = _resumen?.idExpediente;
    if (eid == null) return;
    setState(() => _loadingFotos = true);
    final r = await ExpedienteService.fetchFotos(eid);
    if (!mounted) return;
    setState(() { _fotos = r; _loadingFotos = false; });
  }

  // ── UI ───────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.nombrePaciente,
                style: AppTypography.titleSmall(color: Colors.white)),
            if (_resumen?.numExpediente != null)
              Text('Exp. ${_resumen!.numExpediente}',
                  style: AppTypography.caption(color: Colors.white70)),
          ],
        ),
        bottom: TabBar(
          controller: _tabs,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          indicatorColor: AppColors.primaryLight,
          indicatorWeight: 3,
          isScrollable: true,
          labelStyle: AppTypography.labelSmall(color: Colors.white),
          tabs: const [
            Tab(icon: Icon(Icons.person_outline,   size: 18), text: 'Resumen'),
            Tab(icon: Icon(Icons.grid_view_rounded, size: 18), text: 'Odontograma'),
            Tab(icon: Icon(Icons.medication_outlined,size: 18),text: 'Recetas'),
            Tab(icon: Icon(Icons.healing_outlined,  size: 18), text: 'Tratamientos'),
            Tab(icon: Icon(Icons.photo_library_outlined,size:18),text:'Fotos'),
          ],
        ),
      ),
      body: _loadingResumen
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _resumen == null
              ? Center(
                  child: Text('No se pudo cargar el expediente',
                      style: AppTypography.body(color: AppColors.textMuted)))
              : TabBarView(
                  controller: _tabs,
                  children: [
                    _TabResumen(resumen: _resumen!),
                    OdontogramScreen(
                      idExpediente:   _resumen!.idExpediente!,
                      nombrePaciente: widget.nombrePaciente,
                    ),
                    _TabRecetas(
                      recetas:      _recetas,
                      loading:      _loadingRecetas,
                      idExpediente: _resumen!.idExpediente!,
                      onNueva:      () async {
                        await _mostrarFormReceta(context);
                        _cargarRecetas();
                      },
                    ),
                    _TabTratamientos(
                      tratamientos: _tratamientos,
                      loading:      _loadingTratamientos,
                      idPaciente:   widget.idPaciente,
                      onNuevo:      () async {
                        await _mostrarFormTratamiento(context);
                        _cargarTratamientos();
                      },
                    ),
                    _TabFotos(
                      fotos:        _fotos,
                      loading:      _loadingFotos,
                      idExpediente: _resumen!.idExpediente!,
                      onFotoSubida: _cargarFotos,
                    ),
                  ],
                ),
    );
  }

  // ── Bottom sheets ─────────────────────────────────────────────

  Future<void> _mostrarFormReceta(BuildContext context) async {
    final eid = _resumen?.idExpediente;
    if (eid == null) return;
    await showModalBottomSheet(
      context:       context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _FormReceta(idExpediente: eid),
    );
  }

  Future<void> _mostrarFormTratamiento(BuildContext context) async {
    await showModalBottomSheet(
      context:       context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _FormTratamiento(idPaciente: widget.idPaciente),
    );
  }
}

// ══════════════════════════════════════════════════════════════
// TAB RESUMEN
// ══════════════════════════════════════════════════════════════
class _TabResumen extends StatelessWidget {
  const _TabResumen({required this.resumen});
  final PacienteResumen resumen;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Datos personales
          AppCard(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(children: [
                  const Icon(Icons.person, color: AppColors.primary, size: 18),
                  const SizedBox(width: 8),
                  Text('Datos personales',
                      style: AppTypography.label(color: AppColors.primary)),
                ]),
                const Divider(height: 20),
                _infoRow('Nombre', resumen.nombre),
                if (resumen.fechaNacimiento != null)
                  _infoRow('Fecha nac.', resumen.fechaNacimiento!),
                if (resumen.sexo != null) _infoRow('Sexo', resumen.sexo!),
                if (resumen.telefono != null) _infoRow('Teléfono', resumen.telefono!),
                if (resumen.correo != null) _infoRow('Correo', resumen.correo!),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Contadores
          Row(children: [
            _contadorCard('Dientes\nregistrados',
                resumen.resumen['dientes_registrados'] ?? 0, AppColors.primary,
                Icons.grid_view_rounded),
            const SizedBox(width: 8),
            _contadorCard('Recetas',
                resumen.resumen['total_recetas'] ?? 0, AppColors.success,
                Icons.medication_outlined),
            const SizedBox(width: 8),
            _contadorCard('Tratamientos',
                resumen.resumen['total_tratamientos'] ?? 0, AppColors.warning,
                Icons.healing_outlined),
          ]),
          const SizedBox(height: 12),

          // Alergias
          if (resumen.alergias.isNotEmpty) ...[
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    const Icon(Icons.warning_amber, color: AppColors.warning, size: 18),
                    const SizedBox(width: 8),
                    Text('Alergias', style: AppTypography.label(color: AppColors.warning)),
                  ]),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8, runSpacing: 6,
                    children: resumen.alergias
                        .map((a) => StatusBadge(label: a, color: AppColors.warning))
                        .toList(),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],

          // Enfermedades
          if (resumen.enfermedades.isNotEmpty)
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    const Icon(Icons.local_hospital, color: AppColors.error, size: 18),
                    const SizedBox(width: 8),
                    Text('Enfermedades previas',
                        style: AppTypography.label(color: AppColors.error)),
                  ]),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8, runSpacing: 6,
                    children: resumen.enfermedades
                        .map((e) => StatusBadge(label: e, color: AppColors.error))
                        .toList(),
                  ),
                ],
              ),
            ),

          if (resumen.observaciones != null && resumen.observaciones!.isNotEmpty) ...[
            const SizedBox(height: 12),
            AppCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Observaciones',
                      style: AppTypography.label(color: AppColors.textDark)),
                  const SizedBox(height: 8),
                  Text(resumen.observaciones!,
                      style: AppTypography.body(color: AppColors.textMuted)),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _infoRow(String label, String value) => Padding(
        padding: const EdgeInsets.only(bottom: 6),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              width: 100,
              child: Text(label,
                  style: AppTypography.caption(color: AppColors.textMuted)),
            ),
            Expanded(
              child: Text(value,
                  style: AppTypography.bodyMedium(color: AppColors.textDark)),
            ),
          ],
        ),
      );

  Widget _contadorCard(String label, int value, Color color, IconData icon) =>
      Expanded(
        child: AppCard(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 4),
              Text(value.toString(),
                  style: AppTypography.headlineSmall(color: color)),
              Text(label,
                  style: AppTypography.captionXs(color: AppColors.textMuted),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      );
}

// ══════════════════════════════════════════════════════════════
// TAB RECETAS
// ══════════════════════════════════════════════════════════════
class _TabRecetas extends StatelessWidget {
  const _TabRecetas({
    required this.recetas,
    required this.loading,
    required this.idExpediente,
    required this.onNueva,
  });
  final List<Receta> recetas;
  final bool         loading;
  final int          idExpediente;
  final VoidCallback onNueva;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: onNueva,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: Text('Nueva Receta',
            style: AppTypography.buttonSmall(color: Colors.white)),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : recetas.isEmpty
              ? _emptyState('Sin recetas registradas', Icons.medication_outlined)
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                  itemCount: recetas.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) {
                    final r = recetas[i];
                    return AppCard(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.medication, color: AppColors.primary, size: 18),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(r.medicamento,
                                    style: AppTypography.bodyMedium(color: AppColors.textDark)),
                              ),
                              Text(r.fecha,
                                  style: AppTypography.caption(color: AppColors.textMuted)),
                            ],
                          ),
                          const SizedBox(height: 8),
                          _pill('Dosis', r.dosis, AppColors.primary),
                          const SizedBox(height: 4),
                          _pill('Frecuencia', r.frecuencia, AppColors.info),
                          const SizedBox(height: 4),
                          _pill('Duración', r.duracion, AppColors.success),
                          if (r.notas != null && r.notas!.isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Text(r.notas!,
                                style: AppTypography.caption(color: AppColors.textMuted)),
                          ],
                        ],
                      ),
                    );
                  },
                ),
    );
  }

  Widget _pill(String label, String value, Color color) => Row(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: color.withAlpha(25),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text('$label: ',
                style: AppTypography.captionXs(color: color)
                    .copyWith(fontWeight: FontWeight.bold)),
          ),
          const SizedBox(width: 6),
          Text(value, style: AppTypography.caption(color: AppColors.textDark)),
        ],
      );
}

// ══════════════════════════════════════════════════════════════
// TAB TRATAMIENTOS
// ══════════════════════════════════════════════════════════════
class _TabTratamientos extends StatelessWidget {
  const _TabTratamientos({
    required this.tratamientos,
    required this.loading,
    required this.idPaciente,
    required this.onNuevo,
  });
  final List<Tratamiento> tratamientos;
  final bool              loading;
  final int               idPaciente;
  final VoidCallback      onNuevo;

  Color _estadoColor(String e) => switch (e) {
    'completado' => AppColors.success,
    'suspendido' => AppColors.warning,
    'cancelado'  => AppColors.error,
    _            => AppColors.info,
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: onNuevo,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: Text('Nuevo Tratamiento',
            style: AppTypography.buttonSmall(color: Colors.white)),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : tratamientos.isEmpty
              ? _emptyState('Sin tratamientos registrados', Icons.healing_outlined)
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                  itemCount: tratamientos.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) {
                    final t = tratamientos[i];
                    final color = _estadoColor(t.estado);
                    return AppCard(
                      padding: const EdgeInsets.all(14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(t.tratamiento,
                                    style: AppTypography.bodyMedium(
                                        color: AppColors.textDark)),
                              ),
                              StatusBadge(
                                  label: t.estado.replaceAll('_', ' '),
                                  color: color),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Row(children: [
                            const Icon(Icons.calendar_today,
                                size: 13, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Text(t.fechaInicio,
                                style: AppTypography.caption(
                                    color: AppColors.textMuted)),
                            if (t.fechaFin != null) ...[
                              Text(' → ${t.fechaFin}',
                                  style: AppTypography.caption(
                                      color: AppColors.textMuted)),
                            ],
                            const Spacer(),
                            Text('L ${t.costo.toStringAsFixed(2)}',
                                style: AppTypography.bodyMedium(
                                    color: AppColors.primary)),
                          ]),
                          if (t.notas != null && t.notas!.isNotEmpty) ...[
                            const SizedBox(height: 6),
                            Text(t.notas!,
                                style: AppTypography.caption(
                                    color: AppColors.textMuted)),
                          ],
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}

// ══════════════════════════════════════════════════════════════
// TAB FOTOS
// ══════════════════════════════════════════════════════════════
class _TabFotos extends StatefulWidget {
  const _TabFotos({
    required this.fotos,
    required this.loading,
    required this.idExpediente,
    required this.onFotoSubida,
  });
  final List<FotoExpediente> fotos;
  final bool                 loading;
  final int                  idExpediente;
  final VoidCallback         onFotoSubida;

  @override
  State<_TabFotos> createState() => _TabFotosState();
}

class _TabFotosState extends State<_TabFotos> {
  bool _subiendo = false;
  final _picker  = ImagePicker();

  // Muestra opciones cámara / galería
  void _mostrarOpciones() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            Container(
              width: 40, height: 4,
              decoration: BoxDecoration(
                color: Colors.grey[300],
                borderRadius: BorderRadius.circular(2)),
            ),
            const SizedBox(height: 12),
            ListTile(
              leading: const Icon(Icons.camera_alt, color: AppColors.primary),
              title: const Text('Tomar foto'),
              onTap: () { Navigator.pop(context); _seleccionar(ImageSource.camera); },
            ),
            ListTile(
              leading: const Icon(Icons.photo_library, color: AppColors.primary),
              title: const Text('Elegir de galería'),
              onTap: () { Navigator.pop(context); _seleccionar(ImageSource.gallery); },
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  Future<void> _seleccionar(ImageSource source) async {
    final XFile? file = await _picker.pickImage(
      source: source,
      imageQuality: 80,
      maxWidth: 1920,
    );
    if (file == null || !mounted) return;
    await _subir(file);
  }

  Future<void> _subir(XFile file) async {
    // Pedir descripción opcional
    String descripcion = '';
    final ctrl = TextEditingController();
    if (mounted) {
      await showDialog(
        context: context,
        builder: (_) => AlertDialog(
          title: const Text('Descripción (opcional)'),
          content: TextField(
            controller: ctrl,
            autofocus: true,
            decoration: const InputDecoration(
              hintText: 'Ej: Antes del tratamiento, radiografía…'),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Omitir')),
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
              onPressed: () { descripcion = ctrl.text.trim(); Navigator.pop(context); },
              child: const Text('OK', style: TextStyle(color: Colors.white))),
          ],
        ),
      );
    }
    if (!mounted) return;

    setState(() => _subiendo = true);
    final ok = await ExpedienteService.subirFoto(
        widget.idExpediente, file, descripcion);
    if (!mounted) return;
    setState(() => _subiendo = false);

    if (ok) {
      widget.onFotoSubida(); // refresca la lista en el parent
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Error al subir la foto. Verifica el servidor.'),
          backgroundColor: Colors.red));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _subiendo ? null : _mostrarOpciones,
        backgroundColor: AppColors.primary,
        icon: _subiendo
            ? const SizedBox(
                width: 20, height: 20,
                child: CircularProgressIndicator(
                    color: Colors.white, strokeWidth: 2.5))
            : const Icon(Icons.add_a_photo, color: Colors.white),
        label: Text(
          _subiendo ? 'Subiendo…' : 'Agregar foto',
          style: AppTypography.buttonSmall(color: Colors.white)),
      ),
      body: widget.loading
          ? const Center(
              child: CircularProgressIndicator(color: AppColors.primary))
          : widget.fotos.isEmpty
              ? _emptyState(
                  'Sin fotos — toca el botón para agregar',
                  Icons.add_a_photo_outlined)
              : GridView.builder(
                  padding: const EdgeInsets.fromLTRB(12, 12, 12, 100),
                  gridDelegate:
                      const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 8,
                    mainAxisSpacing: 8,
                  ),
                  itemCount: widget.fotos.length,
                  itemBuilder: (_, i) {
                    final f = widget.fotos[i];
                    return ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          Image.network(
                            'http://localhost${f.url}',
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => Container(
                              color: AppColors.primaryLight,
                              child: const Icon(Icons.broken_image,
                                  color: AppColors.primary)),
                          ),
                          if (f.descripcion != null)
                            Positioned(
                              bottom: 0, left: 0, right: 0,
                              child: Container(
                                padding: const EdgeInsets.all(6),
                                color: Colors.black54,
                                child: Text(f.descripcion!,
                                    style: const TextStyle(
                                        color: Colors.white, fontSize: 11),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis),
                              ),
                            ),
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}

// ── Helpers compartidos ───────────────────────────────────────
Widget _emptyState(String msg, IconData icon) => Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 52, color: AppColors.primaryLight),
          const SizedBox(height: 12),
          Text(msg, style: AppTypography.body(color: AppColors.textMuted)),
        ],
      ),
    );

// ══════════════════════════════════════════════════════════════
// FORM RECETA (Bottom Sheet)
// ══════════════════════════════════════════════════════════════
class _FormReceta extends StatefulWidget {
  const _FormReceta({required this.idExpediente});
  final int idExpediente;

  @override
  State<_FormReceta> createState() => _FormRecetaState();
}

class _FormRecetaState extends State<_FormReceta> {
  final _form     = GlobalKey<FormState>();
  bool  _loading  = false;

  final _medCtrl  = TextEditingController();
  final _dosCtrl  = TextEditingController();
  final _frecCtrl = TextEditingController();
  final _durCtrl  = TextEditingController();
  final _notCtrl  = TextEditingController();

  @override
  void dispose() {
    _medCtrl.dispose(); _dosCtrl.dispose();
    _frecCtrl.dispose(); _durCtrl.dispose(); _notCtrl.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _loading = true);
    final ok = await ExpedienteService.crearReceta({
      'id_expediente': widget.idExpediente,
      'medicamento':   _medCtrl.text.trim(),
      'dosis':         _dosCtrl.text.trim(),
      'frecuencia':    _frecCtrl.text.trim(),
      'duracion':      _durCtrl.text.trim(),
      'notas':         _notCtrl.text.trim(),
    });
    if (!mounted) return;
    if (ok) Navigator.pop(context);
    else {
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error al guardar la receta')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 20, right: 20, top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      child: Form(
        key: _form,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.textMuted,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text('Nueva Receta',
                  style: AppTypography.titleSmall(color: AppColors.primary)),
              const SizedBox(height: 16),
              _campo('Medicamento *', _medCtrl),
              const SizedBox(height: 10),
              _campo('Dosis (ej: 500mg) *', _dosCtrl),
              const SizedBox(height: 10),
              _campo('Frecuencia (ej: cada 8 horas) *', _frecCtrl),
              const SizedBox(height: 10),
              _campo('Duración (ej: 7 días) *', _durCtrl),
              const SizedBox(height: 10),
              _campo('Notas adicionales (opcional)', _notCtrl, required: false),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity, height: 48,
                child: ElevatedButton(
                  onPressed: _loading ? null : _guardar,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: _loading
                      ? const SizedBox(width: 22, height: 22,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                      : Text('Guardar Receta',
                          style: AppTypography.button(color: Colors.white)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _campo(String hint, TextEditingController ctrl, {bool required = true}) =>
      TextFormField(
        controller: ctrl,
        enabled: !_loading,
        style: AppTypography.body(color: AppColors.textDark),
        decoration: InputDecoration(
          hintText: hint,
          filled: true, fillColor: AppColors.background,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
        ),
        validator: required
            ? (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null
            : null,
      );
}

// ══════════════════════════════════════════════════════════════
// FORM TRATAMIENTO (Bottom Sheet) — catálogo cargado desde API
// ══════════════════════════════════════════════════════════════
class _FormTratamiento extends StatefulWidget {
  const _FormTratamiento({required this.idPaciente});
  final int idPaciente;

  @override
  State<_FormTratamiento> createState() => _FormTratamientoState();
}

class _FormTratamientoState extends State<_FormTratamiento> {
  final _form      = GlobalKey<FormState>();
  bool  _loading   = false;
  bool  _loadingCat = true;
  int?  _idTrat;
  final _descCtrl  = TextEditingController();
  final _costoCtrl = TextEditingController();

  List<Map<String, dynamic>> _catalogo = [];

  @override
  void initState() {
    super.initState();
    _cargarCatalogo();
  }

  Future<void> _cargarCatalogo() async {
    final cat = await ExpedienteService.fetchCatalogTratamientos();
    if (!mounted) return;
    setState(() { _catalogo = cat; _loadingCat = false; });
  }

  @override
  void dispose() {
    _descCtrl.dispose();
    _costoCtrl.dispose();
    super.dispose();
  }

  Future<void> _guardar() async {
    if (!_form.currentState!.validate() || _idTrat == null) return;
    setState(() => _loading = true);
    final ok = await ExpedienteService.crearTratamiento({
      'id_paciente':    widget.idPaciente,
      'id_tratamiento': _idTrat,
      'descripcion':    _descCtrl.text.trim(),
      'fecha_inicio':   DateTime.now().toIso8601String().substring(0, 10),
      'costo':          double.tryParse(_costoCtrl.text) ?? 0,
    });
    if (!mounted) return;
    if (ok) {
      Navigator.pop(context);
    } else {
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error al guardar el tratamiento')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        left: 20, right: 20, top: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      child: Form(
        key: _form,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.textMuted,
                    borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 16),
              Text('Registrar Tratamiento',
                  style: AppTypography.titleSmall(color: AppColors.primary)),
              const SizedBox(height: 16),

              // Selector de tipo de tratamiento
              _loadingCat
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.symmetric(vertical: 16),
                        child: CircularProgressIndicator(
                            color: AppColors.primary, strokeWidth: 2),
                      ))
                  : DropdownButtonFormField<int>(
                      value: _idTrat,
                      isExpanded: true,
                      decoration: InputDecoration(
                        hintText: 'Selecciona tratamiento *',
                        filled: true, fillColor: AppColors.background,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide.none),
                        contentPadding: const EdgeInsets.symmetric(
                            vertical: 12, horizontal: 14),
                      ),
                      items: _catalogo.map((t) => DropdownMenuItem<int>(
                        value: (t['id_tratamiento'] as num).toInt(),
                        child: Text(
                          t['descripcion'] as String,
                          overflow: TextOverflow.ellipsis,
                        ),
                      )).toList(),
                      onChanged: (v) {
                        setState(() {
                          _idTrat = v;
                          // Prellenar costo si viene de la API
                          final item = _catalogo.firstWhere(
                            (t) => (t['id_tratamiento'] as num).toInt() == v,
                            orElse: () => {},
                          );
                          final precio = (item['precio_base'] as num?)?.toDouble() ?? 0;
                          if (precio > 0) {
                            _costoCtrl.text = precio.toStringAsFixed(2);
                          }
                        });
                      },
                      validator: (v) =>
                          v == null ? 'Selecciona un tratamiento' : null,
                    ),
              const SizedBox(height: 10),

              TextFormField(
                controller: _costoCtrl,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                enabled: !_loading,
                style: AppTypography.body(color: AppColors.textDark),
                decoration: InputDecoration(
                  hintText: 'Costo (L) *',
                  prefixText: 'L ',
                  filled: true, fillColor: AppColors.background,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(
                      vertical: 12, horizontal: 14),
                ),
                validator: (v) =>
                    (v == null || v.isEmpty) ? 'Requerido' : null,
              ),
              const SizedBox(height: 10),

              TextFormField(
                controller: _descCtrl,
                enabled: !_loading,
                maxLines: 2,
                style: AppTypography.body(color: AppColors.textDark),
                decoration: InputDecoration(
                  hintText: 'Observaciones (opcional)',
                  filled: true, fillColor: AppColors.background,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
                  contentPadding: const EdgeInsets.symmetric(
                      vertical: 12, horizontal: 14),
                ),
              ),
              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity, height: 48,
                child: ElevatedButton(
                  onPressed: (_loading || _loadingCat) ? null : _guardar,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  child: _loading
                      ? const SizedBox(
                          width: 22, height: 22,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2.5))
                      : Text('Registrar',
                          style: AppTypography.button(color: Colors.white)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
