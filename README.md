# OdontoGest 

**OdontoGest** es un sistema integral de gestión para clínicas dentales, compuesto por tres componentes que trabajan de forma coordinada:

- **App móvil/multiplataforma (Flutter)** — para uso diario del personal clínico (odontólogos, asistentes) en el consultorio.
- **Panel administrativo web (PHP)** — para la gestión completa del negocio: usuarios, roles, facturación, inventario, reportes y auditoría.
- **API REST (PHP)** — backend que conecta la app móvil con la base de datos y expone los servicios usados por ambos frontends.

Desarrollado para **Clínica Dental OrtoNova** (Honduras), pero diseñado de forma modular para adaptarse a cualquier consultorio u organización odontológica.

---

## Licencia

Este proyecto fue desarrollado por: **Jorge Arturo Vallecillo Espinoza** y **Lucas Rodrigo Bautista Juarez** cursantes de la carrera de Ingenieria en Computacion de la **Universidad Tecnologica de Honduras (UTH)** como una solución de gestión clínica a medida como proyecto final en la clase Movil II. Consulta con el equipo propietario del repositorio antes de reutilizar, distribuir o desplegar el código en otros entornos.
 
---

## Tabla de contenidos

- [¿Qué resuelve este sistema?](#-qué-resuelve-este-sistema)
- [Arquitectura del proyecto](#-arquitectura-del-proyecto)
- [Módulos funcionales](#-módulos-funcionales)
- [Roles y permisos](#-roles-y-permisos)
- [Estructura del repositorio](#-estructura-del-repositorio)
- [Tecnologías utilizadas](#-tecnologías-utilizadas)
- [Requisitos previos](#-requisitos-previos)
- [Instalación y puesta en marcha](#-instalación-y-puesta-en-marcha)
- [Seguridad](#-seguridad)
- [Licencia](#-licencia)

---

## ¿Qué resuelve este sistema?

Una clínica dental necesita coordinar información dispersa: citas, historiales clínicos, cobros, inventario de insumos y personal. OdontoGest centraliza toda esa operación en una sola plataforma, permitiendo que:

- **Recepción** agende citas, registre pacientes y emita facturas.
- **Odontólogos y asistentes** consulten su agenda diaria, actualicen el expediente clínico y el odontograma de cada paciente desde su celular, incluso en consultorio.
- **Administración** controle usuarios, permisos, inventario, reportes financieros/operativos y el historial de auditoría del sistema.

---

## Arquitectura del proyecto

```
┌─────────────────────┐         ┌──────────────────────┐
│    App Flutter       │        │  Panel Web (PHP MVC)  │
│   (Android/Web)      │        │  odontogest_web/      │
│                      │        │                       │
└──────────┬───────────┘        └───────────┬───────────┘
           │  HTTP (Bearer Token)            │  Sesión PHP
           ▼                                 ▼
   ┌───────────────────┐            ┌────────────────────┐
   │  API REST (PHP)     │◄───────┤  Conexión directa    │
   │  odontogest_api/     │         │  a la misma BD     │
   └──────────┬───────────┘         └────────────────────┘
              │
              ▼
     ┌──────────────────┐
     │   MySQL / MariaDB │
     │   (odonto_gest)   │
     └──────────────────┘
```

- La **app móvil** consume exclusivamente la **API REST**, autenticándose con un token tipo *Bearer* que codifica el usuario, su rol y un timestamp de expiración (24 h).
- El **panel web** trabaja directamente contra la base de datos mediante un patrón **MVC** propio (Controllers / Models / Views), con sesiones PHP y protección CSRF.
- Ambos componentes comparten la misma base de datos, garantizando información consistente en tiempo real entre el consultorio (móvil) y la administración (web).

---

## Módulos funcionales

### 1. Autenticación y seguridad
- Login con usuario/contraseña (hash *bcrypt*).
- Bloqueo temporal tras 5 intentos fallidos (rate limiting de login).
- Tokens Bearer con expiración de 24 horas para la API.
- Protección CSRF en todos los formularios del panel web.
- Límite de peticiones por IP (*rate limiting*) en endpoints sensibles de la API.

### 2. Agenda y citas
- Calendario de citas por odontólogo, con estados: `pendiente`, `confirmada`, `en_curso`, `atendida`, `cancelada`, `no_asistio`.
- Verificación automática de disponibilidad de horario por odontólogo (evita citas duplicadas).
- Consulta de horarios/slots disponibles por fecha.
- Notificaciones automáticas de citas del día y del día siguiente.

### 3. Pacientes y expedientes clínicos
- Ficha completa del paciente: datos personales, contacto de emergencia, responsable de pago, ocupación, estado civil.
- **Expediente clínico** con:
  - Tipo de sangre, antecedentes y observaciones generales.
  - Catálogo de **alergias**, **enfermedades sistémicas** y **medicamentos actuales**.
  - **Odontograma interactivo** con notación FDI (32 piezas dentales), soporte para múltiples condiciones por diente (caries, corona, extracción, implante, bracket, etc.).
  - **Recetas médicas** y **historial de tratamientos** con costos y estado (en proceso, completado, suspendido).
  - Galería de **fotos clínicas** por paciente (radiografías, evolución de tratamientos).
  - Historial completo de citas anteriores.

### 4. Facturación
- Emisión de facturas con desglose de subtotal, ISV (impuesto hondureño) y total.
- Tasas de ISV configurables (0%, 15% servicios, 18% bienes).
- Métodos de pago: efectivo, tarjeta, transferencia.
- Estados de factura: `emitida`, `pagada`, `anulada` (con motivo de anulación).
- Datos fiscales de sucursal (RTN, CAI) conforme a normativa del SAR de Honduras.

### 5. Inventario
- Control de stock de insumos y productos con **stock mínimo configurable**.
- Alertas automáticas de **stock bajo** y **productos agotados**.
- Ajustes de stock (entrada, salida, ajuste manual) con motivo registrado.
- Gestión de proveedores.
- Cálculo de valor total del inventario.

### 6. Gestión de usuarios, roles y odontólogos
- CRUD completo de usuarios del sistema con roles: `Administrador`, `Odontólogo`, `Recepcionista`, `Asistente`.
- Sistema de **permisos por módulo** asignables a cada rol.
- Ficha de odontólogos con número de licencia, especialidad y cargo.
- Activación/desactivación de cuentas y reseteo de contraseñas por un administrador.

### 7. Auditoría del sistema
- Registro automático de acciones críticas (crear, editar, eliminar, login, logout, anular) por módulo.
- Filtros por usuario, módulo, acción, IP y rango de fechas.
- Exportación del log de auditoría a **CSV**.

### 8. Reportes
- Reporte de citas por rango de fechas (efectividad, atendidas vs. canceladas).
- Reporte de ingresos (facturación, ISV recaudado).
- Reporte de estado de inventario.

### 9. Dashboard y notificaciones
- Panel con KPIs en tiempo real: citas del día, pacientes activos, facturas pendientes, alertas de stock.
- Resumen visual de citas por estado.
- Centro de notificaciones con contador de no leídas, tanto en la app móvil como en el panel web.

### 10. Experiencia de usuario
- Modo claro / oscuro persistente en el panel web.
- Interfaz responsiva con sidebar colapsable.
- App móvil con navegación por pestañas (Inicio, Agenda, Pacientes, Facturación, Inventario).

---

## Roles y permisos

| Rol | Alcance típico |
|---|---|
| **Administrador** | Acceso total: usuarios, roles, configuración, auditoría, reportes y todos los módulos operativos. |
| **Odontólogo** | Agenda propia, expedientes clínicos, odontograma, recetas y tratamientos de sus pacientes. |
| **Recepcionista** | Agenda general, pacientes, facturación. |
| **Asistente** | Apoyo operativo con permisos configurables según necesidad de la clínica. |

Los permisos son asignables de forma granular por módulo desde el panel web (**Roles → Permisos**).

---

## 📁 Estructura del repositorio

```
odontogest/            → Aplicación Flutter (móvil y web)
├── lib/
│   ├── core/           → Tema, constantes, sesión, widgets compartidos
│   ├── data/services/   → Consumo de la API REST (HTTP)
│   └── modules/         → Pantallas por módulo (agenda, pacientes, facturación,
│                          inventario, expedientes, seguridad/dashboard)
├── android/ ios/ windows/ macos/ linux/ web/  → Configuración nativa por plataforma

odontogest_api/        → API REST (PHP puro)
├── auth/               → Login
├── agenda/              → Citas y horarios
├── pacientes/           → CRUD de pacientes
├── expediente/          → Odontograma, recetas, tratamientos, fotos
├── facturacion/          → Facturas
├── inventario/           → Productos
├── notificaciones/        → Notificaciones push internas
├── usuarios/              → Gestión de usuarios y perfil
└── core/                   → DB, autenticación por token, rate limiting, respuestas

odontogest_web/        → Panel administrativo (PHP MVC)
├── Controllers/          → Lógica de cada módulo
├── Models/                → Acceso a datos (PDO)
├── Views/                  → Vistas PHP + Bootstrap 5
├── Config/                  → Enrutador, autoload, sesión, CSRF
└── Content/Dist/              → CSS/JS del panel (incluye modo oscuro)
```

---

## Tecnologías utilizadas

**Frontend móvil**
- Flutter / Dart
- `http` (consumo de API), `image_picker` (fotos clínicas), `google_fonts`

**Backend / Panel web**
- PHP (MVC propio, sin frameworks externos)
- PDO con consultas preparadas (prevención de SQL Injection)
- MySQL / MariaDB (administrada con **HeidiSQL**)
- Bootstrap 5, SweetAlert2, Font Awesome 6 (interfaz del panel)

**Infraestructura**
- Autenticación por sesión (panel web) y por token Bearer (API)
- Entorno de desarrollo local con **Laragon** (Apache + PHP + MySQL/MariaDB)
- Base de datos remota MySQL

---

## Requisitos previos

- PHP 8.x con extensión `pdo_mysql`
- Servidor MySQL / MariaDB
- **[Laragon](https://laragon.org/)** (Apache + PHP + MySQL/MariaDB) — entorno recomendado para desarrollo local, con `mod_rewrite` habilitado
- **[HeidiSQL](https://www.heidisql.com/)** — cliente recomendado para administrar la base de datos
- Flutter SDK `^3.9.2` (para compilar la app)
- Un editor con soporte para Flutter/Dart y PHP (VS Code, Android Studio, etc.)

---

## Instalación y puesta en marcha

### 1. Base de datos
Crea la base de datos MySQL/MariaDB con **HeidiSQL** (u otro cliente de tu preferencia) y actualiza las credenciales de conexión en:
- `odontogest_web/Config/Define.php`
- `odontogest_api/core/db.php`

### 2. Panel web (`odontogest_web`)
1. Copia la carpeta a la raíz de proyectos de Laragon (p. ej. `www/odontogest_web`).
2. Asegúrate de que `.htaccess` esté habilitado (`mod_rewrite`, activo por defecto en Laragon).
3. Accede a `http://localhost/odontogest_web/` e inicia sesión con un usuario existente.

### 3. API REST (`odontogest_api`)
1. Copia la carpeta junto al panel web (p. ej. `www/odontogest_api`).
2. Verifica que el `.htaccess` reexponga el header `Authorization` (necesario también en Laragon).
3. La app móvil consumirá los endpoints desde aquí.

### 4. App móvil (`odontogest`)
1. Configura la URL base de la API en `lib/core/app_config.dart` (archivo local, no versionado — ver `.gitignore`).
2. Instala dependencias:
   ```bash
   flutter pub get
   ```
3. Ejecuta la app:
   ```bash
   flutter run
   ```

---

## Seguridad

- Contraseñas almacenadas con **bcrypt** (`password_hash` / `password_verify`).
- Tokens de sesión con **expiración de 24 horas**.
- **CSRF tokens** en todos los formularios críticos del panel web.
- **Rate limiting** por IP en login y en endpoints sensibles de la API.
- Consultas parametrizadas (**PDO prepared statements**) en toda la capa de datos, evitando inyección SQL.
- Registro de **auditoría** de acciones sensibles (creación, edición, eliminación, login/logout).

> ⚠️ **Nota de configuración:** antes de usar este proyecto en producción, cambia todas las credenciales de base de datos y tokens/secretos de ejemplo incluidos en los archivos de configuración, y sirve la aplicación bajo HTTPS.

---
