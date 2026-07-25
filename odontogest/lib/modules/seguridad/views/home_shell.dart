// HomeShell — contenedor principal post-login.
// Gestiona el BottomNavigationBar y el índice de pantalla activa.
// El rol del usuario (pasado como parámetro) determina qué tabs se muestran.
import 'package:flutter/material.dart';
import '../../agenda/views/agenda_screen.dart';
import '../../expedientes/views/pacientes_screen.dart';
import '../../facturacion/views/facturacion_screen.dart';
import '../../inventario/views/inventario_screen.dart';
import '../../../core/constants/app_theme.dart';
import 'dashboard_screen.dart';
// perfil_screen.dart se navega por push desde el avatar del dashboard, no como tab

class HomeShell extends StatefulWidget {
  const HomeShell({super.key, this.rol = 'Odontologo'});

  /// Rol recibido del token de autenticación.
  /// Valores posibles: 'Administrador', 'Recepcionista', 'Odontologo', 'Asistente'
  final String rol;

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _currentIndex = 0;

  // Lista de pantallas según el rol del odontólogo (acceso completo)
  // El Admin/Recepcionista normalmente opera desde el panel web
  late final List<_TabItem> _tabs;

  @override
  void initState() {
    super.initState();
    _tabs = _buildTabs();
  }

  List<_TabItem> _buildTabs() {
    // Base: todas las pantallas disponibles en móvil
    final allTabs = [
      _TabItem(
        label: 'Inicio',
        icon: Icons.home_outlined,
        activeIcon: Icons.home,
        screen: const DashboardScreen(),
      ),
      _TabItem(
        label: 'Agenda',
        icon: Icons.calendar_month_outlined,
        activeIcon: Icons.calendar_month,
        screen: const AgendaScreen(),
      ),
      _TabItem(
        label: 'Pacientes',
        icon: Icons.people_outline,
        activeIcon: Icons.people,
        screen: const PacientesScreen(),
      ),
      _TabItem(
        label: 'Facturación',
        icon: Icons.receipt_long_outlined,
        activeIcon: Icons.receipt_long,
        screen: const FacturacionScreen(),
      ),
      _TabItem(
        label: 'Inventario',
        icon: Icons.inventory_2_outlined,
        activeIcon: Icons.inventory_2,
        screen: const InventarioScreen(),
      ),
    ];
    return allTabs;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        // IndexedStack mantiene el estado de cada pantalla al cambiar de tab
        index: _currentIndex,
        children: _tabs.map((t) => t.screen).toList(),
      ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  Widget _buildBottomNav() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withAlpha(20),
            blurRadius: 12,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) => setState(() => _currentIndex = index),
          type: BottomNavigationBarType.fixed,
          backgroundColor: AppColors.surface,
          selectedItemColor: AppColors.primary,
          unselectedItemColor: AppColors.textMuted,
          selectedFontSize: 11,
          unselectedFontSize: 10,
          elevation: 0,
          items: _tabs.map((tab) {
            return BottomNavigationBarItem(
              icon: Icon(tab.icon),
              activeIcon: Icon(tab.activeIcon),
              label: tab.label,
            );
          }).toList(),
        ),
      ),
    );
  }
}

class _TabItem {
  const _TabItem({
    required this.label,
    required this.icon,
    required this.activeIcon,
    required this.screen,
  });
  final String label;
  final IconData icon;
  final IconData activeIcon;
  final Widget screen;
}
