# Manual de Usuario — OdontoGest
**Sistema de Gestión para Clínica Odontológica**  
Versión 1.0 · 2025

---

## Tabla de contenido

1. [Introducción](#1-introducción)
2. [Requisitos del sistema](#2-requisitos-del-sistema)
3. [Instalación y configuración](#3-instalación-y-configuración)
4. [Acceso al sistema web](#4-acceso-al-sistema-web)
5. [Módulos de la aplicación web](#5-módulos-de-la-aplicación-web)
   - 5.1 Dashboard
   - 5.2 Pacientes
   - 5.3 Expedientes
   - 5.4 Agenda / Citas
   - 5.5 Odontólogos
   - 5.6 Servicios
   - 5.7 Facturación
   - 5.8 Inventario
   - 5.9 Reportes
   - 5.10 Usuarios y Roles
   - 5.11 Configuración
   - 5.12 Auditoría
6. [Aplicación móvil Flutter (odontólogos)](#6-aplicación-móvil-flutter)
   - 6.1 Requisitos y acceso
   - 6.2 Agenda del odontólogo
   - 6.3 Expediente del paciente
   - 6.4 Odontograma
   - 6.5 Recetas
   - 6.6 Tratamientos
   - 6.7 Fotos del expediente
   - 6.8 Nueva cita
7. [Roles y permisos](#7-roles-y-permisos)
8. [Preguntas frecuentes](#8-preguntas-frecuentes)

---

## 1. Introducción

OdontoGest es un sistema de gestión integral para clínicas odontológicas compuesto por dos componentes:

- **Aplicación web** (`odontogest_web`): para recepcionistas y administradores. Gestiona pacientes, agenda, facturación, inventario, reportes y configuración del sistema.
- **Aplicación Flutter** (`odontogest`): para odontólogos. Permite consultar la agenda del día, gestionar expedientes clínicos, registrar odontogramas, recetas, tratamientos y fotografías de los pacientes.

Ambas aplicaciones comparten la misma base de datos MySQL y se comunican a través de una REST API (`odontogest_api`).

---

## 2. Requisitos del sistema

| Componente | Requerimiento |
|---|---|
| Servidor web | XAMPP 8.x (Apache + PHP 8.2+) |
| Base de datos | MySQL 8.0 / MariaDB 10.6+ |
| PHP | 8.2 o superior |
| Flutter | SDK 3.9.2 o superior |
| Navegador (app web) | Chrome, Edge o Firefox actualizados |
| Red | Todos los dispositivos en la misma red local |

---

## 3. Instalación y configuración

### 3.1 Base de datos

1. Iniciar **XAMPP** → activar **Apache** y **MySQL**.
2. Abrir **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Crear base de datos: `odonto_gest`.
4. Importar el archivo `BD_OdontoGest/odonto_gest_v3.sql`.
5. Importar `BD_OdontoGest/stored_procedures.sql` (stored procedures).

### 3.2 Aplicación web

1. Copiar la carpeta `odontogest_web` a `C:/xampp/htdocs/`.
2. Abrir `odontogest_web/Config/Define.php` y verificar:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'odonto_gest');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // o tu contraseña de MySQL
   define('APP_URL',  'http://localhost/odontogest_web/');
   ```
3. Acceder a `http://localhost/odontogest_web/` en el navegador.

### 3.3 API REST (Flutter)

1. Copiar la carpeta `odontogest_api` a `C:/xampp/htdocs/`.
2. Verificar que `.htaccess` en la raíz de `odontogest_api` exista (para el header `Authorization`).
3. La API estará disponible en `http://localhost/odontogest_api/`.

### 3.4 Aplicación Flutter

1. Abrir `odontogest/lib/core/app_config.dart` y configurar la IP del servidor:
   ```dart
   // Si todos están en la misma red:
   static const String apiBase = 'http://192.168.X.X/odontogest_api';
   // Si se ejecuta en el mismo equipo:
   static const String apiBase = 'http://localhost/odontogest_api';
   ```
2. Desde la carpeta `odontogest/` ejecutar:
   ```bash
   flutter pub get
   flutter run -d chrome   # Para ejecutar en navegador
   ```
3. La app estará disponible en `http://localhost:8080` (por defecto).

---

## 4. Acceso al sistema web

Ir a `http://localhost/odontogest_web/`

| Campo | Valor por defecto |
|---|---|
| Usuario | `admin` |
| Contraseña | (la configurada en la BD) |

> La contraseña se almacena como hash SHA-256. Para generar un hash: `http://localhost/odontogest_api/tools/generar_hash.php?pass=tuPassword`

---

## 5. Módulos de la aplicación web

### 5.1 Dashboard

Muestra métricas en tiempo real:
- Total de pacientes, citas del día, ingresos del mes.
- Citas de hoy con estado y paciente.
- Alertas de stock crítico en inventario.
- Accesos rápidos a los módulos principales.

### 5.2 Pacientes

**Funciones disponibles:**
- Listar pacientes con búsqueda por nombre, teléfono, correo o DNI.
- Filtrar por estado (activo / inactivo / fallecido).
- **Registrar nuevo paciente**: nombre, apellidos, DNI, RTN, fecha de nacimiento, sexo, estado civil, ocupación, teléfonos de emergencia, contacto de emergencia, responsable de pago, correo y dirección.
- **Editar** datos de un paciente existente.
- **Desactivar** paciente (no se elimina de la BD).

> **Nota:** Todos los campos del formulario corresponden exactamente a las columnas de la tabla `pacientes` en la base de datos.

### 5.3 Expedientes

Acceso al historial clínico de cada paciente:
- Resumen general: datos del paciente, grupo sanguíneo, alergias, enfermedades previas, observaciones.
- **Odontograma**: visualización de las condiciones de cada pieza dental.
- **Historial de tratamientos**: lista de tratamientos registrados con estado y costo.
- **Recetas emitidas**: medicamentos recetados con dosis y duración.
- **Registro de imágenes** del paciente.

### 5.4 Agenda / Citas

- Vista de citas por fecha con filtros por estado.
- **Nueva cita**: seleccionar paciente, odontólogo, fecha y hora, servicio (opcional) y notas.
- Cambiar estado de una cita: Pendiente → Confirmada → En curso → Atendida / Cancelada.
- Registrar asistencia del paciente.

> El sistema valida que no existan dos citas para el mismo odontólogo en el mismo horario.

### 5.5 Odontólogos

- Registrar odontólogos con: nombre, apellidos, número de licencia, DNI, especialidad, cargo, usuario del sistema, teléfono, correo, fecha de nacimiento y estado.
- Editar datos de odontólogos existentes.
- Activar / desactivar / marcar como en vacaciones.

### 5.6 Servicios

- Catálogo de servicios odontológicos con nombre, descripción, precio base y estado.
- Crear y editar servicios.
- Los servicios se usan al registrar citas y en la facturación.

### 5.7 Facturación

- Lista de facturas emitidas con totales y estado de pago.
- **Nueva factura**:
  1. Buscar paciente.
  2. Agregar ítems (servicio, descripción, cantidad, precio).
  3. Seleccionar tasa de ISV (0%, 15% o 18%).
  4. Guardar factura (calcula subtotal, ISV y total automáticamente).

### 5.8 Inventario

- Lista de productos con stock actual, mínimo y nivel de alerta visual.
- **Nuevo producto**: nombre, proveedor, unidad de medida, stock, stock mínimo, precio costo, precio venta, tasa de impuesto, descripción y estado.
- **Ajustar stock**: entrada, salida o ajuste directo con motivo.
- Alertas automáticas de stock crítico.
- **Reporte de inventario** imprimible con todos los productos.

### 5.9 Reportes

Reportes disponibles (imprimibles en PDF desde el navegador):
- **Inventario**: estado actual del stock con precios.
- **Citas**: listado de citas por rango de fechas y estado.
- **Ingresos**: resumen de facturación por período.

Para imprimir: botón "Imprimir / PDF" en cada reporte → `Ctrl+P` → guardar como PDF.

### 5.10 Usuarios y Roles

**Usuarios:**
- Lista de usuarios del sistema con su rol.
- Crear nuevo usuario: nombre completo, usuario, correo, contraseña, rol y estado.
- Cambiar estado (activo / inactivo).

**Roles:**
- Lista de roles con sus permisos.
- El sistema incluye los roles: **Administrador**, **Recepcionista**, **Odontólogo**, **Auxiliar**.
- Se pueden asignar permisos específicos por módulo a cada rol.

### 5.11 Configuración

Panel de configuración del sistema dividido en secciones:
- **General**: nombre de la clínica, eslogan, teléfono, correo, dirección, sitio web.
- **Horario**: días laborales y horas de atención.
- **Logo**: subir logotipo de la clínica.
- **Colores**: personalizar colores de la interfaz.
- **Notificaciones**: configurar alertas automáticas.
- **Seguridad**: políticas de contraseñas y sesiones.
- **Pagos**: métodos de pago aceptados.
- **Respaldo**: exportar configuración.

### 5.12 Auditoría

- Registro completo de todas las acciones realizadas en el sistema.
- Muestra: fecha y hora, usuario, módulo, acción y detalle.
- Útil para rastrear cambios y detectar uso indebido.

---

## 6. Aplicación móvil Flutter

### 6.1 Requisitos y acceso

- Abrir la app en el navegador: `http://localhost:8080` (o la IP del servidor si es en red).
- Iniciar sesión con las credenciales de un usuario con rol **Odontólogo**.
- La app es un complemento para el trabajo clínico del odontólogo durante la consulta.

### 6.2 Agenda del odontólogo

La pantalla principal muestra las citas del día:
- Navegar entre fechas con el selector de calendario.
- Filtrar por estado: todas, pendientes, confirmadas, completadas, canceladas.
- Cada tarjeta de cita muestra: paciente, hora, servicio y estado actual.

**Acciones sobre una cita:**
- Tocar el ícono de estado → menú con opciones: Confirmar, Completar, Cancelar, Asistió, No asistió.
- Tocar la tarjeta → ir al expediente del paciente.

**Actualizar la lista:** deslizar hacia abajo (pull to refresh).

### 6.3 Expediente del paciente

Al entrar al expediente de un paciente se muestran 5 pestañas:

| Pestaña | Contenido |
|---|---|
| Resumen | Datos personales, contadores, alergias y enfermedades |
| Odontograma | Mapa interactivo de las 32 piezas dentales |
| Recetas | Lista de medicamentos recetados + botón para nueva receta |
| Tratamientos | Historial de tratamientos + registrar nuevo |
| Fotos | Galería de imágenes del expediente + subir nueva foto |

También se puede llegar al expediente desde **Buscar Paciente** en el menú inferior.

### 6.4 Odontograma

- Visualiza las 32 piezas dentales organizadas por cuadrante.
- Tocar una pieza → ver sus condiciones actuales.
- Tocar y mantener → agregar o quitar condiciones (caries, corona, bracket, extracción, etc.).
- Botón **Guardar** → sincroniza con el servidor.

### 6.5 Recetas

- La pestaña muestra todas las recetas emitidas para el paciente.
- Botón **Nueva Receta** → formulario deslizable desde abajo:
  - Medicamento (nombre y presentación)
  - Dosis (ej: 500 mg)
  - Frecuencia (ej: cada 8 horas)
  - Duración (ej: 7 días)
  - Notas adicionales (opcional)
- Tocar **Guardar Receta** → se guarda en el expediente.

### 6.6 Tratamientos

- Lista de tratamientos realizados con estado (en proceso, completado, suspendido, cancelado) y costo.
- Botón **Nuevo Tratamiento** → formulario:
  - Seleccionar tipo de tratamiento (cargado desde la base de datos).
  - Costo en lempiras (se prellenará con el precio base si está configurado).
  - Observaciones (opcional).

### 6.7 Fotos del expediente

- Galería en cuadrícula con todas las fotos del expediente.
- Botón **Agregar foto** → opciones:
  - **Tomar foto** → abre la cámara del dispositivo.
  - **Elegir de galería** → abre el selector de archivos.
- Al seleccionar la imagen se pide una descripción opcional (ej: "Antes del tratamiento").
- La foto se sube automáticamente al servidor.

### 6.8 Nueva cita

Accesible desde el botón `+` en la pantalla de Agenda. Flujo por pasos:

1. **Seleccionar odontólogo** — lista de odontólogos activos cargada desde la API.
2. **Seleccionar fecha** — calendario con restricción a los próximos 90 días.
3. **Seleccionar horario** — slots del día con disponibilidad en tiempo real (los ocupados aparecen deshabilitados).
4. **Seleccionar paciente** — búsqueda en la lista completa de pacientes activos. Agregar notas opcionales.
5. **Confirmar cita** — resumen final → botón Confirmar.

La cita queda registrada como "Pendiente" y aparece automáticamente en la agenda del día correspondiente.

---

## 7. Roles y permisos

| Módulo | Administrador | Recepcionista | Odontólogo | Auxiliar |
|---|:---:|:---:|:---:|:---:|
| Dashboard | ✓ | ✓ | ✓ | ✓ |
| Pacientes (CRUD) | ✓ | ✓ | Solo lectura | — |
| Expedientes | ✓ | ✓ | ✓ | — |
| Agenda | ✓ | ✓ | Solo sus citas | — |
| Odontólogos | ✓ | — | — | — |
| Servicios | ✓ | ✓ | — | — |
| Facturación | ✓ | ✓ | — | — |
| Inventario | ✓ | ✓ | — | ✓ |
| Reportes | ✓ | — | — | — |
| Usuarios / Roles | ✓ | — | — | — |
| Configuración | ✓ | — | — | — |
| Auditoría | ✓ | — | — | — |
| App Flutter | — | — | ✓ | — |

---

## 8. Preguntas frecuentes

**¿Qué hacer si la app Flutter no conecta con la API?**  
Verificar que:
1. XAMPP esté corriendo (Apache y MySQL encendidos).
2. La URL en `app_config.dart` sea correcta (IP del servidor, no `localhost` si se accede desde otro dispositivo).
3. El `.htaccess` de `odontogest_api` exista y esté configurado correctamente.

**¿Cómo restablecer la contraseña de un usuario?**  
En el sistema web: Menú → Usuarios → editar usuario → ingresar nueva contraseña → guardar. El sistema genera el hash automáticamente.

**¿El sistema elimina registros permanentemente?**  
No. Los pacientes, productos y odontólogos se desactivan (estado = inactivo) pero permanecen en la base de datos para mantener la integridad del historial.

**¿Se pueden imprimir recetas desde la app Flutter?**  
Actualmente no. La impresión de recetas se gestiona desde la aplicación web a través del módulo de Expedientes.

**¿El odontograma se guarda automáticamente?**  
No. Es necesario tocar el botón **Guardar** en la pantalla del odontograma para que los cambios queden registrados en el servidor.

---

*OdontoGest — Sistema de Gestión Odontológica · Proyecto Final de Desarrollo Móvil*
