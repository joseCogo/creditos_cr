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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <link href="/css/admin.css" rel="stylesheet">

</head>

<body>
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
          <span>Pagos</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('reportes')">
          <i class="fas fa-chart-line"></i>
          <span>Reportes</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('reportes-caja')">
          <i class="fas fa-file-invoice-dollar"></i>
          <span>Reportes de Caja</span>
        </a>
      </li>
      <li class="menu-item">
        <a class="menu-link" onclick="showSection('usuarios')">
          <i class="fas fa-user-shield"></i>
          <span>Usuarios</span>
        </a>
      </li>
    </ul>
  </aside>

  <main class="main-content" id="mainContent">
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

    <div class="content">
      <section id="dashboard" class="section active">
        <div class="cards-grid">
          <div class="card saldo-card">
            <div class="card-header-flex">
              <div class="card-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-wallet"></i>
              </div>
              <button class="btn-add-saldo" onclick="openModal('modalAgregarSaldo')" title="Agregar saldo">
                <i class="fas fa-plus-circle"></i>
              </button>
            </div>
            <div class="card-title">Saldo Disponible</div>
            <div class="card-value" id="saldo-disponible">$0</div>
            <div class="card-footer">
              <i class="fas fa-hand-holding-usd" style="color: #667eea;"></i>
              <span>Capital disponible en caja</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon danger">
                <i class="fas fa-hand-holding-usd"></i>
              </div>
            </div>
            <div class="card-title">Total Prestado (Capital)</div>
            <div class="card-value" id="total-prestado">$0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-up" style="color: var(--danger-color);"></i>
              <span>Dinero en la calle</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon success">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
            <div class="card-title">Ganancias Totales</div>
            <div class="card-value" id="total-ganancias">$0</div>
            <div class="card-footer">
              <i class="fas fa-chart-line" style="color: var(--success-color);"></i>
              <span>Intereses cobrados</span>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div class="card-icon info">
                <i class="fas fa-user-check"></i>
              </div>
            </div>
            <div class="card-title">Clientes Activos</div>
            <div class="card-value" id="clientes-activos">0</div>
            <div class="card-footer">
              <i class="fas fa-users"></i>
              <span>Con deuda pendiente</span>
            </div>
          </div>
        </div>
      </section>

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

      <section id="prestamos" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3>Gestión de Préstamos</h3>
            <div class="search-box">
              <input type="text" class="search-input" id="buscarPrestamo"
                placeholder="Buscar por cliente..."
                onkeyup="filtrarPrestamos()">
              <select class="search-input" id="filtroPrestamos" onchange="cargarPrestamos()">
                <option value="">Todos</option>
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
                  <th>Cédula</th>
                  <th>Cliente</th>
                  <th>Capital Prestado</th>
                  <th>Interés</th>
                  <th>Saldo Pendiente</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="prestamosTable">
                <tr>
                  <td colspan="7" style="text-align: center;">Cargando préstamos...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="pagos" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3><i class="fas fa-history"></i> Historial de Pagos</h3>
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
                  <th>Monto Pagado</th>
                  <th>Método</th>
                  <th>Fecha Pago</th>
                  <th>Cobrador</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="pagosTable">
                <tr>
                  <td colspan="6" style="text-align: center;">Cargando pagos...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="reportes" class="section">
        <!-- Tarjetas de Resumen -->
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

        <!-- Tabla de Actividad -->
        <div class="table-container">
          <div class="table-header">
            <h3><i class="fas fa-calendar-alt"></i> Resumen de Actividad - Últimos 7 Días</h3>
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

        <!-- GRÁFICOS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">

          <div class="table-container" style="padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 1.1rem;">
              <i class="fas fa-chart-bar"></i> Distribución de Capital
            </h3>
            <div style="position: relative; height: 300px; width: 100%;">
              <canvas id="graficoCapital"></canvas>
            </div>
          </div>

          <div class="table-container" style="padding: 20px;">
            <h3 style="margin-bottom: 15px; font-size: 1.1rem;">
              <i class="fas fa-chart-line"></i> Ingresos Últimos 7 Días
            </h3>
            <div style="position: relative; height: 300px; width: 100%;">
              <canvas id="graficoIngresos7dias"></canvas>
            </div>
          </div>
        </div>

        <div class="table-container" style="padding: 20px; margin-top: 20px; width: 100%; max-width: 500px; margin-left: auto; margin-right: auto;">
          <h3 style="margin-bottom: 15px; text-align: center; font-size: 1.1rem;">
            <i class="fas fa-chart-pie"></i> Estado de Préstamos
          </h3>
          <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="graficoEstadoPrestamos"></canvas>
          </div>
        </div>

        <section id="reportes-caja" class="section">
          <div class="table-container">
            <div class="table-header">
              <h3><i class="fas fa-file-invoice-dollar"></i> Movimientos de Caja</h3>
              <div class="search-box">
                <select class="search-input" id="tipoReporteCaja" onchange="cargarReporteCaja()">
                  <option value="diario">Diario</option>
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="personalizado">Personalizado</option>
                </select>

                <div id="fechasPersonalizadas" style="display: none; gap: 10px;">
                  <input type="date" class="search-input" id="fechaInicioCaja">
                  <input type="date" class="search-input" id="fechaFinCaja">
                </div>

                <button class="btn btn-primary" onclick="cargarReporteCaja()">
                  <i class="fas fa-sync"></i> Generar
                </button>
              </div>
            </div>

            <div class="cards-grid" style="margin: 20px 0;">
              <div class="card">
                <div class="card-title">Saldo Inicial</div>
                <div class="card-value" id="reporteSaldoInicial" style="color: #6b7280;">$0</div>
              </div>
              <div class="card">
                <div class="card-title">Ingresos</div>
                <div class="card-value" id="reporteTotalIngresos" style="color: #10b981;">$0</div>
              </div>
              <div class="card">
                <div class="card-title">Egresos</div>
                <div class="card-value" id="reporteTotalEgresos" style="color: #ef4444;">$0</div>
              </div>
              <div class="card">
                <div class="card-title">Saldo Final</div>
                <div class="card-value" id="reporteSaldoFinal" style="color: #667eea;">$0</div>
              </div>
            </div>

            <div id="contenedorReporteCaja">
              <p style="text-align: center; padding: 40px; color: #6b7280;">Selecciona un reporte</p>
            </div>
          </div>
        </section>

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
                    <td colspan="4" style="text-align: center;">Cargando usuarios...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
    </div>
  </main>

  <div class="modal" id="modalCliente">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Cliente</h3>
        <button class="close-modal" onclick="closeModal('modalCliente')"><i class="fas fa-times"></i></button>
      </div>
      <form class="form-grid" id="formCliente">
        <div class="form-group">
          <label>Cédula *</label>
          <input type="text" name="cedula" required>
        </div>
        <div class="form-group">
          <label>Nombre Completo *</label>
          <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
          <label>Teléfono *</label>
          <input type="tel" name="telefono" required>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Dirección *</label>
          <input type="text" name="direccion" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="correo">
        </div>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
          <button type="submit" class="btn btn-success" style="width: 100%;">Guardar Cliente</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalEditarCliente">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Editar Cliente</h3>
        <button class="close-modal" onclick="closeModal('modalEditarCliente')"><i class="fas fa-times"></i></button>
      </div>
      <form class="form-grid" id="formEditarCliente">
        <div class="form-group">
          <label>Cédula</label>
          <input type="text" name="cedula" id="editCedula" readonly style="background-color: #e5e7eb; cursor: not-allowed;">
        </div>
        <div class="form-group">
          <label>Nombre Completo *</label>
          <input type="text" name="nombre" id="editNombre" required>
        </div>
        <div class="form-group">
          <label>Teléfono *</label>
          <input type="tel" name="telefono" id="editTelefono" required>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Dirección *</label>
          <input type="text" name="direccion" id="editDireccion" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="correo" id="editCorreo">
        </div>
        <div style="grid-column: 1 / -1; margin-top: 20px;">
          <button type="submit" class="btn btn-primary" style="width: 100%;">Actualizar Cliente</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalPrestamo">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Préstamo Simple</h3>
        <button class="close-modal" onclick="closeModal('modalPrestamo')"><i class="fas fa-times"></i></button>
      </div>

      <form class="form-grid" id="formPrestamo">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label><i class="fas fa-search"></i> Buscar Cliente</label>
          <input type="text" id="buscarClienteModal" class="search-input" onkeyup="filtrarClientesModal()" placeholder="Nombre o Cédula...">
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <select name="cliente_id" id="cliente_id" required size="5" style="width: 100%; border: 1px solid #ddd; padding: 5px;">
            <option value="">-- Seleccione un cliente --</option>
          </select>
        </div>

        <div class="form-group">
          <label>Monto a Prestar (Capital) *</label>
          <input type="number" name="monto" id="monto" required min="1" placeholder="Ej: 200000">
        </div>

        <div class="form-group">
          <label>Interés Inicial (%) *</label>
          <input type="number" name="interes" id="interes" required min="0" value="20">
          <small>El 20% de ganancia inicial.</small>
        </div>

        <div style="grid-column: 1 / -1; background: #f0f9ff; padding: 15px; border-radius: 8px;">
          <p style="margin: 0;"><strong>Total Deuda Inicial:</strong> <span id="spanTotal" style="font-size: 1.2em; color: #047857; font-weight: bold;">$0</span></p>
        </div>

        <div style="grid-column: 1 / -1; margin-top: 20px;">
          <button type="submit" class="btn btn-success" style="width: 100%;">Crear Préstamo</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalDetallePrestamo">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Detalle del Préstamo</h3>
        <button class="close-modal" onclick="closeModal('modalDetallePrestamo')"><i class="fas fa-times"></i></button>
      </div>
      <div id="contenidoDetallePrestamo" style="padding: 20px;">
        Cargando...
      </div>
    </div>
  </div>

  <div class="modal" id="modalPago">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Registrar Pago</h3>
        <button class="close-modal" onclick="closeModal('modalPago')"><i class="fas fa-times"></i></button>
      </div>
      <form class="form-grid" id="formPago">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Seleccionar Préstamo *</label>
          <select name="prestamo_id" id="prestamo_pago" required style="width: 100%; padding: 10px;">
            <option value="">-- Cargar préstamos activos --</option>
          </select>
        </div>
        <div class="form-group">
          <label>Monto a Pagar *</label>
          <input type="number" name="monto_pagado" id="monto_pagado" required min="1">
        </div>
        <div class="form-group">
          <label>Método</label>
          <select name="metodo_pago">
            <option value="efectivo">Efectivo</option>
            <option value="nequi">Nequi</option>
            <option value="daviplata">Daviplata</option>
          </select>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Observación</label>
          <input type="text" name="observacion" placeholder="Opcional...">
        </div>
        <div style="grid-column: 1 / -1; margin-top: 10px;">
          <button type="submit" class="btn btn-success" style="width: 100%;">Registrar Pago</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalAgregarSaldo">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Agregar Saldo a Caja</h3>
        <button class="close-modal" onclick="closeModal('modalAgregarSaldo')"><i class="fas fa-times"></i></button>
      </div>
      <form class="form-grid" id="formAgregarSaldo">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Monto a Agregar *</label>
          <input type="number" name="monto" required min="1">
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Concepto</label>
          <textarea name="concepto" required>Ingreso de capital</textarea>
        </div>
        <div style="grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="width: 100%;">Agregar Saldo</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="modalUsuario">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuevo Usuario</h3>
        <button class="close-modal" onclick="closeModal('modalUsuario')"><i class="fas fa-times"></i></button>
      </div>
      <form class="form-grid" id="formUsuario">
        <div class="form-group">
          <label>Nombre *</label>
          <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="correo" required>
        </div>
        <div class="form-group">
          <label>Clave *</label>
          <input type="password" name="clave" required>
        </div>
        <div class="form-group">
          <label>Rol *</label>
          <select name="rol" required>
            <option value="empleado">Empleado</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div style="grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="width: 100%;">Crear Usuario</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // --- LÓGICA DE INTERFAZ Y NAVEGACIÓN ---
    function cerrarSesion() {
      Swal.fire({
        title: '¿Salir?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, salir'
      }).then((r) => {
        if (r.isConfirmed) window.location.href = '/php/logout.php';
      });
    }

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      if (window.innerWidth <= 850) {
        sidebar.classList.toggle('active');
      } else {
        sidebar.classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
      }
    }

    function showSection(id) {
      document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
      document.getElementById(id).classList.add('active');

      // Cargar datos según sección
      if (id === 'clientes') cargarClientes();
      if (id === 'prestamos') cargarPrestamos();
      if (id === 'pagos') cargarPagos();
      if (id === 'usuarios') cargarUsuarios();
      if (id === 'dashboard') cargarEstadisticas();
      if (id === 'reportes') cargarReportes();
    }

    // --- MODALES Y HELPERS ---
    function openModal(id) {
      document.getElementById(id).classList.add('active');
      if (id === 'modalPrestamo') {
        cargarClientesSelect();
        obtenerSaldoCaja();
      }
      if (id === 'modalPago') {
        cargarPrestamosSelect();
      }
    }

    function closeModal(id) {
      document.getElementById(id).classList.remove('active');
      // Reseteos específicos
      if (id === 'modalPrestamo') {
        document.getElementById('formPrestamo').reset();
        document.getElementById('spanTotal').textContent = '$0';
      }
      if (id === 'modalPago') document.getElementById('formPago').reset();
    }

    function formatMoney(amount) {
      return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
      }).format(amount);
    }

    function obtenerFechaActual() {
      return new Date().toISOString().split('T')[0];
    }
  </script>

  <script>
    // 1. Calcular TOTAL en vivo (Monto + Interés)
    document.addEventListener('DOMContentLoaded', () => {
      const inputMonto = document.getElementById('monto');
      const inputInteres = document.getElementById('interes');

      function actualizarTotal() {
        const monto = parseFloat(inputMonto.value) || 0;
        const interes = parseFloat(inputInteres.value) || 0;
        const total = monto + (monto * (interes / 100));
        document.getElementById('spanTotal').textContent = formatMoney(total);
      }

      if (inputMonto && inputInteres) {
        inputMonto.addEventListener('input', actualizarTotal);
        inputInteres.addEventListener('input', actualizarTotal);
      }
    });

    // 2. Variables Globales
    let saldoDisponibleCaja = 0;
    let clientesModalData = [];
    let prestamosActivosData = [];

    // 3. Obtener Saldo Caja
    async function obtenerSaldoCaja() {
      try {
        const res = await fetch('/php/obtener_saldo.php');
        const data = await res.json();
        saldoDisponibleCaja = data.success ? parseFloat(data.saldo) : 0;
      } catch (e) {
        saldoDisponibleCaja = 0;
      }
    }

    // 4. Registrar Préstamo (Lógica Simple)
    document.getElementById('formPrestamo').addEventListener('submit', async function(e) {
      e.preventDefault();
      const monto = parseFloat(document.getElementById('monto').value);

      if (monto > saldoDisponibleCaja) {
        Swal.fire('Saldo Insuficiente', `En caja solo hay ${formatMoney(saldoDisponibleCaja)}`, 'error');
        return;
      }

      const formData = new FormData(this);

      try {
        const res = await fetch('/php/registrar_prestamo.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          Swal.fire('¡Éxito!', data.message, 'success');
          closeModal('modalPrestamo');
          cargarPrestamos();
          cargarEstadisticas();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error(error);
      }
    });

    // 5. Cargar Clientes en Select
    async function cargarClientesSelect() {
      const res = await fetch('/php/obtener_cliente.php');
      clientesModalData = await res.json();
      renderizarClientesModal(clientesModalData);
    }

    function renderizarClientesModal(lista) {
      const select = document.getElementById('cliente_id');
      select.innerHTML = '<option value="">-- Seleccione --</option>' +
        lista.map(c => `<option value="${c.id}">${c.nombre} - ${c.cedula}</option>`).join('');
    }

    function filtrarClientesModal() {
      const txt = document.getElementById('buscarClienteModal').value.toLowerCase();
      const filtrados = clientesModalData.filter(c => c.nombre.toLowerCase().includes(txt) || c.cedula.includes(txt));
      renderizarClientesModal(filtrados);
    }
  </script>

  <script>
    // 1. Ver Detalle (Versión Limpia)
    async function verDetallePrestamo(id) {
      try {
        const res = await fetch(`/php/obtener_detalle_prestamo.php?id=${id}`);
        const data = await res.json();

        if (!data.success) return Swal.fire('Error', data.message, 'error');

        const p = data.prestamo;
        const pagos = data.pagos || [];

        let htmlPagos = '<ul style="list-style:none; padding:0; max-height:200px; overflow-y:auto;">';
        pagos.forEach(pg => {
          htmlPagos += `
                <li style="padding: 8px; border-bottom:1px solid #eee; font-size:13px;">
                    <strong>${pg.fecha_pago}</strong>: ${formatMoney(pg.monto_pagado)} (${pg.metodo_pago})
                </li>`;
        });
        htmlPagos += '</ul>';

        document.getElementById('contenidoDetallePrestamo').innerHTML = `
                <div style="text-align:left;">
                    <div style="background:#f9fafb; padding:10px; border-radius:5px; margin-bottom:10px;">
                        <h4 style="margin:0;">${p.cliente_nombre}</h4>
                        <p style="margin:0; font-size:12px; color:#666;">${p.cedula} - ${p.telefono}</p>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                        <div style="background:#f0fdf4; padding:10px; border-radius:5px;">
                            <small>Capital Prestado</small>
                            <div style="font-weight:bold; color:#166534;">${formatMoney(p.monto)}</div>
                        </div>
                        <div style="background:#fef2f2; padding:10px; border-radius:5px;">
                            <small>Saldo Pendiente</small>
                            <div style="font-weight:bold; color:#991b1b; font-size:1.2em;">${formatMoney(p.saldo_pendiente)}</div>
                        </div>
                    </div>

                    <p><strong>Deuda Total Inicial:</strong> ${formatMoney(p.monto_total)} (${p.interes}% Interés)</p>
                    <p><strong>Fecha Inicio:</strong> ${p.fecha_inicio}</p>
                    <p><strong>Último Movimiento:</strong> ${p.fecha_fin}</p>
                    
                    <button class="btn btn-warning" style="width:100%; margin:10px 0;" onclick="abrirModalMulta(${p.id})">
                        <i class="fas fa-plus-circle"></i> Agregar Interés / Multa Manual
                    </button>

                    <h5 style="margin-top:15px; border-top:1px solid #eee; padding-top:10px;">Historial Pagos</h5>
                    ${pagos.length ? htmlPagos : '<p style="color:#999;">Sin pagos aún</p>'}
                </div>
            `;
        openModal('modalDetallePrestamo');
      } catch (e) {
        console.error(e);
      }
    }

    // 2. Función para Agregar Multa/Interés Manual
    // REEMPLAZAR la función abrirModalMulta en admin.php

    async function abrirModalMulta(id) {
      const {
        value: monto
      } = await Swal.fire({
        title: 'Agregar Interés Manual',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p style="margin-bottom: 15px; color: #666; font-size: 14px;">
                    <i class="fas fa-plus-circle"></i> 
                    Este monto se <strong>sumará</strong> a la deuda del cliente
                </p>
                
                <input id="swal-monto" 
                       type="number" 
                       class="swal2-input" 
                       placeholder="Ej: 50000"
                       min="1000"
                       step="1000"
                       style="width: 90%; font-size: 18px;">
            </div>
        `,
        width: '400px',
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#f59e0b',
        preConfirm: () => {
          const valor = document.getElementById('swal-monto').value;

          if (!valor || parseFloat(valor) <= 0) {
            Swal.showValidationMessage('Ingrese un monto válido');
            return false;
          }

          return valor;
        }
      });

      if (!monto) return;

      // Enviar directamente
      const formData = new FormData();
      formData.append('prestamo_id', id);
      formData.append('monto_extra', monto);

      try {
        const res = await fetch('/php/agregar_interes.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          Swal.fire({
            title: '¡Actualizado!',
            html: `
                    <div style="text-align: left; padding: 10px;">
                        <p style="font-size: 14px; color: #666; margin-bottom: 10px;">
                            <strong>${data.datos.cliente}</strong>
                        </p>
                        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin: 10px 0;">
                            <p style="margin: 5px 0; font-size: 16px;">
                                <strong>Monto agregado:</strong> 
                                <span style="color: #f59e0b; font-size: 20px; font-weight: bold;">
                                    ${formatMoney(data.datos.monto_agregado)}
                                </span>
                            </p>
                        </div>
                        <div style="background: #fee2e2; padding: 15px; border-radius: 8px;">
                            <p style="margin: 5px 0;"><strong>Nueva deuda total:</strong> ${formatMoney(data.datos.nuevo_total)}</p>
                            <p style="margin: 5px 0; font-size: 18px;">
                                <strong>Saldo pendiente:</strong> 
                                <span style="color: #dc2626; font-weight: bold;">
                                    ${formatMoney(data.datos.nuevo_saldo)}
                                </span>
                            </p>
                        </div>
                    </div>
                `,
            icon: 'success',
            confirmButtonColor: '#10b981'
          });

          // Cerrar modal de detalle y recargar
          closeModal('modalDetallePrestamo');
          cargarPrestamos();
          cargarEstadisticas();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
      }
    }

    // --- 3. GESTIÓN DE PRÉSTAMOS (TABLA PRINCIPAL) ---
    async function cargarPrestamos() {
      try {
        const estado = document.getElementById('filtroPrestamos').value;
        // Asegúrate de que este archivo devuelva el JSON correcto con los campos nuevos
        const res = await fetch(`/php/obtener_prestamos.php?estado=${estado}`);
        const data = await res.json();

        // Guardamos en variable global para filtrar localmente
        prestamosData = Array.isArray(data) ? data : [];
        renderizarPrestamos(prestamosData);
      } catch (error) {
        console.error(error);
        document.getElementById('prestamosTable').innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error al cargar datos</td></tr>';
      }
    }

    function renderizarPrestamos(lista) {
      const tbody = document.getElementById('prestamosTable');

      // Validación de seguridad
      if (!Array.isArray(lista)) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error al cargar datos</td></tr>';
        return;
      }

      if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#888;">No se encontraron préstamos</td></tr>';
        return;
      }

      tbody.innerHTML = lista.map(p => `
            <tr>
                <td>${p.cliente_cedula}</td>
                <td style="font-weight:600;">${p.cliente_nombre}</td>
                <td style="color:#166534;">${formatMoney(p.monto)}</td>
                <td>${p.interes}%</td>
                <td style="font-weight:700; color:${p.saldo_pendiente > 0 ? '#dc2626' : '#16a34a'};">
                    ${formatMoney(p.saldo_pendiente)}
                </td>
                <td>
                    <span class="badge badge-${p.estado === 'activo' ? 'success' : 'secondary'}">
                        ${p.estado.toUpperCase()}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="verDetallePrestamo(${p.id})" title="Ver Detalle y Pagos">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function filtrarPrestamos() {
      const texto = document.getElementById('buscarPrestamo').value.toLowerCase();
      const filtrados = prestamosData.filter(p =>
        p.cliente_nombre.toLowerCase().includes(texto) ||
        p.cliente_cedula.includes(texto)
      );
      renderizarPrestamos(filtrados);
    }

    // --- 4. GESTIÓN DE PAGOS ---
    async function cargarPagos() {
      try {
        const fecha = document.getElementById('fechaPago').value;
        const url = fecha ? `/php/obtener_pagos.php?fecha=${fecha}` : '/php/obtener_pagos.php';

        const res = await fetch(url);
        pagosData = await res.json();
        renderizarPagos(pagosData);
      } catch (error) {
        console.error(error);
      }
    }

    function renderizarPagos(lista) {
      const tbody = document.getElementById('pagosTable');

      // Validación de seguridad para evitar errores "map is not a function"
      if (!Array.isArray(lista)) {
        console.error("Datos inválidos en renderizarPagos:", lista);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Error al cargar datos</td></tr>';
        return;
      }

      if (lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">No hay pagos registrados</td></tr>';
        return;
      }

      tbody.innerHTML = lista.map(pg => `
        <tr>
            <td style="font-weight:600;">${pg.cliente_nombre}</td>
            <td style="color:#16a34a; font-weight:bold;">${formatMoney(pg.monto_pagado)}</td>
            <td><span class="badge badge-info">${pg.metodo_pago || 'efectivo'}</span></td>
            <td>${pg.fecha_pago}</td>
            <td style="font-size:12px; color:#666;">${pg.cobrador || 'Admin'}</td>
            <td>
                <button class="btn btn-sm btn-secondary" onclick="verComprobantePago(${pg.id})" title="Ver comprobante">
                    <i class="fas fa-print"></i>
                </button>
            </td>
        </tr>
      `).join('');
    }

    // --- 5. LOGICA DEL MODAL DE PAGO (CRUCIAL) ---
    async function cargarPrestamosSelect() {
      try {
        // Obtenemos solo los activos para que aparezcan en el select del modal de pago
        const res = await fetch('/php/obtener_prestamos.php?estado=activo');
        const data = await res.json();

        const select = document.getElementById('prestamo_pago');
        if (!Array.isArray(data) || data.length === 0) {
          select.innerHTML = '<option value="">No hay préstamos con deuda pendiente</option>';
          return;
        }

        select.innerHTML = '<option value="">-- Seleccione Cliente a Pagar --</option>' +
          data.map(p => `
                    <option value="${p.id}">
                        ${p.cliente_nombre} - Debe: ${formatMoney(p.saldo_pendiente)}
                    </option>
                `).join('');

      } catch (error) {
        console.error(error);
      }
    }

    // Registrar Pago (Submit del Formulario)
    document.getElementById('formPago').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const res = await fetch('/php/registrar_pago.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          Swal.fire('Pago Registrado', `Nuevo saldo: ${formatMoney(data.nuevo_saldo)}`, 'success');
          closeModal('modalPago');
          cargarPagos(); // Recargar tabla pagos
          cargarEstadisticas(); // Actualizar caja en dashboard
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (e) {
        console.error(e);
        Swal.fire('Error', 'No se pudo registrar el pago', 'error');
      }
    });

    // 1. VARIABLES GLOBALES DE CLIENTES (Importante: deben ir antes de las funciones)
    let clientesData = [];
    let clientesPaginaActual = 1;
    const clientesPorPagina = 10; // <--- Aquí definimos la variable que te faltaba

    // 2. CARGAR CLIENTES
    async function cargarClientes() {
      try {
        const res = await fetch('/php/obtener_cliente.php');
        const data = await res.json();

        // Validar que sea un array
        clientesData = Array.isArray(data) ? data : [];

        // Renderizar
        renderizarClientes(clientesData);
      } catch (error) {
        console.error('Error al cargar clientes:', error);
      }
    }

    // 3. RENDERIZAR TABLA (Con Modal corregido y Paginación)
    function renderizarClientes(lista) {
      const tbody = document.getElementById('clientesTable');

      if (!lista || lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay clientes registrados</td></tr>';
        return;
      }

      // A. Filtrar
      const busqueda = document.getElementById('buscarCliente')?.value.toLowerCase() || '';
      const clientesFiltrados = lista.filter(c =>
        c.nombre.toLowerCase().includes(busqueda) ||
        c.cedula.includes(busqueda)
      );

      // B. Calcular Paginación
      const totalPaginas = Math.ceil(clientesFiltrados.length / clientesPorPagina);

      // Ajustar página actual si se sale del rango
      if (clientesPaginaActual > totalPaginas && totalPaginas > 0) {
        clientesPaginaActual = totalPaginas;
      } else if (clientesPaginaActual < 1) {
        clientesPaginaActual = 1;
      }

      const inicio = (clientesPaginaActual - 1) * clientesPorPagina;
      const fin = inicio + clientesPorPagina;
      const clientesPagina = clientesFiltrados.slice(inicio, fin);

      // C. Generar HTML
      tbody.innerHTML = clientesPagina.map(c => `
            <tr>
                <td>${c.cedula}</td>
                <td>${c.nombre}</td>
                <td>${c.telefono}</td>
                <td>${c.direccion}</td>
                <td>${c.correo || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="abrirEditarCliente('${c.cedula}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarCliente('${c.cedula}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');

      // D. Renderizar controles de paginación
      renderizarPaginacionClientes(totalPaginas, clientesFiltrados.length);
    }

    // 4. CONTROLES DE PAGINACIÓN
    function renderizarPaginacionClientes(totalPaginas, totalRegistros) {
      const container = document.querySelector('#clientes .table-container');
      let paginacionDiv = container.querySelector('.pagination-controls');

      if (!paginacionDiv) {
        paginacionDiv = document.createElement('div');
        paginacionDiv.className = 'pagination-controls';
        paginacionDiv.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;';
        container.appendChild(paginacionDiv);
      }

      if (totalPaginas <= 1) {
        paginacionDiv.innerHTML = `<div style="color: #6b7280; font-size: 14px;">Total: ${totalRegistros} clientes</div>`;
        return;
      }

      paginacionDiv.innerHTML = `
            <div style="color: #6b7280; font-size: 14px;">
                Página ${clientesPaginaActual} de ${totalPaginas} (Total: ${totalRegistros})
            </div>
            <div style="display: flex; gap: 5px;">
                <button class="btn btn-sm btn-primary" 
                        onclick="cambiarPaginaClientes(${clientesPaginaActual - 1})"
                        ${clientesPaginaActual === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
                <button class="btn btn-sm btn-primary" 
                        onclick="cambiarPaginaClientes(${clientesPaginaActual + 1})"
                        ${clientesPaginaActual === totalPaginas ? 'disabled' : ''}>
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        `;
    }

    // 5. CAMBIAR PÁGINA
    function cambiarPaginaClientes(nuevaPagina) {
      // Validamos rango antes de asignar
      const totalPaginas = Math.ceil(clientesData.length / clientesPorPagina); // Calculo rápido sobre total global (aprox)
      if (nuevaPagina >= 1) {
        clientesPaginaActual = nuevaPagina;
        renderizarClientes(clientesData);
      }
    }

    // 6. FILTRAR
    function filtrarClientes() {
      clientesPaginaActual = 1; // Resetear a pag 1 al buscar
      renderizarClientes(clientesData);
    }

    // 7. ABRIR MODAL EDITAR
    async function abrirEditarCliente(cedula) {
      try {
        // Buscamos el cliente específico
        const response = await fetch(`/php/obtener_cliente.php?cedula=${cedula}`);
        const cliente = await response.json();

        if (!cliente) {
          Swal.fire('Error', 'No se encontraron datos del cliente', 'error');
          return;
        }

        // Llenar el formulario del modal
        // Asegúrate de que los ID coincidan con tu HTML
        document.getElementById('editCedula').value = cliente.cedula;
        document.getElementById('editNombre').value = cliente.nombre;
        document.getElementById('editTelefono').value = cliente.telefono;
        document.getElementById('editDireccion').value = cliente.direccion;
        document.getElementById('editCorreo').value = cliente.correo || '';

        openModal('modalEditarCliente');
      } catch (error) {
        console.error(error);
        Swal.fire('Error', 'No se pudo cargar la información del cliente', 'error');
      }
    }

    // 8. GUARDAR CAMBIOS DE EDICIÓN (Submit)
    const formEditar = document.getElementById('formEditarCliente');
    if (formEditar) {
      formEditar.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
          const res = await fetch('/php/editar_cliente.php', {
            method: 'POST',
            body: formData
          });
          const data = await res.json();

          if (data.success) {
            Swal.fire('Actualizado', data.message, 'success');
            closeModal('modalEditarCliente');
            cargarClientes(); // Recargar la tabla
          } else {
            Swal.fire('Error', data.message, 'error');
          }
        } catch (error) {
          console.error(error);
          Swal.fire('Error', 'Error de conexión', 'error');
        }
      });
    }

    // 9. ELIMINAR CLIENTE
    function eliminarCliente(cedula) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esto y se borrará el historial del cliente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const formData = new FormData();
            formData.append('cedula', cedula);

            const res = await fetch('/php/eliminar_cliente.php', {
              method: 'POST',
              body: formData
            });
            const data = await res.json();

            if (data.success) {
              Swal.fire('Eliminado', 'El cliente ha sido eliminado.', 'success');
              cargarClientes(); // Recargar tabla
            } else {
              Swal.fire('Error', data.message, 'error');
            }
          } catch (error) {
            console.error(error);
            Swal.fire('Error', 'No se pudo eliminar', 'error');
          }
        }
      });
    }

    // 10. REGISTRAR NUEVO CLIENTE (Submit)
    const formCrear = document.getElementById('formCliente');
    if (formCrear) {
      formCrear.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
          const res = await fetch('/php/registrar_cliente.php', {
            method: 'POST',
            body: formData
          });
          const data = await res.json();

          if (data.success) {
            Swal.fire('Guardado', 'Cliente registrado correctamente', 'success');
            closeModal('modalCliente');
            this.reset();
            cargarClientes(); // Recargar tabla
          } else {
            Swal.fire('Error', data.message, 'error');
          }
        } catch (error) {
          console.error(error);
          Swal.fire('Error', 'No se pudo registrar el cliente', 'error');
        }
      });
    }

    // --- 11. ESTADÍSTICAS Y REPORTES ---
    async function cargarEstadisticas() {
      try {
        // Asegúrate de tener este archivo php creado según la lógica nueva
        const res = await fetch('/php/obtener_estadisticas.php');
        const data = await res.json();

        document.getElementById('saldo-disponible').textContent = formatMoney(data.saldo_disponible || 0);
        document.getElementById('total-prestado').textContent = formatMoney(data.total_prestado || 0);
        document.getElementById('total-ganancias').textContent = formatMoney(data.total_ganancias || 0);
        document.getElementById('clientes-activos').textContent = data.clientes_activos || 0;

      } catch (error) {
        console.error("Error cargando stats:", error);
      }
    }

    // Lógica para Reportes de Caja (Filtros)
    const selectReporte = document.getElementById('tipoReporteCaja');
    if (selectReporte) {
      selectReporte.addEventListener('change', function() {
        const divFechas = document.getElementById('fechasPersonalizadas');
        if (this.value === 'personalizado') divFechas.style.display = 'flex';
        else divFechas.style.display = 'none';
      });
    }

    async function cargarReporteCaja() {
      const tipo = document.getElementById('tipoReporteCaja').value;
      let url = `/php/obtener_reportes_caja.php?tipo=${tipo}`;

      if (tipo === 'personalizado') {
        const ini = document.getElementById('fechaInicioCaja').value;
        const fin = document.getElementById('fechaFinCaja').value;
        if (!ini || !fin) return Swal.fire('Atención', 'Selecciona ambas fechas', 'warning');
        url += `&fecha_inicio=${ini}&fecha_fin=${fin}`;
      }

      try {
        const res = await fetch(url);
        const data = await res.json();

        if (!data.success) return Swal.fire('Error', 'No se pudo generar reporte', 'error');

        // Actualizar resumen caja
        document.getElementById('reporteSaldoInicial').textContent = formatMoney(data.saldo_inicial);
        document.getElementById('reporteTotalIngresos').textContent = formatMoney(data.total_ingresos);
        document.getElementById('reporteTotalEgresos').textContent = formatMoney(data.total_egresos);
        document.getElementById('reporteSaldoFinal').textContent = formatMoney(data.saldo_final);

        // Renderizar tabla simple de movimientos
        let html = '<ul style="list-style:none; padding:0;">';
        if (data.movimientos_por_fecha) {
          data.movimientos_por_fecha.forEach(grupo => {
            html += `<li style="background:#eee; padding:5px 10px; font-weight:bold; margin-top:10px;">${grupo.fecha}</li>`;
            grupo.movimientos.forEach(m => {
              const color = m.tipo === 'ingreso' ? 'green' : 'red';
              const signo = m.tipo === 'ingreso' ? '+' : '-';
              html += `
                            <li style="padding:10px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between;">
                                <span>${m.concepto}</span>
                                <strong style="color:${color};">${signo}${formatMoney(m.monto)}</strong>
                            </li>
                        `;
            });
          });
        }
        html += '</ul>';
        document.getElementById('contenedorReporteCaja').innerHTML = html;

      } catch (e) {
        console.error(e);
      }
    }

    // --- 12. GESTIÓN DE USUARIOS ---
    async function cargarUsuarios() {
      try {
        const res = await fetch('/php/obtener_usuarios.php'); // Asegúrate de crear este PHP
        const usuarios = await res.json();
        const tbody = document.getElementById('usuariosTable');

        if (usuarios.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay usuarios</td></tr>';
          return;
        }

        tbody.innerHTML = usuarios.map(u => `
                <tr>
                    <td>${u.nombre}</td>
                    <td>${u.correo}</td>
                    <td><span class="badge badge-${u.rol === 'admin' ? 'primary' : 'secondary'}">${u.rol}</span></td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="alert('Eliminar usuario ID: ${u.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
      } catch (e) {
        console.error(e);
      }
    }

    document.getElementById('formUsuario').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      try {
        const res = await fetch('/php/registrar_usuario.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          Swal.fire('Éxito', 'Usuario creado', 'success');
          closeModal('modalUsuario');
          cargarUsuarios();
          this.reset();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (e) {
        console.error(e);
      }
    });

    // --- 13. INICIALIZACIÓN FINAL ---
    document.addEventListener('DOMContentLoaded', () => {
      // Establecer fecha de hoy en el filtro de pagos
      const hoy = obtenerFechaActual();
      const fechaPagoInput = document.getElementById('fechaPago');
      if (fechaPagoInput) fechaPagoInput.value = hoy;

      // Cargar datos iniciales
      cargarEstadisticas();
      cargarClientes(); // Precarga clientes para tener la lista lista
      cargarPrestamos(); // Precarga préstamos
    });

    async function verComprobantePago(pago_id) {
      try {
        const res = await fetch(`/php/generar_comprobante.php?id=${pago_id}`);
        const data = await res.json();

        if (!data.success) {
          Swal.fire('Error', data.message || 'No se pudo generar el comprobante', 'error');
          return;
        }

        // Crear HTML del comprobante
        const htmlComprobante = `
      <div style="font-family: Arial; max-width: 400px; margin: 0 auto; text-align: left;">
        <div style="text-align: center; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px 10px 0 0;">
          <h2 style="margin: 0;">Créditos CR</h2>
          <p style="margin: 5px 0; font-size: 14px;">Comprobante de Pago</p>
          <h3 style="margin: 10px 0;">#${data.numero_comprobante}</h3>
        </div>

        <div style="background: #f9fafb; padding: 20px; border: 2px solid #667eea; border-top: none; border-radius: 0 0 10px 10px;">
          
          <!-- Tipo de Pago -->
          <div style="text-align: center; margin-bottom: 15px;">
            <span style="background: ${data.tipo_pago === 'CANCELACIÓN' ? '#10b981' : '#3b82f6'}; 
                         color: white; padding: 8px 20px; border-radius: 20px; font-weight: bold;">
              ${data.tipo_pago}
            </span>
          </div>

          <!-- Información del Cliente -->
          <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <h4 style="margin: 0 0 10px 0; color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">
              <i class="fas fa-user"></i> Cliente
            </h4>
            <p style="margin: 5px 0;"><strong>Nombre:</strong> ${data.cliente.nombre}</p>
            <p style="margin: 5px 0;"><strong>Cédula:</strong> ${data.cliente.cedula}</p>
            <p style="margin: 5px 0;"><strong>Teléfono:</strong> ${data.cliente.telefono}</p>
          </div>

          <!-- Detalle del Pago -->
          <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <h4 style="margin: 0 0 10px 0; color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">
              <i class="fas fa-money-bill-wave"></i> Detalle del Pago
            </h4>
            <div style="display: flex; justify-content: space-between; margin: 10px 0; font-size: 18px;">
              <strong>Monto Pagado:</strong>
              <span style="color: #10b981; font-weight: bold;">${formatMoney(data.pago.monto)}</span>
            </div>
            <p style="margin: 5px 0;"><strong>Método:</strong> ${data.pago.metodo}</p>
            <p style="margin: 5px 0;"><strong>Fecha:</strong> ${data.fecha}</p>
            ${data.pago.observacion ? `<p style="margin: 5px 0;"><strong>Observación:</strong> ${data.pago.observacion}</p>` : ''}
          </div>

          <!-- Estado del Préstamo -->
          <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <h4 style="margin: 0 0 10px 0; color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px;">
              <i class="fas fa-file-invoice-dollar"></i> Estado del Préstamo
            </h4>
            <p style="margin: 5px 0;"><strong>Préstamo #${data.prestamo.id}</strong></p>
            <p style="margin: 5px 0;">Deuda Total: ${formatMoney(data.prestamo.monto_total)}</p>
            <div style="display: flex; justify-content: space-between; margin: 10px 0; padding: 10px; 
                        background: ${data.prestamo.saldo_pendiente <= 0 ? '#d1fae5' : '#fee2e2'}; 
                        border-radius: 5px;">
              <strong>Saldo Pendiente:</strong>
              <span style="font-size: 18px; font-weight: bold; 
                           color: ${data.prestamo.saldo_pendiente <= 0 ? '#065f46' : '#991b1b'};">
                ${formatMoney(data.prestamo.saldo_pendiente)}
              </span>
            </div>
          </div>

          <!-- Footer -->
          <div style="text-align: center; padding-top: 15px; border-top: 1px dashed #d1d5db;">
            <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">
              <strong>Cobrador:</strong> ${data.cobrador}
            </p>
            <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">
              ¡Gracias por su pago!
            </p>
          </div>
        </div>
      </div>
    `;

        // Mostrar en modal
        Swal.fire({
          html: htmlComprobante,
          width: '500px',
          showCloseButton: true,
          showCancelButton: false,
          confirmButtonText: '<i class="fas fa-print"></i> Imprimir',
          confirmButtonColor: '#667eea'
        }).then((result) => {
          if (result.isConfirmed) {
            imprimirComprobante(htmlComprobante, data.numero_comprobante);
          }
        });

      } catch (error) {
        console.error('Error al generar comprobante:', error);
        Swal.fire('Error', 'No se pudo cargar el comprobante', 'error');
      }
    }

    function imprimirComprobante(html, numero) {
      // Crear ventana nueva
      const ventana = window.open('', '_blank', 'width=800,height=600');

      // Escribir contenido
      ventana.document.write(`
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Comprobante ${numero}</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        body { 
          font-family: Arial, sans-serif; 
          padding: 20px;
          background: #f3f4f6;
        }
        @media print {
          body { 
            background: white;
            padding: 0;
          }
          .no-print {
            display: none !important;
          }
        }
        .btn-imprimir {
          position: fixed;
          top: 20px;
          right: 20px;
          background: #667eea;
          color: white;
          border: none;
          padding: 12px 24px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 600;
          box-shadow: 0 4px 6px rgba(0,0,0,0.2);
          transition: background 0.2s;
        }
        .btn-imprimir:hover {
          background: #5568d3;
        }
      </style>
    </head>
    <body>
      <button class="btn-imprimir no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir
      </button>
      ${html}
    </body>
    </html>
  `);

      ventana.document.close();

      // Auto-imprimir después de medio segundo
      ventana.onload = function() {
        setTimeout(() => {
          ventana.print();
        }, 500);
      };
    }

    // --- 14. REPORTES DE ACTIVIDAD ---
    let chartCapital = null;
    let chartIngresos = null;
    let chartEstados = null;

    async function cargarReportes() {
      try {
        const res = await fetch('/php/obtener_reportes.php');
        const data = await res.json();

        if (!data.success) {
          console.error('Error al cargar reportes');
          return;
        }

        // 1. ACTUALIZAR TARJETAS DE RESUMEN
        document.getElementById('ingresos-hoy').textContent = formatMoney(data.ingresos_hoy || 0);
        document.getElementById('ingresos-semana').textContent = formatMoney(data.ingresos_semana || 0);
        document.getElementById('ingresos-mes').textContent = formatMoney(data.ingresos_mes || 0);

        // 2. RENDERIZAR TABLA DE ACTIVIDAD
        const tbody = document.getElementById('reportesTable');

        if (!data.actividad || data.actividad.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:20px; color:#888;">No hay actividad registrada</td></tr>';
        } else {
          tbody.innerHTML = data.actividad.map(item => `
        <tr>
          <td>${item.fecha}</td>
          <td style="color: #10b981; font-weight: bold;">${formatMoney(item.total_recaudado)}</td>
          <td style="text-align: center;">
            <span class="badge badge-info">${item.numero_pagos}</span>
          </td>
        </tr>
      `).join('');
        }

        // 3. RENDERIZAR GRÁFICOS
        renderizarGraficoCapital(data.grafico_capital);
        renderizarGraficoIngresos7Dias(data.grafico_ingresos_7dias);
        renderizarGraficoEstados(data.grafico_estados);

      } catch (error) {
        console.error('Error al cargar reportes:', error);
        document.getElementById('reportesTable').innerHTML =
          '<tr><td colspan="3" style="text-align:center; color:red;">Error al cargar datos</td></tr>';
      }
    }

    // ========================================
    // GRÁFICO 1: CAPITAL EN CAJA VS EN LA CALLE
    // ========================================
    function renderizarGraficoCapital(datos) {
      const ctx = document.getElementById('graficoCapital');
      if (!ctx) return;

      // Destruir gráfico anterior si existe
      if (chartCapital) chartCapital.destroy();

      chartCapital = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['En Caja', 'Prestado (Calle)', 'Recuperado'],
          datasets: [{
            label: 'Monto',
            data: [
              datos.en_caja,
              datos.pendiente,
              datos.recuperado
            ],
            backgroundColor: [
              'rgba(102, 126, 234, 0.8)', // Azul - En caja
              'rgba(239, 68, 68, 0.8)', // Rojo - En la calle
              'rgba(16, 185, 129, 0.8)' // Verde - Recuperado
            ],
            borderColor: [
              'rgba(102, 126, 234, 1)',
              'rgba(239, 68, 68, 1)',
              'rgba(16, 185, 129, 1)'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return formatMoney(context.parsed.y);
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '$' + (value / 1000).toFixed(0) + 'k';
                }
              }
            }
          }
        }
      });
    }

    // ========================================
    // GRÁFICO 2: INGRESOS ÚLTIMOS 7 DÍAS
    // ========================================
    function renderizarGraficoIngresos7Dias(datos) {
      const ctx = document.getElementById('graficoIngresos7dias');
      if (!ctx) return;

      if (chartIngresos) chartIngresos.destroy();

      const fechas = datos.map(d => d.fecha);
      const montos = datos.map(d => d.total);

      chartIngresos = new Chart(ctx, {
        type: 'line',
        data: {
          labels: fechas,
          datasets: [{
            label: 'Ingresos Diarios',
            data: montos,
            borderColor: 'rgba(16, 185, 129, 1)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(16, 185, 129, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return formatMoney(context.parsed.y);
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '$' + (value / 1000).toFixed(0) + 'k';
                }
              }
            }
          }
        }
      });
    }

    // ========================================
    // GRÁFICO 3: ESTADO DE PRÉSTAMOS (PIE)
    // ========================================
    function renderizarGraficoEstados(datos) {
      const ctx = document.getElementById('graficoEstadoPrestamos');
      if (!ctx) return;

      if (chartEstados) chartEstados.destroy();

      chartEstados = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Préstamos Activos', 'Préstamos Cancelados'],
          datasets: [{
            data: [datos.activos, datos.cancelados],
            backgroundColor: [
              'rgba(239, 68, 68, 0.8)', // Rojo - Activos
              'rgba(16, 185, 129, 0.8)' // Verde - Cancelados
            ],
            borderColor: [
              'rgba(239, 68, 68, 1)',
              'rgba(16, 185, 129, 1)'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 20,
                font: {
                  size: 14
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const total = datos.activos + datos.cancelados;
                  const percentage = ((value / total) * 100).toFixed(1);
                  return `${label}: ${value} (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    }
  </script>
</body>

</html>