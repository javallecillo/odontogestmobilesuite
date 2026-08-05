# Manual de Usuario — OdontoGest

**Sistema de Gestión para Clínicas Dentales**
Clínica Dental OrtoNova — Honduras

---

## Tabla de contenidos

1. [Introducción](#1-introducción)
2. [Componentes del sistema](#2-componentes-del-sistema)
3. [Roles de usuario](#3-roles-de-usuario)
4. [Acceso al sistema](#4-acceso-al-sistema)
5. [Panel principal (Dashboard)](#5-panel-principal-dashboard)
6. [Módulo de Agenda y Citas](#6-módulo-de-agenda-y-citas)
7. [Módulo de Pacientes](#7-módulo-de-pacientes)
8. [Módulo de Expedientes Clínicos](#8-módulo-de-expedientes-clínicos)
9. [Módulo de Facturación](#9-módulo-de-facturación)
10. [Módulo de Inventario](#10-módulo-de-inventario)
11. [Administración: Usuarios, Roles y Odontólogos](#11-administración-usuarios-roles-y-odontólogos)
12. [Auditoría del sistema](#12-auditoría-del-sistema)
13. [Reportes](#13-reportes)
14. [Notificaciones](#14-notificaciones)
15. [Preguntas frecuentes](#15-preguntas-frecuentes)
16. [Glosario](#16-glosario)

---

## 1. Introducción

Este manual explica a **cómo usar** OdontoGest en el día a día de la clínica: desde iniciar sesión, hasta agendar una cita, actualizar un expediente clínico, emitir una factura o revisar el inventario.

Está dirigido a todo el personal que interactúa con el sistema:

- **Recepción** — agenda, pacientes, facturación.
- **Odontólogos y asistentes** — agenda propia, expedientes clínicos, odontograma.
- **Administración** — usuarios, roles, inventario, reportes y auditoría.

> 📌 Si se busca información técnica sobre la arquitectura, instalación o estructura del código, consulta el **README.md** del repositorio. Este manual se enfoca en el **uso funcional** del sistema.

---

## 2. Componentes del sistema

OdontoGest tiene dos formas de acceso, según el tipo de tarea:

| Componente | ¿Quién lo usa? | ¿Para qué? |
|---|---|---|
| **App móvil** (Android / iOS / escritorio) | Odontólogos y asistentes | Agenda diaria, expedientes clínicos, odontograma, recetas, fotos, notificaciones — ideal para usar en el consultorio. |
| **Panel web** (navegador) | Administración y recepción | Gestión completa: usuarios, roles, facturación, inventario, reportes, auditoría y configuración general. |

Ambos se conectan a la **misma base de datos**, por lo que cualquier cambio hecho en uno se refleja de inmediato en el otro (por ejemplo, una cita creada en el panel web aparece al instante en la agenda del odontólogo en su celular).

---

## 3. Roles de usuario

El acceso a cada módulo depende del **rol** asignado al usuario:

| Rol | Qué puede hacer |
|---|---|
| **Administrador** | Acceso total al sistema: usuarios, roles, configuración, auditoría, reportes y todos los módulos operativos. |
| **Odontólogo** | Ve su propia agenda, gestiona expedientes clínicos, odontograma, recetas y tratamientos de sus pacientes. |
| **Recepcionista** | Gestiona agenda general, pacientes y facturación. |
| **Asistente** | Apoyo operativo, con permisos configurables por un administrador según las necesidades de la clínica. |

> Un **Administrador** puede ajustar qué puede ver o hacer cada rol desde **Administración → Roles → Permisos** (ver [sección 11](#11-administración-usuarios-roles-y-odontólogos)).

---

## 4. Acceso al sistema

### 4.1 Panel web

1. Abre el navegador y entra a la URL de la clínica (por ejemplo, `http://localhost/odontogest_web/`).
2. Ingresa tu **usuario** y **contraseña**.
3. Haz clic en **Iniciar Sesión**.

Si tu cuenta está **inactiva** o **bloqueada**, el sistema te lo indicará; contacta a un administrador.

> 🔒 Por seguridad, después de **5 intentos fallidos** de inicio de sesión, la cuenta queda bloqueada temporalmente durante 5 minutos.

### 4.2 App móvil

1. Abre la aplicación OdontoGest en tu dispositivo.
2. Ingresa tu **usuario** y **contraseña** en la pantalla de bienvenida.
3. Presiona **Ingresar**.

Tu sesión permanece activa hasta que cierres sesión manualmente desde tu perfil o el token expire (24 horas).

### 4.3 Cerrar sesión

- **Panel web:** menú del avatar (esquina superior derecha) → **Cerrar sesión**.
- **App móvil:** ícono de salida en la parte superior del Dashboard, o desde **Mi Perfil**.

---

## 5. Panel principal (Dashboard)

Al iniciar sesión llegas al **Dashboard**, que resume la actividad del día:

- **Citas hoy** — total de citas programadas para la fecha actual.
- **Pacientes activos** — total de pacientes registrados con estado "activo".
- **Facturas pendientes** — facturas emitidas aún no cobradas.
- **Stock bajo** — cantidad de productos por debajo de su nivel mínimo de inventario.

Debajo de los indicadores encontrarás:

- **Citas del día**, con hora, paciente, odontólogo, servicio y estado.
- **Resumen de citas** por estado (pendientes, confirmadas, atendidas, canceladas).
- **Últimas facturas** emitidas.
- **Alertas de inventario** (productos con stock crítico).
- **Accesos rápidos** a los módulos más usados (nueva cita, nuevo paciente, nueva factura, inventario, pacientes, reportes).

---

## 6. Módulo de Agenda y Citas

### 6.1 Ver la agenda

- **Panel web:** menú lateral → **Agenda**. Puedes filtrar por **fecha**, **estado** de la cita o buscar por **paciente/odontólogo**.
- **App móvil:** pestaña **Agenda**. Usa los chips de filtro (Todas, Pendientes, Confirmadas, etc.) y el ícono de calendario para cambiar de fecha.

### 6.2 Crear una nueva cita

1. Presiona **Nueva Cita**.
2. Selecciona **paciente**, **odontólogo** y, opcionalmente, un **servicio**.
3. Indica **fecha y hora**. El sistema valida automáticamente que el odontólogo no tenga ya una cita en ese horario.
4. Agrega **notas** si es necesario.
5. Guarda la cita — quedará con estado **Pendiente**.

> En la app móvil, el flujo es guiado paso a paso: primero eliges odontólogo, luego fecha, luego un horario disponible, y finalmente el paciente.

### 6.3 Cambiar el estado de una cita

Estados posibles: `Pendiente` → `Confirmada` → `En curso` → `Atendida`, o bien `Cancelada` / `No asistió`.

- **Panel web:** botón de intercambio (⇄) en la fila de la cita → elige el nuevo estado → **Guardar cambio**.
- **App móvil:** toca la cita → **Cambiar estado** → selecciona la opción correspondiente (también puedes marcar asistencia: "Asistió" / "No asistió").

### 6.4 Eliminar una cita

Disponible solo para **Administrador**. Panel web → botón de papelera en la fila de la cita → confirma la eliminación. Esta acción es **permanente**.

---

## 7. Módulo de Pacientes

### 7.1 Buscar y listar pacientes

- **Panel web:** menú **Pacientes**. Usa el buscador (nombre, teléfono, correo o DNI) y el filtro de **estado** (activo/inactivo/fallecido).
- **App móvil:** pestaña **Pacientes** o **Buscar Paciente** desde el Dashboard. La búsqueda filtra en tiempo real por nombre, número de expediente o teléfono.

### 7.2 Registrar un nuevo paciente

1. Botón **Nuevo Paciente**.
2. Completa los datos: nombre, apellidos, DNI/pasaporte, fecha de nacimiento, sexo, teléfono, correo, dirección, estado civil, ocupación, contacto de emergencia y responsable de pago.
3. Guarda — el paciente queda con estado **Activo** por defecto.

### 7.3 Editar o desactivar un paciente

- **Editar:** ícono de lápiz en la fila del paciente → actualiza los datos → **Guardar**.
- **Desactivar:** ícono de "usuario tachado" → confirma. El paciente no se elimina de la base de datos, solo pasa a estado **Inactivo** (esto preserva su historial clínico).

---

## 8. Módulo de Expedientes Clínicos

Este es el corazón clínico del sistema. Cada paciente tiene un expediente con varias secciones (pestañas):

### 8.1 Historial

Lista cronológica de todas las citas del paciente, con odontólogo, servicio, estado y notas.

### 8.2 Datos Clínicos

- **Tipo de sangre.**
- **Antecedentes médicos** y **observaciones generales** (texto libre).
- **Alergias**, **enfermedades sistémicas** y **medicamentos actuales**: selecciona de un catálogo con casillas de verificación; para medicamentos puedes indicar la **dosis**.

Guarda los cambios con el botón **Guardar Datos Clínicos**.

### 8.3 Odontograma

Representación visual de las 32 piezas dentales (notación FDI), donde puedes:

1. Elegir una **condición** en la barra de herramientas (Sano, Caries, Extracción, Corona, Obturación, Ausente, Implante, Fractura, Bracket).
2. Tocar la pieza dental correspondiente para aplicar la condición.
3. Un diente puede tener **varias condiciones a la vez** (por ejemplo, corona + bracket), excepto extracción/ausente, que reemplazan cualquier otra condición.
4. Presiona **Guardar** para almacenar los cambios (visible cuando hay modificaciones pendientes).

Cada pieza se colorea según su condición, y puedes tocarla nuevamente para ver el detalle o limpiar sus condiciones.

### 8.4 Recetas (app móvil)

1. Botón **Nueva Receta**.
2. Completa medicamento, dosis, frecuencia, duración y notas.
3. Guarda — la receta queda asociada al expediente con fecha y odontólogo.

### 8.5 Tratamientos

1. Botón **Nuevo Tratamiento**.
2. Selecciona el tipo de tratamiento del catálogo (limpieza, extracción, ortodoncia, endodoncia, etc.) — el costo base se autocompleta si está definido.
3. Ajusta el costo si es necesario y agrega observaciones.
4. Guarda — el tratamiento queda registrado con estado (en proceso, completado, suspendido, cancelado).

### 8.6 Fotos clínicas (app móvil)

1. Botón **Agregar foto**.
2. Elige **Tomar foto** o **Elegir de galería**.
3. Agrega una descripción opcional (por ejemplo, "Antes del tratamiento").
4. La foto se sube y queda disponible en la galería del expediente.

### 8.7 Facturas del paciente

Desde la pestaña **Facturas** del expediente puedes consultar todas las facturas emitidas a ese paciente, con su estado y monto.

---

## 9. Módulo de Facturación

### 9.1 Ver facturas

Panel web → **Facturación**. Filtra por **estado** (emitida, pagada, anulada), **rango de fechas** o busca por paciente/número de factura. En la parte superior verás indicadores: emitidas, pagadas, ingresos del mes y monto pendiente.

### 9.2 Emitir una nueva factura

1. Botón **Nueva Factura**.
2. Busca y selecciona el **paciente**.
3. Elige el **método de pago** (efectivo, tarjeta, transferencia) y la **tasa de ISV** (0%, 15% o 18%, según lo que aplique en Honduras).
4. Agrega los **ítems** de la factura: descripción, cantidad y precio — se calculan automáticamente subtotal, ISV y total.
5. Agrega notas si es necesario y presiona **Emitir Factura**.

### 9.3 Marcar como pagada o anular

- **Marcar pagada:** ícono de check (✓) en la fila de la factura → confirma.
- **Anular:** ícono de prohibido (⊘) → indica el **motivo** de anulación → confirma. Solo un **Administrador** puede anular facturas.

> Las facturas anuladas no se eliminan; quedan registradas con estado "Anulada" y su motivo, para mantener la trazabilidad fiscal.

---

## 10. Módulo de Inventario

### 10.1 Ver productos

Panel web → **Inventario**, o pestaña **Inventario** en la app móvil. Se muestran indicadores de total de productos, activos, stock crítico y agotados. Puedes buscar por nombre y filtrar por estado.

Cada producto muestra su **nivel de stock** con un código de color:
- 🟢 **OK** — stock saludable.
- 🟡 **Bajo** — cerca del mínimo.
- 🔴 **Crítico / Agotado** — requiere reposición urgente.

### 10.2 Registrar un nuevo producto

1. Botón **Nuevo Producto**.
2. Completa nombre, proveedor, unidad de medida, stock actual, stock mínimo, precios de costo/venta y tasa de impuesto.
3. Guarda.

### 10.3 Ajustar stock

1. Ícono de cajas en la fila del producto → **Ajustar stock**.
2. Elige el tipo de movimiento: **Entrada** (+), **Salida** (–) o **Ajuste directo**.
3. Indica la **cantidad** y el **motivo** (por ejemplo, "Compra a proveedor" o "Uso en tratamiento").
4. Aplica el ajuste — el stock y el estado del producto (activo/agotado) se actualizan automáticamente.

### 10.4 Alertas de stock

El panel muestra un listado de **productos con stock bajo o crítico**, ordenado por urgencia, para facilitar la reposición oportuna.

---

## 11. Administración: Usuarios, Roles y Odontólogos

*(Disponible solo para el rol Administrador)*

### 11.1 Gestión de usuarios

Panel web → **Administración → Usuarios**.

- **Crear usuario:** botón **Nuevo usuario** → nombre completo, nombre de usuario, correo, teléfono, rol y contraseña inicial.
- **Editar:** ícono de lápiz → actualiza datos y rol.
- **Activar/Desactivar:** ícono correspondiente → confirma (no puedes desactivar tu propia cuenta).
- **Resetear contraseña:** ícono de llave → define una nueva contraseña temporal para el usuario.

### 11.2 Roles y permisos

Panel web → **Administración → Roles**.

- **Crear rol:** botón **Nuevo Rol** → nombre y descripción.
- **Configurar permisos:** botón **Permisos** en la tarjeta del rol → activa o desactiva permisos agrupados por módulo (agenda, expedientes, facturación, inventario, etc.). Puedes seleccionar/quitar todos los permisos de un módulo con el checkbox "Todos".
- Guarda los cambios con **Guardar Permisos**.

### 11.3 Odontólogos

Panel web → **Administración → Odontólogos**.

1. Botón **Nuevo Odontólogo**.
2. Completa nombre, apellidos, número de licencia, especialidad, cargo, DNI, teléfono, correo y, opcionalmente, vincula una cuenta de usuario existente.
3. Guarda. El odontólogo aparecerá disponible al agendar citas.
4. Puedes **activar/desactivar** un odontólogo (por ejemplo, durante vacaciones) desde el ícono correspondiente.

---

## 12. Auditoría del sistema

*(Disponible solo para el rol Administrador)*

Panel web → **Administración → Auditoría**. Aquí se registra automáticamente cada acción relevante del sistema: creaciones, ediciones, eliminaciones, inicios y cierres de sesión, y anulaciones.

- **Filtros disponibles:** usuario, módulo, tipo de acción, dirección IP y rango de fechas.
- **Ver detalle:** ícono de lupa en cada fila para ver la información completa del evento (usuario, rol, descripción, IP, navegador).
- **Exportar:** botón **Exportar CSV** para descargar el log filtrado y compartirlo o archivarlo.

---

## 13. Reportes

Panel web → **Reportes**. Disponibles tres reportes principales:

| Reporte | Qué muestra |
|---|---|
| **Reporte de Citas** | Total de citas, atendidas y canceladas por día, con porcentaje de efectividad, dentro de un rango de fechas. |
| **Reporte de Ingresos** | Facturación por día: subtotal, ISV y total recaudado, dentro de un rango de fechas. |
| **Reporte de Inventario** | Estado actual de todos los productos: stock, mínimo, precios y estado. |

Cada reporte permite definir un **rango de fechas** (cuando aplica) e **imprimir / exportar a PDF** usando la opción de impresión del navegador.

---

## 14. Notificaciones

El sistema genera notificaciones automáticas de **citas próximas** (hoy y mañana).

- **Panel web:** ícono de campana en la barra superior — muestra un contador de notificaciones no leídas. Haz clic para desplegar la lista; puedes marcar una o todas como leídas.
- **App móvil:** ícono de campana en el Dashboard, con el mismo contador. Toca para abrir el listado completo de notificaciones.

---

## 15. Preguntas frecuentes

**¿Por qué no puedo iniciar sesión?**
Verifica que el usuario y la contraseña sean correctos. Si tu cuenta está inactiva o bloqueada, contacta a un administrador. Recuerda que tras 5 intentos fallidos hay un bloqueo temporal de 5 minutos.

**¿Puedo eliminar un paciente por completo?**
No. Por seguridad e integridad del historial clínico, los pacientes solo se **desactivan**, nunca se eliminan de la base de datos.

**¿Qué pasa si dos citas se agendan a la misma hora con el mismo odontólogo?**
El sistema lo impide automáticamente: si el horario ya está ocupado para ese odontólogo, mostrará un error al intentar guardar la cita.

**¿Cómo corrijo una factura con errores?**
No se editan facturas emitidas. Debes **anularla** (indicando el motivo) y crear una nueva con los datos correctos.

**¿La información se sincroniza entre la app móvil y el panel web?**
Sí, en tiempo real, porque ambos usan la misma base de datos.

---

## 16. Glosario

| Término | Significado |
|---|---|
| **Expediente clínico** | Ficha médica completa de un paciente: antecedentes, alergias, odontograma, recetas y tratamientos. |
| **Odontograma** | Diagrama de las piezas dentales (notación FDI) usado para registrar el estado de cada diente. |
| **ISV** | Impuesto Sobre Ventas — impuesto aplicado en Honduras a bienes y servicios. |
| **Slot / Horario disponible** | Espacio de tiempo libre en la agenda de un odontólogo para agendar una cita. |
| **Auditoría** | Registro histórico de acciones realizadas por los usuarios dentro del sistema. |
| **KPI** | Indicador clave de desempeño mostrado en el Dashboard (por ejemplo, citas del día). |

---

*Para dudas técnicas sobre instalación, arquitectura o configuración del servidor, consulta el archivo `README.md` del repositorio.*