<?php
include(__DIR__ . "/../php/verificar_sesion.php");

// Verificar que sea administrador
if (!esAdmin()) {
  header("Location: empleado.php");
  exit();
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créditos CR - Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="/css/admin.css" rel="stylesheet">
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <button class="toggle-btn" onclick="toggleSidebar()">
      <i class="fas fa-chevron-left"></i>
    </button>

    <div class="sidebar-header">
      <h2>Créditos CR</h2>
      <p>Sistema de Gestión</p>
    </div>

    <ul class="menu">
      <li class="menu-item">
        <a class="menu-link active" onclick="showSection('dashboard')">
          <i class="fas fa-home"></i>
          <span>Inicio</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('clientes')">
          <i class="fas fa-users"></i>
          <span>Clientes</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('prestamos')">
          <i class="fas fa-money-bill-wave"></i>
          <span>Préstamos</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('pagos')">
          <i class="fas fa-hand-holding-usd"></i>
          <span>Pagos Diarios</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('reportes')">
          <i class="fas fa-chart-line"></i>
          <span>Reportes</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('usuarios')">
          <i class="fas fa-user-shield"></i>
          <span>Usuarios</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('configuracion')">
          <i class="fas fa-cog"></i>
          <span>Configuración</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content" id="mainContent">
    <!-- Header -->
    <header class="header">
      <div class="header-left">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h1 id="pageTitle">Dashboard</h1>
      </div>
      <div class="header-right">
        <div class="user-info">
          <div class="user-avatar"><?php echo strtoupper(substr($nombre_usuario, 0, 2)); ?></div>
          <div>
            <div style="font-weight: 600;"><?php echo htmlspecialchars($nombre_usuario); ?></div>
            <div style="font-size: 12px; color: #6b7280;">Administrador</div>
          </div>
        </div>
        <button class="logout-btn" onclick="cerrarSesion()">
          <i class="fas fa-sign-out-alt"></i> Salir
        </button>
      </div>
    </header>

    <!-- Content -->
    <div class="content">
      <!-- Dashboard Section -->
      <section id="dashboard" class="section active">
        <div class="cards-grid">
          <div class="card">
            <div class="card-header">
              <div class="card-icon success">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
            <div class="card-title">Total Prestado</div>
            <div class="card-value" id="total-prestado">$0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-up" style="color: var(--success-color);"></i>
              <span>Monto total de préstamos</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon info">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
            <div class="card-title">Total Recuperado</div>
            <div class="card-value" id="total-recuperado">$0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-up" style="color: var(--success-color);"></i>
              <span>Pagos recibidos</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon warning">
                <i class="fas fa-user-check"></i>
              </div>
            </div>
            <div class="card-title">Clientes Activos</div>
            <div class="card-value" id="clientes-activos">0</div>
            <div class="card-footer">
              <i class="fas fa-users"></i>
              <span>Con préstamos activos</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon danger">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
            </div>
            <div class="card-title">Clientes Morosos</div>
            <div class="card-value" id="clientes-morosos">0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-down" style="color: var(--danger-color);"></i>
              <span>Pagos vencidos</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Clientes Section -->
      <section id="clientes" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3>Gestión de Clientes</h3>
            <div class="search-box">
              <input type="text" id="buscarCliente" class="search-input" placeholder="Buscar por nombre o cédula..." onkeyup="filtrarClientes()">
              <button class="btn btn-primary" onclick="openModal('modalCliente')">
                <i class="fas fa-plus"></i> Nuevo Cliente
              </button>
            </div>
          </div>

          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Cédula</th>
                  <th>Nombre</th>
                  <th>Teléfono</th>
                  <th>Dirección</th>
                  <th>Correo</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="clientesTable">
                <tr>
                  <td colspan="6" style="text-align: center;">Cargando clientes...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Préstamos Section -->
      <section id="prestamos" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3>Gestión de Préstamos</h3>
            <div class="search-box">
              <select class="search-input" id="filtroPrestamos" onchange="cargarPrestamos()">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="cancelado">Cancelados</option>
              </select>
              <button class="btn btn-primary" onclick="openModal('modalPrestamo')">
                <i class="fas fa-plus"></i> Nuevo Préstamo
              </button>
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Cliente</th>
                  <th>Monto</th>
                  <th>Interés</th>
                  <th>Cuota Diaria</th>
                  <th>Fecha Inicio</th>
                  <th>Saldo Pendiente</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="prestamosTable">
                <tr>
                  <td colspan="9" style="text-align: center;">Cargando préstamos...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Pagos Section -->
      <section id="pagos" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3>Registro de Pagos Diarios</h3>
            <div class="search-box">
              <input type="date" class="search-input" id="fechaPago" onchange="cargarPagos()">
              <button class="btn btn-success" onclick="openModal('modalPago')">
                <i class="fas fa-plus"></i> Registrar Pago
              </button>
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Préstamo ID</th>
                  <th>Cuota Esperada</th>
                  <th>Monto Pagado</th>
                  <th>Método</th>
                  <th>Fecha Pago</th>
                  <th>Cobrador</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="pagosTable">
                <tr>
                  <td colspan="8" style="text-align: center;">Cargando pagos...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Reportes Section -->
      <section id="reportes" class="section">
        <div class="cards-grid" style="margin-bottom: 20px;">
          <div class="card">
            <div class="card-title">Ingresos Hoy</div>
            <div class="card-value" id="ingresos-hoy">$0</div>
          </div>
          <div class="card">
            <div class="card-title">Ingresos Semana</div>
            <div class="card-value" id="ingresos-semana">$0</div>
          </div>
          <div class="card">
            <div class="card-title">Ingresos Mes</div>
            <div class="card-value" id="ingresos-mes">$0</div>
          </div>
        </div>

        <div class="table-container">
          <div class="table-header">
            <h3>Resumen de Actividad - Últimos 7 Días</h3>
            <button class="btn btn-info" onclick="cargarReportes()">
              <i class="fas fa-sync"></i> Actualizar
            </button>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Total Recaudado</th>
                  <th>Número de Pagos</th>
                </tr>
              </thead>
              <tbody id="reportesTable">
                <tr>
                  <td colspan="3" style="text-align: center;">Cargando reportes...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Usuarios Section -->
      <section id="usuarios" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3>Gestión de Usuarios</h3>
            <button class="btn btn-primary" onclick="openModal('modalUsuario')">
              <i class="fas fa-plus"></i> Nuevo Usuario
            </button>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Rol</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="usuariosTable">
                <tr>
                  <td colspan="6" style="text-align: center;">Cargando usuarios...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Configuración Section -->
      <section id="configuracion" class="section">
        <div class="table-container">
          <h3>Parámetros del Sistema</h3>
          <form class="form-grid" style="margin-top: 20px;" id="formConfiguracion">
            <div class="form-group">
              <label>Interés por Defecto (%)</label>
              <input type="number" id="interes_defecto" value="20" min="0" max="100">
            </div>
            <div class="form-group">
              <label>Días de Cobro por Defecto</label>
              <input type="number" id="dias_defecto" value="30" min="1">
            </div>
            <div class="form-group">
              <label>Días de Gracia</label>
              <input type="number" id="dias_gracia" value="3" min="0">
            </div>
            <div class="form-group">
              <label>Mora Diaria (%)</label>
              <input type="number" id="mora_diaria" value="2" min="0">
            </div>
          </form>
          <button class="btn btn-success" style="margin-top: 20px;" onclick="guardarConfiguracion()">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
        </div>
      </section>
    </div>
  </main>

  <!-- Modal Cliente -->
  <div class="modal" id="modalCliente">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Cliente</h3>
        <button class="close-modal" onclick="closeModal('modalCliente')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form class="form-grid" id="formCliente">
        <div class="form-group">
          <label for="cedula">Cédula *</label>
          <input type="text" name="cedula" id="cedula" required placeholder="1234567890">
        </div>

        <div class="form-group">
          <label for="nombre">Nombre Completo *</label>
          <input type="text" name="nombre" id="nombre" required placeholder="María González">
        </div>

        <div class="form-group">
          <label for="telefono">Teléfono *</label>
          <input type="tel" name="telefono" id="telefono" required placeholder="300-1234567">
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="direccion">Dirección *</label>
          <input type="text" name="direccion" id="direccion" required placeholder="Calle 123 #45-67">
        </div>

        <div class="form-group">
          <label for="correo">Email</label>
          <input type="email" name="correo" id="correo" placeholder="maria@ejemplo.com">
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Guardar Cliente
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalCliente')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal editar Cliente -->
  <div class="modal" id="modalEditarCliente">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Editar Cliente</h3>
        <button class="close-modal" onclick="closeModal('modalEditarCliente')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form class="form-grid" id="formEditarCliente">
        <div class="form-group">
          <label for="editCedula">Cédula *</label>
          <input type="text" name="cedula" id="editCedula" readonly>
        </div>

        <div class="form-group">
          <label for="editNombre">Nombre Completo *</label>
          <input type="text" name="nombre" id="editNombre" required>
        </div>

        <div class="form-group">
          <label for="editTelefono">Teléfono *</label>
          <input type="tel" name="telefono" id="editTelefono" required>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="editDireccion">Dirección *</label>
          <input type="text" name="direccion" id="editDireccion" required>
        </div>

        <div class="form-group">
          <label for="editCorreo">Email</label>
          <input type="email" name="correo" id="editCorreo">
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Actualizar
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalEditarCliente')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Préstamo -->
  <div class="modal" id="modalPrestamo">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Préstamo</h3>
        <button class="close-modal" onclick="closeModal('modalPrestamo')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form class="form-grid" id="formPrestamo" oninput="calcularCuota()">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="cliente_id">Seleccionar Cliente *</label>
          <select name="cliente_id" id="cliente_id" required>
            <option value="">-- Seleccione un cliente --</option>
          </select>
        </div>

        <div class="form-group">
          <label for="monto">Monto del Préstamo *</label>
          <input type="number" name="monto" id="monto" required min="0" placeholder="500000">
        </div>

        <div class="form-group">
          <label for="interes">Interés Total (%) *</label>
          <input type="number" name="interes" id="interes" required value="20" min="0" max="100">
        </div>

        <div class="form-group">
          <label for="cuotas">Número de Cuotas (días) *</label>
          <input type="number" name="cuotas" id="cuotas" required value="30" min="1">
        </div>

        <div class="form-group">
          <label for="fecha_inicio">Fecha de Inicio *</label>
          <input type="date" name="fecha_inicio" id="fecha_inicio" required>
        </div>

        <div class="form-group" style="grid-column: 1 / -1; background: #f0f9ff; padding: 15px; border-radius: 8px;">
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
              <div style="font-size: 12px; color: #6b7280;">Monto Total a Pagar</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--primary-color);" id="montoTotal">$0</div>
            </div>
            <div>
              <div style="font-size: 12px; color: #6b7280;">Cuota Diaria</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--success-color);" id="cuotaDiaria">$0</div>
            </div>
            <div>
              <div style="font-size: 12px; color: #6b7280;">Fecha de Vencimiento</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--dark-color);" id="fechaVencimiento">--</div>
            </div>
          </div>
        </div>

        <input type="hidden" name="cuota_diaria" id="cuota_diaria">
        <input type="hidden" name="fecha_fin" id="fecha_fin">

        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Registrar Préstamo
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalPrestamo')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Detalle Préstamo -->
  <div class="modal" id="modalDetallePrestamo">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Detalle del Préstamo</h3>
        <button class="close-modal" onclick="closeModal('modalDetallePrestamo')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div id="contenidoDetallePrestamo" style="padding: 20px;">
        Cargando...
      </div>
    </div>
  </div>

  <!-- Modal Pago -->
  <div class="modal" id="modalPago">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Registrar Pago</h3>
        <button class="close-modal" onclick="closeModal('modalPago')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form class="form-grid" id="formPago">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Seleccionar Préstamo *</label>
          <select name="prestamo_id" id="prestamo_pago" required>
            <option value="">-- Seleccione --</option>
          </select>
        </div>
        <div class="form-group">
          <label>Monto a Pagar *</label>
          <input type="number" name="monto_pagado" id="monto_pagado" required placeholder="20000" min="0" step="0.01">
        </div>
        <div class="form-group">
          <label>Método de Pago *</label>
          <select name="metodo_pago" required>
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="nequi">Nequi</option>
            <option value="daviplata">Daviplata</option>
          </select>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Observaciones</label>
          <textarea name="observacion" rows="2" placeholder="Notas sobre el pago..."></textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Registrar Pago
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalPago')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Usuario -->
  <div class="modal" id="modalUsuario">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Usuario</h3>
        <button class="close-modal" onclick="closeModal('modalUsuario')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form class="form-grid" id="formUsuario">
        <div class="form-group">
          <label>Nombre Completo *</label>
          <input type="text" name="nombre" required placeholder="Pedro Gómez">
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="correo" required placeholder="pedro@creditoscr.com">
        </div>
        <div class="form-group">
          <label>Contraseña *</label>
          <input type="password" name="clave" required placeholder="••••••••">
        </div>
        <div class="form-group">
          <label>Rol *</label>
          <select name="rol" required>
            <option value="">-- Seleccione --</option>
            <option value="admin">Administrador</option>
            <option value="empleado">Empleado</option>
          </select>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Crear Usuario
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalUsuario')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Editar Usuario -->
  <div class="modal" id="modalEditarUsuario">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Editar Usuario</h3>
        <button class="close-modal" onclick="closeModal('modalEditarUsuario')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form class="form-grid" id="formEditarUsuario">
        <input type="hidden" name="usuario_id" id="editUsuarioId">

        <div class="form-group">
          <label>Nombre Completo *</label>
          <input type="text" name="nombre" id="editUsuarioNombre" required>
        </div>

        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="correo" id="editUsuarioCorreo" required>
        </div>

        <div class="form-group">
          <label>Rol *</label>
          <select name="rol" id="editUsuarioRol" required>
            <option value="admin">Administrador</option>
            <option value="empleado">Empleado</option>
          </select>
        </div>

        <div class="form-group">
          <label>Nueva Contraseña (dejar vacío para mantener)</label>
          <input type="password" name="clave" id="editUsuarioClave" placeholder="••••••••">
          <small style="color: #6b7280;">Solo llena este campo si deseas cambiar la contraseña</small>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-save"></i> Guardar Cambios
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalEditarUsuario')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function cerrarSesion() {
      Swal.fire({
        title: '¿Cerrar sesión?',
        text: "¿Estás seguro de que deseas salir?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '/php/logout.php';
        }
      });
    }

    function openMobileMenu() {
      document.getElementById('sidebar').classList.add('active');
    }

    function closeMobileMenu() {
      document.getElementById('sidebar').classList.remove('active');
    }

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');

      // Si estamos en móvil → abrir/cerrar menú móvil
      if (window.innerWidth <= 850) {
        sidebar.classList.toggle('active');
        return;
      }

      // Si es escritorio → colapsar/expandir
      sidebar.classList.toggle('collapsed');
      document.getElementById('mainContent').classList.toggle('expanded');
    }

    // Cerrar sidebar móvil al elegir una sección
    document.querySelectorAll('.menu-link').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 850) {
          closeMobileMenu();
        }
      });
    });

    function showSection(id) {
      document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
      const section = document.getElementById(id);
      if (section) {
        section.classList.add('active');
      }

      document.querySelectorAll('.menu-link').forEach(link => link.classList.remove('active'));

      // Buscar y activar el enlace del menú
      const menuLinks = document.querySelectorAll('.menu-link');
      menuLinks.forEach(link => {
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(id)) {
          link.classList.add('active');
        }
      });

      // Cargar datos según la sección
      if (id === 'clientes') cargarClientes();
      if (id === 'prestamos') cargarPrestamos();
      if (id === 'pagos') cargarPagos();
      if (id === 'usuarios') cargarUsuarios();
      if (id === 'dashboard') cargarEstadisticas();
      if (id === 'reportes') cargarReportes();
    }

    function openModal(modalId) {
      document.getElementById(modalId).classList.add('active');
      if (modalId === 'modalPrestamo') cargarClientesSelect();
      if (modalId === 'modalPago') cargarPrestamosSelect();
    }

    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');

      // Limpiar formularios
      if (modalId === 'modalCliente') document.getElementById('formCliente').reset();
      if (modalId === 'modalPrestamo') document.getElementById('formPrestamo').reset();
      if (modalId === 'modalPago') document.getElementById('formPago').reset();
      if (modalId === 'modalUsuario') document.getElementById('formUsuario').reset();
      if (modalId === 'modalEditarUsuario') document.getElementById('formEditarUsuario').reset();
    }

    window.addEventListener('click', function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
      }
    });

    async function cargarEstadisticas() {
      try {
        const response = await fetch('/php/obtener_estadisticas.php');
        const data = await response.json();

        document.getElementById('total-prestado').textContent = formatMoney(data.total_prestado);
        document.getElementById('total-recuperado').textContent = formatMoney(data.total_recuperado);
        document.getElementById('clientes-activos').textContent = data.clientes_activos;
        document.getElementById('clientes-morosos').textContent = data.clientes_morosos;
      } catch (error) {
        console.error('Error cargando estadísticas:', error);
      }
    }

    async function cargarReportes() {
      try {
        const response = await fetch('/php/obtener_reportes.php');
        const data = await response.json();

        // Actualizar cards de ingresos
        document.getElementById('ingresos-hoy').textContent = formatMoney(data.ingresos_hoy || 0);
        document.getElementById('ingresos-semana').textContent = formatMoney(data.ingresos_semana || 0);
        document.getElementById('ingresos-mes').textContent = formatMoney(data.ingresos_mes || 0);

        // Cargar tabla de actividad
        const tbody = document.getElementById('reportesTable');
        if (!data.actividad_7dias || data.actividad_7dias.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3" style="text-align: center;">No hay actividad registrada</td></tr>';
          return;
        }

        tbody.innerHTML = data.actividad_7dias.map(item => `
          <tr>
            <td>${item.fecha}</td>
            <td style="font-weight: 600; color: #10b981;">${formatMoney(item.total)}</td>
            <td>${item.num_pagos}</td>
          </tr>
        `).join('');
      } catch (error) {
        console.error('Error cargando reportes:', error);
        document.getElementById('reportesTable').innerHTML =
          '<tr><td colspan="3" style="text-align: center; color: red;">Error al cargar reportes</td></tr>';
      }
    }

    async function cargarClientes() {
      try {
        const response = await fetch('/php/obtener_cliente.php');
        const clientes = await response.json();

        const tbody = document.getElementById('clientesTable');
        if (clientes.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay clientes registrados</td></tr>';
          return;
        }

        tbody.innerHTML = clientes.map(cliente => `
          <tr>
            <td>${cliente.cedula}</td>
            <td>${cliente.nombre}</td>
            <td>${cliente.telefono}</td>
            <td>${cliente.direccion}</td>
            <td>${cliente.correo || '-'}</td>
            <td>
              <button class="btn btn-primary btn-sm" onclick="abrirEditarCliente('${cliente.cedula}')">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-danger btn-sm" onclick="eliminarCliente('${cliente.cedula}')">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `).join('');
      } catch (error) {
        console.error('Error cargando clientes:', error);
      }
    }

    // Función de registrar cliente
    document.getElementById('formCliente').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      console.log('=== Datos del cliente ===');
      for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
      }

      try {
        const response = await fetch('/php/registrar_cliente.php', {
          method: 'POST',
          body: formData
        });

        const text = await response.text();
        console.log('Respuesta raw:', text);

        const data = JSON.parse(text);
        console.log('Respuesta parseada:', data);

        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          closeModal('modalCliente');
          cargarClientes();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error completo:', error);
        Swal.fire('Error', 'No se pudo conectar con el servidor: ' + error.message, 'error');
      }
    });

    async function abrirEditarCliente(cedula) {
      try {
        const response = await fetch(`/php/obtener_cliente.php?cedula=${cedula}`);
        const cliente = await response.json();

        document.getElementById('editCedula').value = cliente.cedula;
        document.getElementById('editNombre').value = cliente.nombre;
        document.getElementById('editTelefono').value = cliente.telefono;
        document.getElementById('editDireccion').value = cliente.direccion;
        document.getElementById('editCorreo').value = cliente.correo || '';

        openModal('modalEditarCliente');
      } catch (error) {
        Swal.fire('Error', 'No se pudo cargar el cliente', 'error');
      }
    }

    document.getElementById('formEditarCliente').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/editar_cliente.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          closeModal('modalEditarCliente');
          cargarClientes();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
      }
    });

    function eliminarCliente(cedula) {
      Swal.fire({
        title: '¿Eliminar cliente?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('cedula', cedula);

          try {
            const response = await fetch('/php/eliminar_cliente.php', {
              method: 'POST',
              body: formData
            });
            const data = await response.json();

            if (data.success) {
              Swal.fire('Eliminado', data.message, 'success');
              cargarClientes();
            } else {
              Swal.fire('Error', data.message, 'error');
            }
          } catch (error) {
            Swal.fire('Error', 'No se pudo eliminar el cliente', 'error');
          }
        }
      });
    }

    function filtrarClientes() {
      const busqueda = document.getElementById('buscarCliente').value.toLowerCase();
      const filas = document.querySelectorAll('#clientesTable tr');

      filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(busqueda) ? '' : 'none';
      });
    }

    async function cargarPrestamos() {
      try {
        const filtro = document.getElementById('filtroPrestamos').value;
        let url = '/php/obtener_prestamos.php';
        if (filtro) {
          url += `?estado=${filtro}`;
        }

        const response = await fetch(url);
        const prestamos = await response.json();

        const tbody = document.getElementById('prestamosTable');
        if (prestamos.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay préstamos registrados</td></tr>';
          return;
        }

        tbody.innerHTML = prestamos.map(p => `
          <tr>
            <td>#${p.id}</td>
            <td>${p.cliente_nombre}</td>
            <td>${formatMoney(p.monto)}</td>
            <td>${p.interes}%</td>
            <td>${formatMoney(p.cuota_diaria)}</td>
            <td>${p.fecha_inicio}</td>
            <td>${formatMoney(p.saldo_pendiente)}</td>
            <td><span class="badge badge-${p.estado === 'activo' ? 'success' : 'secondary'}">${p.estado}</span></td>
            <td>
              <button class="btn btn-primary btn-sm" onclick="verDetallePrestamo(${p.id})" title="Ver detalles">
                <i class="fas fa-eye"></i>
              </button>
            </td>
          </tr>
        `).join('');
      } catch (error) {
        console.error('Error cargando préstamos:', error);
      }
    }

    async function verDetallePrestamo(prestamoId) {
      try {
        const response = await fetch(`/php/obtener_detalle_prestamo.php?id=${prestamoId}`);
        const data = await response.json();

        if (!data.success) {
          Swal.fire('Error', data.message, 'error');
          return;
        }

        const prestamo = data.prestamo;
        const pagos = data.pagos || [];

        let htmlPagos = '';
        if (pagos.length > 0) {
          htmlPagos = '<h4 style="margin-top: 20px;">Historial de Pagos:</h4><div style="max-height: 300px; overflow-y: auto;">';
          pagos.forEach(pago => {
            htmlPagos += `
              <div style="border: 1px solid #e5e7eb; padding: 10px; margin: 5px 0; border-radius: 5px;">
                <p><strong>Fecha:</strong> ${pago.fecha_pago}</p>
                <p><strong>Monto:</strong> ${formatMoney(pago.monto_pagado)}</p>
                <p><strong>Método:</strong> ${pago.metodo_pago}</p>
                ${pago.observacion ? `<p><strong>Observación:</strong> ${pago.observacion}</p>` : ''}
              </div>
            `;
          });
          htmlPagos += '</div>';
        } else {
          htmlPagos = '<p style="color: #888; margin-top: 20px;">No hay pagos registrados</p>';
        }

        document.getElementById('contenidoDetallePrestamo').innerHTML = `
          <div style="text-align: left;">
            <h4>Información del Préstamo</h4>
            <p><strong>ID:</strong> #${prestamo.id}</p>
            <p><strong>Cliente:</strong> ${prestamo.cliente_nombre}</p>
            <p><strong>Monto Inicial:</strong> ${formatMoney(prestamo.monto)}</p>
            <p><strong>Interés:</strong> ${prestamo.interes}%</p>
            <p><strong>Cuota Diaria:</strong> ${formatMoney(prestamo.cuota_diaria)}</p>
            <p><strong>Fecha Inicio:</strong> ${prestamo.fecha_inicio}</p>
            <p><strong>Fecha Fin:</strong> ${prestamo.fecha_fin}</p>
            <p><strong>Saldo Pendiente:</strong> ${formatMoney(prestamo.saldo_pendiente)}</p>
            <p><strong>Estado:</strong> <span class="badge badge-${prestamo.estado === 'activo' ? 'success' : 'secondary'}">${prestamo.estado.toUpperCase()}</span></p>
            ${htmlPagos}
          </div>
        `;

        openModal('modalDetallePrestamo');
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar el detalle del préstamo', 'error');
      }
    }

    async function cargarClientesSelect() {
      try {
        const response = await fetch('/php/obtener_cliente.php');
        const clientes = await response.json();

        const select = document.getElementById('cliente_id');
        select.innerHTML = '<option value="">-- Seleccione un cliente --</option>' +
          clientes.map(c => `<option value="${c.id}">${c.nombre} - ${c.cedula}</option>`).join('');
      } catch (error) {
        console.error('Error cargando clientes:', error);
      }
    }

    function calcularCuota() {
      const monto = parseFloat(document.getElementById('monto').value) || 0;
      const interes = parseFloat(document.getElementById('interes').value) || 0;
      const cuotas = parseInt(document.getElementById('cuotas').value) || 0;
      const fechaInicio = document.getElementById('fecha_inicio').value;

      const montoTotal = monto + (monto * (interes / 100));
      const cuotaDiaria = cuotas > 0 ? montoTotal / cuotas : 0;

      document.getElementById('montoTotal').textContent = formatMoney(montoTotal);
      document.getElementById('cuotaDiaria').textContent = formatMoney(cuotaDiaria);
      document.getElementById('cuota_diaria').value = cuotaDiaria.toFixed(2);

      if (fechaInicio && cuotas > 0) {
        const fecha = new Date(fechaInicio);
        fecha.setDate(fecha.getDate() + cuotas);
        const vencimiento = fecha.toISOString().split('T')[0];
        document.getElementById('fechaVencimiento').textContent = vencimiento;
        document.getElementById('fecha_fin').value = vencimiento;
      }
    }

    document.getElementById('formPrestamo').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/registrar_prestamo.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          closeModal('modalPrestamo');
          cargarPrestamos();
          cargarEstadisticas();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error en fetch:', error);
        Swal.fire('Error', 'No se pudo registrar el préstamo', 'error');
      }
    });

    // FUNCIÓN PARA VER COMPROBANTE DE PAGO
    async function verComprobantePago(pagoId) {
      try {
        const response = await fetch(`/php/generar_comprobante.php?id=${pagoId}`);
        const data = await response.json();

        if (!data.success) {
          Swal.fire('Error', data.message, 'error');
          return;
        }

        // Crear HTML del comprobante
        const htmlComprobante = `
      <div id="comprobante-print" style="text-align: left; padding: 20px; border: 2px solid #667eea; border-radius: 10px; background: white;">
        <!-- Encabezado -->
        <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 15px;">
          <h2 style="color: #667eea; margin: 0; font-size: 28px;">CRÉDITOS CR</h2>
          <p style="margin: 5px 0; color: #6b7280;">Sistema de Gestión de Créditos</p>
          <p style="margin: 5px 0; font-weight: 600;">COMPROBANTE DE PAGO</p>
          <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">N° ${data.numero_comprobante}</p>
        </div>

        <!-- Fecha y Tipo de Pago -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; padding: 10px; background: #f9fafb; border-radius: 5px;">
          <div>
            <strong>Fecha:</strong> ${data.fecha}
          </div>
          <div style="text-align: right;">
            <span style="background: ${data.tipo_pago === 'CUOTA COMPLETA' ? '#10b981' : '#f59e0b'}; color: white; padding: 5px 10px; border-radius: 5px; font-weight: 600; font-size: 12px;">
              ${data.tipo_pago}
            </span>
          </div>
        </div>

        <!-- Información del Cliente -->
        <div style="margin-bottom: 15px; padding: 15px; background: #f0f9ff; border-left: 4px solid #667eea; border-radius: 5px;">
          <h4 style="margin: 0 0 10px 0; color: #667eea;">Información del Cliente</h4>
          <p style="margin: 5px 0;"><strong>Nombre:</strong> ${data.cliente.nombre}</p>
          <p style="margin: 5px 0;"><strong>Cédula:</strong> ${data.cliente.cedula}</p>
          <p style="margin: 5px 0;"><strong>Teléfono:</strong> ${data.cliente.telefono}</p>
          <p style="margin: 5px 0;"><strong>Dirección:</strong> ${data.cliente.direccion}</p>
        </div>

        <!-- Detalles del Pago -->
        <div style="margin-bottom: 15px; padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 5px;">
          <h4 style="margin: 0 0 10px 0; color: #10b981;">Detalles del Pago</h4>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
              <p style="margin: 5px 0;"><strong>Monto Pagado:</strong></p>
              <p style="margin: 5px 0; font-size: 24px; color: #10b981; font-weight: 700;">
                ${formatMoney(data.pago.monto)}
              </p>
            </div>
            <div>
              <p style="margin: 5px 0;"><strong>Método de Pago:</strong></p>
              <p style="margin: 5px 0; font-size: 18px; font-weight: 600;">
                ${data.pago.metodo}
              </p>
            </div>
          </div>
          ${data.pago.observacion ? `
            <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 5px;">
              <p style="margin: 0;"><strong>Observaciones:</strong></p>
              <p style="margin: 5px 0; color: #6b7280;">${data.pago.observacion}</p>
            </div>
          ` : ''}
        </div>

        <!-- Información del Préstamo -->
        <div style="margin-bottom: 15px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 5px;">
          <h4 style="margin: 0 0 10px 0; color: #f59e0b;">Información del Préstamo</h4>
          <p style="margin: 5px 0;"><strong>Préstamo #:</strong> ${data.prestamo.id}</p>
          <p style="margin: 5px 0;"><strong>Cuota Diaria:</strong> ${formatMoney(data.prestamo.cuota_diaria)}</p>
          <p style="margin: 5px 0;"><strong>Saldo Pendiente:</strong> ${formatMoney(data.prestamo.saldo_pendiente)}</p>
        </div>

        <!-- Pie de página -->
        <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 2px solid #e5e7eb;">
          <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">
            <strong>Cobrador:</strong> ${data.cobrador}
          </p>
          <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">
            Este comprobante es válido como constancia de pago
          </p>
          <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">
            Créditos CR - ${new Date().getFullYear()}
          </p>
        </div>
      </div>
    `;

        // Mostrar en SweetAlert con opción de imprimir
        Swal.fire({
          title: 'Comprobante de Pago',
          html: htmlComprobante,
          width: '700px',
          showCancelButton: true,
          confirmButtonText: '<i class="fas fa-print"></i> Imprimir',
          cancelButtonText: 'Cerrar',
          confirmButtonColor: '#667eea',
          cancelButtonColor: '#6b7280',
          customClass: {
            popup: 'comprobante-popup'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            imprimirComprobante(htmlComprobante);
          }
        });

      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar el comprobante', 'error');
      }
    }

    // Función para imprimir el comprobante
    function imprimirComprobante(htmlComprobante) {
      const ventanaImpresion = window.open('', '', 'width=800,height=600');
      ventanaImpresion.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Comprobante de Pago - Créditos CR</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          margin: 20px;
        }
        @media print {
          body {
            margin: 0;
          }
          button {
            display: none;
          }
        }
      </style>
    </head>
    <body>
      ${htmlComprobante}
      <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
          <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
          Cerrar
        </button>
      </div>
    </body>
    </html>
  `);
      ventanaImpresion.document.close();
    }

    // Actualizar la función cargarPagos para agregar el evento al botón
    async function cargarPagos() {
      try {
        const fechaSeleccionada = document.getElementById('fechaPago').value;

        let url = '/php/obtener_pagos.php';
        if (fechaSeleccionada) {
          url += `?fecha=${fechaSeleccionada}`;
        }

        const response = await fetch(url);
        const pagos = await response.json();

        const tbody = document.getElementById('pagosTable');

        if (pagos.length === 0) {
          const mensaje = fechaSeleccionada ?
            `No hay pagos registrados para la fecha ${fechaSeleccionada}` :
            'No hay pagos registrados';
          tbody.innerHTML = `<tr><td colspan="8" style="text-align: center;">${mensaje}</td></tr>`;
          return;
        }

        tbody.innerHTML = pagos.map(p => `
      <tr>
        <td>${p.cliente_nombre}</td>
        <td>#${p.prestamo_id}</td>
        <td>${formatMoney(p.cuota_diaria)}</td>
        <td>${formatMoney(p.monto_pagado)}</td>
        <td><span class="badge badge-info">${p.metodo_pago || 'efectivo'}</span></td>
        <td>${p.fecha_pago}</td>
        <td>${p.cobrador || '-'}</td>
        <td>
          <button class="btn btn-primary btn-sm" onclick="verComprobantePago(${p.id})" title="Ver comprobante">
            <i class="fas fa-print"></i>
          </button>
        </td>
      </tr>
    `).join('');
      } catch (error) {
        console.error('Error cargando pagos:', error);
      }
    }

    async function cargarPrestamosSelect() {
      try {
        const response = await fetch('/php/obtener_prestamos.php');
        const prestamos = await response.json();

        const select = document.getElementById('prestamo_pago');

        const prestamosActivos = prestamos.filter(p => p.estado === 'activo');

        if (prestamosActivos.length === 0) {
          select.innerHTML = '<option value="">No hay préstamos activos</option>';
          return;
        }

        select.innerHTML = '<option value="">-- Seleccione --</option>' +
          prestamosActivos.map(p =>
            `<option value="${p.id}" data-cuota="${p.cuota_diaria}">${p.cliente_nombre} - Préstamo #${p.id} - Cuota: ${formatMoney(p.cuota_diaria)}</option>`
          ).join('');

        // Auto-llenar monto cuando se selecciona un préstamo
        select.addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          const cuota = selectedOption.getAttribute('data-cuota');
          if (cuota) {
            document.getElementById('monto_pagado').value = cuota;
          }
        });
      } catch (error) {
        console.error('Error cargando préstamos:', error);
      }
    }

    document.getElementById('formPago').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/registrar_pago.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          const tipoPago = data.tipo_pago === 'completo' ? 'CUOTA COMPLETA' : 'PAGO PARCIAL';
          const montoPagado = parseFloat(formData.get('monto_pagado'));

          closeModal('modalPago');

          await cargarPagos();
          await cargarEstadisticas();

          Swal.fire({
            icon: 'success',
            title: '¡Pago Registrado!',
            html: `
              <div style="text-align: left; padding: 10px;">
                <p><strong>Tipo:</strong> <span style="color: ${tipoPago === 'CUOTA COMPLETA' ? '#10b981' : '#f59e0b'};">${tipoPago}</span></p>
                <p><strong>Monto:</strong> ${formatMoney(montoPagado)}</p>
                <p><strong>Saldo Restante:</strong> ${formatMoney(data.nuevo_saldo)}</p>
              </div>
            `,
            confirmButtonColor: '#667eea',
            timer: 5000
          });
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        Swal.fire('Error', 'No se pudo registrar el pago', 'error');
      }
    });

    // SECCIÓN USUARIOS - CRUD COMPLETO
    async function cargarUsuarios() {
      try {
        const response = await fetch('/php/obtener_usuarios.php');
        const text = await response.text();

        let usuarios;
        try {
          usuarios = JSON.parse(text);
        } catch (e) {
          console.error('Respuesta no JSON:', text);
          document.getElementById('usuariosTable').innerHTML =
            '<tr><td colspan="4" style="text-align: center; color: red;">Error: Respuesta inválida del servidor</td></tr>';
          return;
        }

        const tbody = document.getElementById('usuariosTable');

        if (usuarios.success === false) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No tienes permisos para ver usuarios</td></tr>';
          return;
        }

        if (!usuarios || usuarios.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No hay usuarios registrados</td></tr>';
          return;
        }

        tbody.innerHTML = usuarios.map(u => `
      <tr>
        <td>${u.nombre}</td>
        <td>${u.correo}</td>
        <td><span class="badge badge-${u.rol === 'admin' ? 'danger' : 'info'}">${u.rol}</span></td>
        <td>
          <button class="btn btn-primary btn-sm" onclick="abrirEditarUsuario(${u.id})" title="Editar">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(${u.id})" title="Eliminar">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `).join('');
      } catch (error) {
        console.error('Error cargando usuarios:', error);
        document.getElementById('usuariosTable').innerHTML =
          '<tr><td colspan="4" style="text-align: center; color: red;">Error al cargar usuarios</td></tr>';
      }
    }

    // Registrar nuevo usuario desde admin
    document.getElementById('formUsuario').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/registrar_usuario.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          closeModal('modalUsuario');
          cargarUsuarios();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo registrar el usuario', 'error');
      }
    });

    // Abrir modal para editar usuario
    async function abrirEditarUsuario(usuarioId) {
      try {
        const response = await fetch('/php/obtener_usuarios.php');
        const usuarios = await response.json();

        const usuario = usuarios.find(u => u.id == usuarioId);

        if (!usuario) {
          Swal.fire('Error', 'Usuario no encontrado', 'error');
          return;
        }

        document.getElementById('editUsuarioId').value = usuario.id;
        document.getElementById('editUsuarioNombre').value = usuario.nombre;
        document.getElementById('editUsuarioCorreo').value = usuario.correo;
        document.getElementById('editUsuarioRol').value = usuario.rol;
        document.getElementById('editUsuarioClave').value = '';

        openModal('modalEditarUsuario');
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar el usuario', 'error');
      }
    }

    // Guardar cambios de usuario editado
    document.getElementById('formEditarUsuario').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/editar_usuario.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          closeModal('modalEditarUsuario');
          cargarUsuarios();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo actualizar el usuario', 'error');
      }
    });

    // Eliminar usuario
    function eliminarUsuario(usuarioId) {
      Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('usuario_id', usuarioId);

          try {
            const response = await fetch('/php/eliminar_usuario.php', {
              method: 'POST',
              body: formData
            });
            const data = await response.json();

            if (data.success) {
              Swal.fire('Eliminado', data.message, 'success');
              cargarUsuarios();
            } else {
              Swal.fire('Error', data.message, 'error');
            }
          } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo eliminar el usuario', 'error');
          }
        }
      });
    }

    function guardarConfiguracion() {
      const interes = document.getElementById('interes_defecto').value;
      const dias = document.getElementById('dias_defecto').value;
      const gracia = document.getElementById('dias_gracia').value;
      const mora = document.getElementById('mora_diaria').value;

      // Guardar en localStorage para uso futuro
      localStorage.setItem('config_interes_defecto', interes);
      localStorage.setItem('config_dias_defecto', dias);
      localStorage.setItem('config_dias_gracia', gracia);
      localStorage.setItem('config_mora_diaria', mora);

      Swal.fire({
        icon: 'success',
        title: 'Configuración Guardada',
        text: 'Los parámetros se han guardado correctamente',
        timer: 2000
      });
    }

    function cargarConfiguracion() {
      const interes = localStorage.getItem('config_interes_defecto');
      const dias = localStorage.getItem('config_dias_defecto');
      const gracia = localStorage.getItem('config_dias_gracia');
      const mora = localStorage.getItem('config_mora_diaria');

      if (interes) document.getElementById('interes_defecto').value = interes;
      if (dias) document.getElementById('dias_defecto').value = dias;
      if (gracia) document.getElementById('dias_gracia').value = gracia;
      if (mora) document.getElementById('mora_diaria').value = mora;
    }

    function formatMoney(amount) {
      return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
      }).format(amount);
    }

    document.addEventListener('DOMContentLoaded', function() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('fecha_inicio').value = today;
      document.getElementById('fechaPago').value = today;

      // Cargar configuración
      cargarConfiguracion();

      // Cargar datos iniciales
      cargarEstadisticas();
      cargarClientes();
    });

    // Configuración de columnas para cada tabla
    const tableRules = {
      clientes: {
        table: "#tabla-clientes",
        hideOrder: [1, 2, 3], // columnas que se ocultan progresivamente
        keepVisible: [4] // columna Acciones
      },
      pagos: {
        table: "#pagosTable",
        hideOrder: [1, 2, 4, 5, 6],
        keepVisible: [7]
      },
      pendientes: {
        table: "#tabla-pendientes",
        hideOrder: [2, 5, 6, 7],
        keepVisible: [8]
      }
    };

    // Función general para ocultar columnas
    function applyResponsiveTables() {
      const width = window.innerWidth;

      Object.keys(tableRules).forEach(key => {
        const config = tableRules[key];
        const table = document.querySelector(config.table);

        if (!table) return;

        const rows = table.querySelectorAll("tr");

        // Mostrar todas primero
        rows.forEach(row => {
          [...row.children].forEach(cell => (cell.style.display = ""));
        });

        // Aplicar reglas según ancho
        if (width < 850) hideColumn(config, rows, 0);
        if (width < 700) hideColumn(config, rows, 1);
        if (width < 550) hideColumn(config, rows, 2);
        if (width < 450) hideColumn(config, rows, 3);
      });
    }

    function hideColumn(config, rows, index) {
      const col = config.hideOrder[index];
      if (col === undefined) return;

      rows.forEach(row => {
        const cell = row.children[col];
        if (cell && !config.keepVisible.includes(col)) {
          cell.style.display = "none";
        }
      });
    }

    window.addEventListener("resize", applyResponsiveTables);
    window.addEventListener("DOMContentLoaded", applyResponsiveTables);

    // Convertir "Carlos Ricardo Sánchez Jiménez" → "Carlos Sánchez"
    function abreviarNombre(nombre) {
      let partes = nombre.trim().split(" ");
      if (partes.length >= 2) {
        return partes[0] + " " + partes[partes.length - 1];
      }
      return nombre;
    }

    // Aplicar abreviación en las tablas
    function abreviarNombresEnTabla(selector, colIndex) {
      document.querySelectorAll(selector).forEach(row => {
        let cell = row.children[colIndex];
        if (cell) {
          cell.setAttribute("data-abbr", "1");
          cell.textContent = abreviarNombre(cell.textContent);
        }
      });
    }

    // Llamar después de cargar datos
    setTimeout(() => {
      abreviarNombresEnTabla("#tabla-clientes tr", 1);
      abreviarNombresEnTabla("#pagosTable tr", 0);
      abreviarNombresEnTabla("#tabla-pendientes tr", 1);
    }, 1000);
  </script>
</body>

</html>