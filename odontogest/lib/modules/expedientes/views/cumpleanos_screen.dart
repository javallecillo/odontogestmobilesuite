import 'package:flutter/material.dart';

import '../../../core/constants/app_theme.dart';
import '../../../data/services/cumpleanos_service.dart';

class CumpleanosScreen extends StatefulWidget {
  const CumpleanosScreen({super.key});

  @override
  State<CumpleanosScreen> createState() => _CumpleanosScreenState();
}

class _CumpleanosScreenState extends State<CumpleanosScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nombreCtrl = TextEditingController();
  final _fechaCtrl = TextEditingController();
  final _telefonoCtrl = TextEditingController();

  bool _loading = true;
  int? _selectedId;
  final List<CumpleanoRegistro> _registros = [];

  @override
  void initState() {
    super.initState();
    _cargarRegistros();
  }

  @override
  void dispose() {
    _nombreCtrl.dispose();
    _fechaCtrl.dispose();
    _telefonoCtrl.dispose();
    super.dispose();
  }

  Future<void> _cargarRegistros() async {
    setState(() => _loading = true);
    final registros = await CumpleanosService.listar();
    if (!mounted) return;
    setState(() {
      _registros
        ..clear()
        ..addAll(registros);
      _loading = false;
    });
  }

  void _limpiar() {
    _formKey.currentState?.reset();
    _nombreCtrl.clear();
    _fechaCtrl.clear();
    _telefonoCtrl.clear();
    setState(() => _selectedId = null);
  }

  Future<void> _seleccionarFecha() async {
    final now = DateTime.now();
    final initial = _parseDate(_fechaCtrl.text) ?? DateTime(now.year, now.month, now.day);
    final picked = await showDatePicker(
      context: context,
      initialDate: initial,
      firstDate: DateTime(1900),
      lastDate: DateTime(now.year + 10),
      helpText: 'Seleccione la fecha de cumpleaños',
    );
    if (picked == null) return;

    setState(() {
      _fechaCtrl.text = _formatDisplayDate(picked);
    });
  }

  Future<void> _guardar() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    final creado = await CumpleanosService.guardar(
      nombre: _nombreCtrl.text.trim(),
      fechaNacimiento: _fechaParaApi(_fechaCtrl.text.trim()),
      telefono: _telefonoCtrl.text.trim(),
    );

    if (!mounted) return;
    if (creado == null) {
      _mostrarMensaje('No se pudo guardar el registro.');
      return;
    }

    await _cargarRegistros();
    _limpiar();
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Registro guardado.')),
    );
  }

  Future<void> _modificar() async {
    final id = _selectedId;
    if (id == null) {
      _mostrarMensaje('Seleccione un registro para modificarlo.');
      return;
    }
    if (!(_formKey.currentState?.validate() ?? false)) return;

    final ok = await CumpleanosService.actualizar(
      idPaciente: id,
      nombre: _nombreCtrl.text.trim(),
      fechaNacimiento: _fechaParaApi(_fechaCtrl.text.trim()),
      telefono: _telefonoCtrl.text.trim(),
    );

    if (!mounted) return;
    if (!ok) {
      _mostrarMensaje('No se pudo actualizar el registro.');
      return;
    }

    await _cargarRegistros();
    _cargarRegistroPorId(id);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Registro actualizado.')),
    );
  }

  Future<void> _eliminar() async {
    final id = _selectedId;
    if (id == null) {
      _mostrarMensaje('Seleccione un registro para eliminarlo.');
      return;
    }

    final ok = await CumpleanosService.eliminar(id);
    if (!mounted) return;
    if (!ok) {
      _mostrarMensaje('No se pudo eliminar el registro.');
      return;
    }

    await _cargarRegistros();
    _limpiar();
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Registro eliminado.')),
    );
  }

  void _cargarRegistro(CumpleanoRegistro registro) {
    setState(() {
      _selectedId = registro.idPaciente;
      _nombreCtrl.text = registro.nombre;
      _fechaCtrl.text = _fechaParaPantalla(registro.fechaNacimiento);
      _telefonoCtrl.text = registro.telefono;
    });
  }

  void _cargarRegistroPorId(int id) {
    final index = _registros.indexWhere((registro) => registro.idPaciente == id);
    if (index >= 0) {
      _cargarRegistro(_registros[index]);
      return;
    }
    _limpiar();
  }

  void _mostrarMensaje(String mensaje) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(mensaje)),
    );
  }

  DateTime? _parseDate(String value) {
    final parts = value.split('/');
    if (parts.length != 3) return null;
    final day = int.tryParse(parts[0]);
    final month = int.tryParse(parts[1]);
    final year = int.tryParse(parts[2]);
    if (day == null || month == null || year == null) return null;
    return DateTime(year, month, day);
  }

  String _formatDisplayDate(DateTime date) {
    final day = date.day.toString().padLeft(2, '0');
    final month = date.month.toString().padLeft(2, '0');
    return '$day/$month/${date.year}';
  }

  String _fechaParaPantalla(String fechaApi) {
    if (fechaApi.isEmpty) return '';
    final parsed = DateTime.tryParse(fechaApi);
    if (parsed == null) return fechaApi;
    return _formatDisplayDate(parsed);
  }

  String _fechaParaApi(String fechaPantalla) {
    final parsed = _parseDate(fechaPantalla);
    if (parsed == null) return fechaPantalla;
    final month = parsed.month.toString().padLeft(2, '0');
    final day = parsed.day.toString().padLeft(2, '0');
    return '${parsed.year}-$month-$day';
  }

  Widget _campoTexto({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
    bool readOnly = false,
    VoidCallback? onTap,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      readOnly: readOnly,
      onTap: onTap,
      validator: validator,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16)),
        filled: true,
        fillColor: AppColors.surface,
      ),
    );
  }

  Widget _accion({
    required String text,
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
  }) {
    return SizedBox(
      height: 48,
      child: FilledButton.icon(
        onPressed: onPressed,
        style: FilledButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
        icon: Icon(icon, size: 18),
        label: Text(text),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        title: Text(
          'CUMPLEAÑOS',
          style: AppTypography.titleSmall(color: Colors.white),
        ),
        centerTitle: false,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 5),
                const Text(
                  'Datos',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppColors.primary,
                  ),
                ),
                const SizedBox(height: 5),
                _campoTexto(
                  controller: _nombreCtrl,
                  label: 'Nombre',
                  icon: Icons.person_outline,
                  validator: (value) => (value == null || value.trim().isEmpty)
                      ? 'Ingrese el nombre'
                      : null,
                ),
                const SizedBox(height: 12),
                _campoTexto(
                  controller: _fechaCtrl,
                  label: 'Fecha de cumpleaños',
                  icon: Icons.cake_outlined,
                  readOnly: true,
                  onTap: _seleccionarFecha,
                  validator: (value) => (value == null || value.trim().isEmpty)
                      ? 'Seleccione la fecha de cumpleaños'
                      : null,
                ),
                const SizedBox(height: 12),
                _campoTexto(
                  controller: _telefonoCtrl,
                  label: 'Teléfono',
                  icon: Icons.phone_outlined,
                  keyboardType: TextInputType.phone,
                  validator: (value) => (value == null || value.trim().isEmpty)
                      ? 'Ingrese el teléfono'
                      : null,
                ),
                const SizedBox(height: 16),
                Wrap(
                  spacing: 10,
                  runSpacing: 10,
                  children: [
                    _accion(
                      text: 'Guardar',
                      icon: Icons.save_outlined,
                      color: AppColors.success,
                      onPressed: _guardar,
                    ),
                    _accion(
                      text: 'Modificar',
                      icon: Icons.edit_outlined,
                      color: AppColors.primary,
                      onPressed: _modificar,
                    ),
                    _accion(
                      text: 'Eliminar',
                      icon: Icons.delete_outline,
                      color: AppColors.error,
                      onPressed: _eliminar,
                    ),
                    _accion(
                      text: 'Limpiar',
                      icon: Icons.cleaning_services_outlined,
                      color: AppColors.textMuted,
                      onPressed: _limpiar,
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.primary,
                      side: const BorderSide(color: AppColors.primary),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    icon: const Icon(Icons.arrow_back),
                    label: const Text('Regresar al dashboard'),
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Registros capturados',
                      style: AppTypography.titleSmall(
                        color: AppColors.textDark,
                      ),
                    ),
                    Text(
                      '${_registros.length} total',
                      style: AppTypography.caption(color: AppColors.textMuted),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                if (_loading)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 20),
                    child: Center(
                      child: CircularProgressIndicator(
                        color: AppColors.primary,
                      ),
                    ),
                  )
                else if (_registros.isEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Text(
                      'Todavía no hay registros guardados.',
                      style: AppTypography.body(color: AppColors.textMuted),
                    ),
                  )
                else
                  ListView.separated(
                    itemCount: _registros.length,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (_, index) {
                      final registro = _registros[index];
                      final seleccionado = registro.idPaciente == _selectedId;
                      return InkWell(
                        onTap: () => _cargarRegistro(registro),
                        borderRadius: BorderRadius.circular(16),
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: seleccionado
                                ? AppColors.primaryLight.withAlpha(180)
                                : AppColors.surface,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: seleccionado
                                  ? AppColors.primary
                                  : AppColors.border,
                              width: seleccionado ? 1.5 : 1,
                            ),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 22,
                                backgroundColor: AppColors.primary,
                                child: Text(
                                  registro.nombre.isNotEmpty
                                      ? registro.nombre[0].toUpperCase()
                                      : 'R',
                                  style: AppTypography.titleSmall(
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      registro.nombre,
                                      style: AppTypography.bodyMedium(
                                        color: AppColors.textDark,
                                      ),
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      'Cumpleaños: ${_fechaParaPantalla(registro.fechaNacimiento)}',
                                      style: AppTypography.caption(
                                        color: AppColors.textMuted,
                                      ),
                                    ),
                                    Text(
                                      'Teléfono: ${registro.telefono}',
                                      style: AppTypography.caption(
                                        color: AppColors.textMuted,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const Icon(
                                Icons.chevron_right,
                                color: AppColors.textMuted,
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}