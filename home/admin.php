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
  <!-- jsPDF para exportar a PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

  <!-- SheetJS para exportar a Excel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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
          <!-- CARD: Saldo Disponible -->
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
              <span>Capital disponible</span>
            </div>
          </div>

          <!-- CARD: Total Prestado -->
          <div class="card">
            <div class="card-header">
              <div class="card-icon danger">
                <i class="fas fa-hand-holding-usd"></i>
              </div>
            </div>
            <div class="card-title">Total Prestado</div>
            <div class="card-value" id="total-prestado">$0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-up" style="color: var(--danger-color);"></i>
              <span>Capital en préstamos</span>
            </div>
          </div>

          <!-- CARD: Total Recuperado -->
          <div class="card">
            <div class="card-header">
              <div class="card-icon warning">
                <i class="fas fa-ticket-alt"></i>
              </div>
            </div>
            <div class="card-title">Ganancias Boletas</div>
            <div class="card-value" id="total-recuperado">$0</div>
            <div class="card-footer">
              <i class="fas fa-arrow-down" style="color: var(--warning-color);"></i>
              <span>Descuento primera cuota</span>
            </div>
          </div>

          <!-- CARD: Ganancias -->
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
              <span>Todos los pagos recibidos</span>
            </div>
          </div>

          <!-- CARD: Clientes Activos -->
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
              <span>Con préstamos activos</span>
            </div>
          </div>

          <!-- CARD: Clientes Morosos -->
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
              <input type="text" class="search-input" id="buscarPrestamo"
                placeholder="Buscar por cliente o ID..."
                onkeyup="filtrarPrestamos()">
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
                  <th>Cedula</th>
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

        <div class="table-container" style="margin-bottom: 30px; border-left: 4px solid #f59e0b;">
          <div class="table-header">
            <h3><i class="fas fa-clock" style="color: #f59e0b;"></i> Cuotas Atrasadas</h3>
            <div class="search-box">
              <span style="font-weight: 600; color: #ef4444; margin-right: 10px;">
                Total Pendientes: <span id="total-pendientes-admin">0</span>
              </span>
              <input type="text" class="search-input" id="buscarPendienteAdmin"
                placeholder="Buscar deudor..."
                onkeyup="renderizarPendientesAdmin()">
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Cédula</th>
                  <th>Cliente</th>
                  <th>Frecuencia</th>
                  <th>Valor Cuota</th>
                  <th>Monto en Mora</th>
                  <th>Cuotas Atrasadas</th>
                  <th>Próximo Pago</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tabla-pendientes-admin">
                <tr>
                  <td colspan="7" style="text-align: center;">Cargando cartera vencida...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-container">
          <div class="table-header">
            <h3><i class="fas fa-history"></i> Historial de Pagos Recibidos</h3>
            <div class="search-box">
              <input type="date" class="search-input" id="fechaPago" onchange="cargarPagos()">
              <button class="btn btn-success" onclick="openModal('modalPago')">
                <i class="fas fa-plus"></i> Registrar Pago Manual
              </button>
            </div>
          </div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>N° Boleta</th>
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
        <br>

        <!-- GRÁFICOS -->
        <div class="charts-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-top: 30px;">

          <!-- Gráfico 1: Capital Prestado vs Recuperado -->
          <div class="chart-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3><i class="fas fa-chart-bar"></i> Capital Prestado vs Recuperado</h3>
            </div>
            <canvas id="graficoCapital" style="max-height: 300px;"></canvas>
          </div>

          <!-- Gráfico 2: Ingresos de los últimos 7 días -->
          <div class="chart-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3><i class="fas fa-chart-line"></i> Ingresos Últimos 7 Días</h3>
            </div>
            <canvas id="graficoIngresos7dias" style="max-height: 300px;"></canvas>
          </div>

        </div>

        <!-- Gráfico 3: Estado de Préstamos (ancho completo) -->
        <div class="chart-container" style="margin-top: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3><i class="fas fa-chart-pie"></i> Estado de Préstamos</h3>
          </div>
          <canvas id="graficoEstadoPrestamos" style="max-height: 250px;"></canvas>
        </div>
      </section>


      <!-- Sección Reportes de Caja -->
      <section id="reportes-caja" class="section">
        <div class="table-container">
          <div class="table-header">
            <h3><i class="fas fa-file-invoice-dollar"></i> Reportes de Movimientos de Caja</h3>
            <div class="search-box">
              <select class="search-input" id="tipoReporteCaja" onchange="cargarReporteCaja()">
                <option value="diario">Reporte Diario</option>
                <option value="semanal">Reporte Semanal</option>
                <option value="mensual">Reporte Mensual</option>
                <option value="personalizado">Personalizado</option>
              </select>

              <div id="fechasPersonalizadas" style="display: none; gap: 10px;">
                <input type="date" class="search-input" id="fechaInicioCaja">
                <input type="date" class="search-input" id="fechaFinCaja">
              </div>

              <button class="btn btn-primary" onclick="cargarReporteCaja()">
                <i class="fas fa-sync"></i> Generar
              </button>

              <button class="btn btn-success" onclick="exportarReporteCajaPDF()">
                <i class="fas fa-file-pdf"></i> PDF
              </button>

              <button class="btn" style="background: #10b981; color: white;" onclick="exportarReporteCajaExcel()">
                <i class="fas fa-file-excel"></i> Excel
              </button>
            </div>
          </div>

          <!-- Resumen del Reporte -->
          <div class="cards-grid" style="margin: 20px 0;">
            <div class="card">
              <div class="card-title">Saldo Inicial</div>
              <div class="card-value" id="reporteSaldoInicial" style="color: #6b7280;">$0</div>
            </div>

            <div class="card">
              <div class="card-title">Total Ingresos</div>
              <div class="card-value" id="reporteTotalIngresos" style="color: #10b981;">$0</div>
            </div>

            <div class="card">
              <div class="card-title">Total Egresos</div>
              <div class="card-value" id="reporteTotalEgresos" style="color: #ef4444;">$0</div>
            </div>

            <div class="card">
              <div class="card-title">Saldo Final</div>
              <div class="card-value" id="reporteSaldoFinal" style="color: #667eea;">$0</div>
            </div>
          </div>

          <!-- Tabla de Movimientos -->
          <div id="contenedorReporteCaja">
            <p style="text-align: center; padding: 40px; color: #6b7280;">
              Selecciona un tipo de reporte y haz clic en "Generar"
            </p>
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
        <!-- Campo de búsqueda de clientes -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>
            <i class="fas fa-search"></i> Buscar Cliente
          </label>
          <input type="text" id="buscarClienteModal" class="search-input"
            placeholder="Escribe el nombre o cédula del cliente..."
            onkeyup="filtrarClientesModal()"
            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
        </div>

        <!-- Select de clientes -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="cliente_id">Seleccionar Cliente *</label>
          <select name="cliente_id" id="cliente_id" required size="5"
            style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
            <option value="">-- Seleccione un cliente --</option>
          </select>
          <small style="color: #6b7280; display: block; margin-top: 5px;">
            <i class="fas fa-info-circle"></i> Use el buscador para encontrar el cliente rápidamente
          </small>
        </div>

        <!-- Información del cliente seleccionado -->
        <div id="infoCliente" style="grid-column: 1 / -1; display: none; background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
          <h4 style="margin: 0 0 10px 0; color: #667eea; font-size: 14px;">
            <i class="fas fa-user-circle"></i> Información del Cliente
          </h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 13px;">
            <div>
              <strong>Nombre:</strong>
              <div id="infoNombre" style="color: #374151;">-</div>
            </div>
            <div>
              <strong>Cédula:</strong>
              <div id="infoCedula" style="color: #374151;">-</div>
            </div>
            <div>
              <strong>Teléfono:</strong>
              <div id="infoTelefono" style="color: #374151;">-</div>
            </div>
            <div>
              <strong>Dirección:</strong>
              <div id="infoDireccion" style="color: #374151; grid-column: 1 / -1;">-</div>
            </div>
          </div>
        </div>

        <!-- Campos del préstamo -->
        <div class="form-group">
          <label for="monto">Monto del Préstamo *</label>
          <input type="number" name="monto" id="monto" required min="0" placeholder="500000" onchange="calcularCuota()" oninput="calcularCuota()">
        </div>

        <div class="form-group">
          <label for="interes">Interés Total (%) *</label>
          <input type="number" name="interes" id="interes" required value="20" min="0" max="100" onchange="calcularCuota()" oninput="calcularCuota()">
        </div>

        <div class="form-group">
          <label for="periodicidad">Periodicidad de Pago *</label>
          <select name="periodicidad" id="periodicidad" required onchange="calcularCuota()">
            <option value="diario">Diario</option>
            <option value="semanal">Semanal (cada 7 días)</option>
            <option value="quincenal">Quincenal (cada 15 días)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="cuotas">Número de Cuotas *</label>
          <input type="number" name="cuotas" id="cuotas" required value="30" min="1" onchange="calcularCuota()" oninput="calcularCuota()">
        </div>

        <div class="form-group">
          <label for="fecha_inicio">Fecha de Inicio *</label>
          <input type="date" name="fecha_inicio" id="fecha_inicio" required onchange="calcularCuota()">
        </div>

        <!-- NUEVO: Número de boleta -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="numero_boleta">
            <i class="fas fa-ticket-alt"></i> Número de Boleta/Rifa *
          </label>
          <input type="text" name="numero_boleta" id="numero_boleta" required
            placeholder="Ej: 1234, A-567, etc."
            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
          <small style="color: #6b7280; display: block; margin-top: 5px;">
            <i class="fas fa-info-circle"></i> Ingrese el número de la boleta que se entregará al cliente
          </small>
        </div>

        <!-- RESUMEN DEL PRÉSTAMO CON BOLETAS -->
        <div class="form-group" style="grid-column: 1 / -1; background: #f0f9ff; padding: 15px; border-radius: 8px;">
          <h4 style="margin: 0 0 15px 0; color: #667eea; font-size: 16px;">
            <i class="fas fa-calculator"></i> Resumen del Préstamo
          </h4>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
            <div>
              <div style="font-size: 12px; color: #6b7280;">Monto Total a Pagar</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--primary-color);" id="montoTotal">$0</div>
            </div>
            <input type="hidden" id="cuotaDiaria"></input>
            <!-- <div>
              <div style="font-size: 12px; color: #6b7280;">Cuota Diaria Normal</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--success-color);" id="cuotaDiaria">$0</div>
            </div> -->
            <div>
              <div style="font-size: 12px; color: #6b7280;">Cuota Según Periodicidad</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--warning-color);" id="cuotaPeriodica">$0</div>
            </div>
            <div>
              <div style="font-size: 12px; color: #6b7280;">Fecha de Vencimiento</div>
              <div style="font-size: 20px; font-weight: 700; color: var(--dark-color);" id="fechaVencimiento">--</div>
            </div>
          </div>

          <!-- Información de la primera cuota -->
          <div style="margin-top: 15px; padding: 15px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <h4 style="margin: 0 0 10px 0; color: #f59e0b; font-size: 14px;">
              <i class="fas fa-ticket-alt"></i> Primera Cuota (Automática)
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">

              <div>
                <div style="font-size: 12px; color: #78350f;">Primera Cuota Total</div>
                <div style="font-size: 18px; font-weight: 700; color: #ef4444;" id="primeraCuota">$0</div>
              </div>
              <div>
                <div style="font-size: 12px; color: #78350f;">Monto a Entregar</div>
                <div style="font-size: 18px; font-weight: 700; color: #10b981;" id="montoEntregado">$0</div>
              </div>
            </div>
            <small style="display: block; margin-top: 8px; color: #78350f; font-style: italic;">
              <i class="fas fa-info-circle"></i> Al registrar el préstamo, se descontará automáticamente el valor de la primera cuota
            </small>
          </div>
        </div>

        <!-- Campos ocultos -->
        <input type="hidden" name="cuota_diaria" id="cuota_diaria">
        <input type="hidden" name="fecha_fin" id="fecha_fin">
        <!-- <input type="hidden" name="valor_boleta" id="valor_boleta"> -->

        <!-- Botones -->
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

  <!-- Modal Agregar Saldo -->
  <div class="modal" id="modalAgregarSaldo">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fas fa-wallet"></i> Agregar Saldo a Caja</h3>
        <button class="close-modal" onclick="closeModal('modalAgregarSaldo')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form class="form-grid" id="formAgregarSaldo">
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="montoSaldo">Monto a Agregar *</label>
          <input type="number" name="monto" id="montoSaldo" required placeholder="1000000" min="1" step="0.01">
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label for="conceptoSaldo">Concepto</label>
          <textarea name="concepto" id="conceptoSaldo" rows="3" placeholder="Ej: Capital inicial, Ingreso de efectivo, etc.">Ingreso de capital</textarea>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 20px; grid-column: 1 / -1;">
          <button type="submit" class="btn btn-success" style="flex: 1;">
            <i class="fas fa-check"></i> Agregar Saldo
          </button>
          <button type="button" class="btn btn-danger" onclick="closeModal('modalAgregarSaldo')" style="flex: 1;">
            <i class="fas fa-times"></i> Cancelar
          </button>
        </div>
      </form>

      <!-- Últimos Movimientos -->
      <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <h4 style="margin: 0;">Últimos Movimientos</h4>
          <button class="btn btn-sm" style="background: #6b7280; color: white; padding: 5px 10px;" onclick="cargarMovimientosCaja()">
            <i class="fas fa-sync"></i>
          </button>
        </div>
        <div class="movimientos-container" id="movimientosContainer">
          <p style="text-align: center; color: #6b7280; padding: 20px;">Cargando movimientos...</p>
        </div>
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
        <!-- Campo de búsqueda de préstamos -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>
            <i class="fas fa-search"></i> Buscar Préstamo por Cliente
          </label>
          <input
            type="text"
            id="buscarPrestamoModal"
            class="search-input"
            placeholder="Escribe el nombre o cédula del cliente..."
            onkeyup="filtrarPrestamosModal()"
            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px;">
        </div>

        <!-- Select de préstamos (ahora filtrable) -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label>Seleccionar Préstamo *</label>
          <select name="prestamo_id" id="prestamo_pago" required size="5"
            style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
            <option value="">-- Seleccione un préstamo --</option>
          </select>
          <small style="color: #6b7280; display: block; margin-top: 5px;">
            <i class="fas fa-info-circle"></i> Mostrando solo préstamos activos
          </small>
        </div>

        <!-- Información del préstamo seleccionado -->
        <div id="infoPrestamo" style="grid-column: 1 / -1; display: none; background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
          <h4 style="margin: 0 0 10px 0; color: #667eea; font-size: 14px;">
            <i class="fas fa-info-circle"></i> Información del Préstamo
          </h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 13px;">
            <div>
              <strong>Cliente:</strong>
              <div id="infoPagoCliente" style="color: #374151;">-</div>
            </div>
            <div>
              <strong>Cuota Diaria:</strong>
              <div id="infoCuota" style="color: #10b981; font-weight: 600;">-</div>
            </div>
            <div>
              <strong>Saldo Pendiente:</strong>
              <div id="infoSaldo" style="color: #ef4444; font-weight: 600;">-</div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Monto a Pagar *</label>
          <input type="number" name="monto_pagado" id="monto_pagado" required
            placeholder="20000" min="0" step="0.01">
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
  </script>

  <script>
    function showSection(id) {
      document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
      const section = document.getElementById(id);
      if (section) {
        section.classList.add('active');
      }

      document.querySelectorAll('.menu-link').forEach(link => link.classList.remove('active'));

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
      cargarPendientesAdmin();
      if (id === 'usuarios') cargarUsuarios();
      if (id === 'dashboard') cargarEstadisticas();
      if (id === 'reportes') cargarReportes();
      if (id === 'reportes-caja') {
        // Inicializar fechas
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('fechaInicioCaja').value = hoy;
        document.getElementById('fechaFinCaja').value = hoy;
      }
    }

    function openModal(modalId) {
      document.getElementById(modalId).classList.add('active');

      if (modalId === 'modalPrestamo') {
        cargarClientesSelect();
        obtenerSaldoCaja(); // <-- NUEVO: Cargar saldo al abrir el modal
      }

      if (modalId === 'modalPago') {
        cargarPrestamosSelect();
      }
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


    // FUNCIÓN PARA AGREGAR SALDO
    document.getElementById('formAgregarSaldo').addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      try {
        const response = await fetch('/php/agregar_saldo.php', {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Saldo Agregado!',
            html: `
              <p>Nuevo saldo disponible:</p>
              <h3 style="color: #10b981; font-size: 32px;">${formatMoney(data.nuevo_saldo)}</h3>
            `,
            confirmButtonColor: '#667eea'
          });
          closeModal('modalAgregarSaldo');
          cargarEstadisticas();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo agregar el saldo', 'error');
      }
    });

    // FUNCIÓN PARA CARGAR MOVIMIENTOS DE CAJA
    async function cargarMovimientosCaja() {
      try {
        const response = await fetch('/php/obtener_movimientos_caja.php');
        const movimientos = await response.json();

        const container = document.getElementById('movimientosContainer');

        if (!movimientos || movimientos.length === 0) {
          container.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No hay movimientos registrados</p>';
          return;
        }

        container.innerHTML = movimientos.map(m => `
          <div class="movimiento-item movimiento-${m.tipo}">
            <div class="movimiento-info">
              <div style="font-weight: 600; color: #1f2937;">${m.concepto}</div>
              <div style="font-size: 12px; color: #6b7280; margin-top: 3px;">
                ${m.fecha_movimiento} • ${m.usuario_nombre || 'Sistema'}
              </div>
              ${m.referencia ? `<div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">${m.referencia}</div>` : ''}
            </div>
            <div class="movimiento-monto ${m.tipo}">
              ${m.tipo === 'ingreso' ? '+' : '-'}${formatMoney(m.monto)}
            </div>
          </div>
        `).join('');
      } catch (error) {
        console.error('Error cargando movimientos:', error);
        document.getElementById('movimientosContainer').innerHTML =
          '<p style="text-align: center; color: red; padding: 20px;">Error al cargar movimientos</p>';
      }
    }

    // Mostrar/ocultar campos de fecha personalizada
    document.getElementById('tipoReporteCaja')?.addEventListener('change', function() {
      const fechasDiv = document.getElementById('fechasPersonalizadas');
      if (this.value === 'personalizado') {
        fechasDiv.style.display = 'flex';
      } else {
        fechasDiv.style.display = 'none';
      }
    });
  </script>

  <script>
    // Variable global para almacenar datos del último reporte


    /// FUNCIÓN PARA EXPORTAR A PDF
    async function exportarReporteCajaPDF() {
      if (!ultimoReporteCaja) {
        Swal.fire('Error', 'Primero genera un reporte', 'warning');
        return;
      }

      try {
        const {
          jsPDF
        } = window.jspdf;
        const doc = new jsPDF();

        const data = ultimoReporteCaja;

        // Configuración de colores
        const colorPrimario = [102, 126, 234];
        const colorSecundario = [118, 75, 162];
        const colorVerde = [16, 185, 129];
        const colorRojo = [239, 68, 68];

        // ENCABEZADO
        doc.setFillColor(...colorPrimario);
        doc.rect(0, 0, 210, 40, 'F');

        // Logo o nombre de la empresa
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.setFont(undefined, 'bold');
        doc.text('CRÉDITOS CR', 105, 15, {
          align: 'center'
        });

        doc.setFontSize(12);
        doc.setFont(undefined, 'normal');
        doc.text('Sistema de Gestión de Créditos', 105, 23, {
          align: 'center'
        });

        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text('REPORTE DE MOVIMIENTOS DE CAJA', 105, 33, {
          align: 'center'
        });

        // INFORMACIÓN DEL REPORTE
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');

        let yPos = 50;

        doc.setFont(undefined, 'bold');
        doc.text('Tipo de Reporte:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(data.tipo_reporte.charAt(0).toUpperCase() + data.tipo_reporte.slice(1), 60, yPos);

        yPos += 7;
        doc.setFont(undefined, 'bold');
        doc.text('Período:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(`${data.fecha_inicio} al ${data.fecha_fin}`, 60, yPos);

        yPos += 7;
        doc.setFont(undefined, 'bold');
        doc.text('Fecha de generación:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(new Date().toLocaleString('es-CO'), 60, yPos);

        yPos += 7;
        doc.setFont(undefined, 'bold');
        doc.text('Total de movimientos:', 20, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(data.total_movimientos.toString(), 60, yPos);

        // RESUMEN FINANCIERO
        yPos += 12;
        doc.setFillColor(240, 249, 255);
        doc.rect(15, yPos - 5, 180, 40, 'F');

        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(...colorPrimario);
        doc.text('RESUMEN FINANCIERO', 105, yPos, {
          align: 'center'
        });

        yPos += 10;
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);

        // Saldo Inicial
        doc.setFont(undefined, 'bold');
        doc.text('Saldo Inicial:', 25, yPos);
        doc.setFont(undefined, 'normal');
        doc.text(formatMoney(data.saldo_inicial), 70, yPos);

        // Total Ingresos
        doc.setFont(undefined, 'bold');
        doc.text('Total Ingresos:', 110, yPos);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(...colorVerde);
        doc.text(formatMoney(data.total_ingresos), 155, yPos);

        yPos += 7;
        doc.setTextColor(0, 0, 0);

        // Total Egresos
        doc.setFont(undefined, 'bold');
        doc.text('Total Egresos:', 25, yPos);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(...colorRojo);
        doc.text(formatMoney(data.total_egresos), 70, yPos);

        // Saldo Final
        doc.setTextColor(0, 0, 0);
        doc.setFont(undefined, 'bold');
        doc.text('Saldo Final:', 110, yPos);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(...colorPrimario);
        doc.text(formatMoney(data.saldo_final), 155, yPos);

        yPos += 7;
        doc.setTextColor(0, 0, 0);

        // Balance
        const balanceColor = data.balance >= 0 ? colorVerde : colorRojo;
        doc.setFont(undefined, 'bold');
        doc.text('Balance del Período:', 25, yPos);
        doc.setTextColor(...balanceColor);
        doc.text(formatMoney(data.balance), 70, yPos);

        // TABLA DE MOVIMIENTOS
        yPos += 15;
        doc.setTextColor(0, 0, 0);

        // Preparar datos para la tabla
        const tableData = [];

        data.movimientos_por_fecha.forEach(grupo => {
          // Agregar encabezado de fecha
          tableData.push([{
            content: `📅 ${grupo.fecha} - Ingresos: ${formatMoney(grupo.ingresos)} | Egresos: ${formatMoney(grupo.egresos)}`,
            colSpan: 5,
            styles: {
              fillColor: [102, 126, 234],
              textColor: [255, 255, 255],
              fontStyle: 'bold',
              halign: 'center'
            }
          }]);

          // Agregar movimientos del día
          grupo.movimientos.forEach(mov => {
            const fecha = new Date(mov.fecha_movimiento);
            const hora = fecha.toLocaleTimeString('es-CO', {
              hour: '2-digit',
              minute: '2-digit',
              timeZone: 'America/Bogota'
            });

            const signo = mov.tipo === 'ingreso' ? '+' : '-';
            const montoFormateado = signo + formatMoney(mov.monto);

            tableData.push([
              hora,
              mov.tipo.toUpperCase(),
              mov.concepto,
              mov.referencia || '-',
              {
                content: montoFormateado,
                styles: {
                  textColor: mov.tipo === 'ingreso' ? [16, 185, 129] : [239, 68, 68],
                  fontStyle: 'bold',
                  halign: 'right'
                }
              }
            ]);
          });
        });

        doc.autoTable({
          startY: yPos,
          head: [
            ['Hora', 'Tipo', 'Concepto', 'Referencia', 'Monto']
          ],
          body: tableData,
          theme: 'grid',
          headStyles: {
            fillColor: [118, 75, 162],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center'
          },
          styles: {
            fontSize: 8,
            cellPadding: 3
          },
          columnStyles: {
            0: {
              cellWidth: 20,
              halign: 'center'
            },
            1: {
              cellWidth: 25,
              halign: 'center'
            },
            2: {
              cellWidth: 70
            },
            3: {
              cellWidth: 35,
              halign: 'center'
            },
            4: {
              cellWidth: 35,
              halign: 'right'
            }
          },
          margin: {
            left: 15,
            right: 15
          }
        });

        // PIE DE PÁGINA
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
          doc.setPage(i);
          doc.setFontSize(8);
          doc.setTextColor(128, 128, 128);
          doc.text(
            `Página ${i} de ${pageCount}`,
            105,
            doc.internal.pageSize.height - 10, {
              align: 'center'
            }
          );
          doc.text(
            'Créditos CR - Sistema de Gestión',
            105,
            doc.internal.pageSize.height - 5, {
              align: 'center'
            }
          );
        }

        // Guardar PDF
        const nombreArchivo = `Reporte_Caja_${data.tipo_reporte}_${data.fecha_inicio}_${data.fecha_fin}.pdf`;
        doc.save(nombreArchivo);

        Swal.fire({
          icon: 'success',
          title: '¡PDF Generado!',
          text: `Se ha descargado: ${nombreArchivo}`,
          confirmButtonColor: '#667eea'
        });

      } catch (error) {
        console.error('Error generando PDF:', error);
        Swal.fire('Error', 'No se pudo generar el PDF: ' + error.message, 'error');
      }
    }

    // FUNCIÓN PARA EXPORTAR A EXCEL
    async function exportarReporteCajaExcel() {
      if (!ultimoReporteCaja) {
        Swal.fire('Error', 'Primero genera un reporte', 'warning');
        return;
      }

      try {
        const data = ultimoReporteCaja;

        // Crear un nuevo libro de Excel
        const wb = XLSX.utils.book_new();

        // HOJA 1: RESUMEN
        const resumenData = [
          ['CRÉDITOS CR'],
          ['REPORTE DE MOVIMIENTOS DE CAJA'],
          [],
          ['Tipo de Reporte:', data.tipo_reporte.toUpperCase()],
          ['Período:', `${data.fecha_inicio} al ${data.fecha_fin}`],
          ['Fecha de Generación:', new Date().toLocaleString('es-CO')],
          ['Total de Movimientos:', data.total_movimientos],
          [],
          ['RESUMEN FINANCIERO'],
          ['Saldo Inicial:', data.saldo_inicial],
          ['Total Ingresos:', data.total_ingresos],
          ['Total Egresos:', data.total_egresos],
          ['Saldo Final:', data.saldo_final],
          ['Balance del Período:', data.balance]
        ];

        const wsResumen = XLSX.utils.aoa_to_sheet(resumenData);
        wsResumen['!cols'] = [{
          wch: 25
        }, {
          wch: 20
        }];
        XLSX.utils.book_append_sheet(wb, wsResumen, 'Resumen');

        // HOJA 2: MOVIMIENTOS DETALLADOS
        const movimientosData = [
          ['Fecha', 'Hora', 'Tipo', 'Concepto', 'Referencia', 'Usuario', 'Monto']
        ];

        data.movimientos.forEach(mov => {
          const fecha = new Date(mov.fecha_movimiento);
          const fechaStr = fecha.toLocaleDateString('es-CO');
          const horaStr = fecha.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'America/Bogota'
          });

          movimientosData.push([
            fechaStr,
            horaStr,
            mov.tipo.toUpperCase(),
            mov.concepto,
            mov.referencia || '-',
            mov.usuario_nombre || 'Sistema',
            parseFloat(mov.monto)
          ]);
        });

        const wsMovimientos = XLSX.utils.aoa_to_sheet(movimientosData);
        wsMovimientos['!cols'] = [{
            wch: 12
          }, {
            wch: 10
          }, {
            wch: 12
          },
          {
            wch: 40
          }, {
            wch: 20
          }, {
            wch: 20
          }, {
            wch: 15
          }
        ];
        XLSX.utils.book_append_sheet(wb, wsMovimientos, 'Movimientos Detallados');

        // HOJA 3: RESUMEN POR FECHA
        const porFechaData = [
          ['Fecha', 'Total Ingresos', 'Total Egresos', 'Balance', 'Cantidad de Movimientos']
        ];

        data.movimientos_por_fecha.forEach(grupo => {
          const balance = grupo.ingresos - grupo.egresos;
          porFechaData.push([
            grupo.fecha,
            parseFloat(grupo.ingresos),
            parseFloat(grupo.egresos),
            balance,
            grupo.movimientos.length
          ]);
        });

        porFechaData.push([]);
        porFechaData.push([
          'TOTALES',
          parseFloat(data.total_ingresos),
          parseFloat(data.total_egresos),
          parseFloat(data.balance),
          data.total_movimientos
        ]);

        const wsPorFecha = XLSX.utils.aoa_to_sheet(porFechaData);
        wsPorFecha['!cols'] = [{
          wch: 12
        }, {
          wch: 18
        }, {
          wch: 18
        }, {
          wch: 18
        }, {
          wch: 25
        }];
        XLSX.utils.book_append_sheet(wb, wsPorFecha, 'Resumen por Fecha');

        // Generar y descargar
        const nombreArchivo = `Reporte_Caja_${data.tipo_reporte}_${data.fecha_inicio}_${data.fecha_fin}.xlsx`;
        XLSX.writeFile(wb, nombreArchivo);

        Swal.fire({
          icon: 'success',
          title: '¡Excel Generado!',
          html: `
        <p>Se ha descargado: <strong>${nombreArchivo}</strong></p>
        <p style="margin-top: 10px; font-size: 14px; color: #6b7280;">
          El archivo contiene 3 hojas:<br>
          📊 Resumen<br>
          📋 Movimientos Detallados<br>
          📅 Resumen por Fecha
        </p>
      `,
          confirmButtonColor: '#667eea'
        });

      } catch (error) {
        console.error('Error generando Excel:', error);
        Swal.fire('Error', 'No se pudo generar el archivo Excel: ' + error.message, 'error');
      }
    }

    // Variables globales para los gráficos
    let graficoCapital = null;
    let graficoIngresos = null;
    let graficoEstado = null;

    // FUNCIÓN PARA CREAR GRÁFICO CAPITAL PRESTADO VS RECUPERADO
    async function crearGraficoCapital() {
      const ctx = document.getElementById('graficoCapital');
      if (!ctx) return;

      try {
        const response = await fetch('/php/obtener_estadisticas.php');
        const data = await response.json();

        // Destruir gráfico anterior si existe
        if (graficoCapital) {
          graficoCapital.destroy();
        }

        graficoCapital = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Capital Prestado', 'Capital Recuperado', 'Saldo Disponible'],
            datasets: [{
              label: 'Monto ($)',
              data: [
                data.total_prestado || 0,
                data.total_recuperado || 0,
                data.saldo_disponible || 0
              ],
              backgroundColor: [
                'rgba(239, 68, 68, 0.7)', // Rojo para prestado
                'rgba(16, 185, 129, 0.7)', // Verde para recuperado
                'rgba(102, 126, 234, 0.7)' // Azul para disponible
              ],
              borderColor: [
                'rgba(239, 68, 68, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(102, 126, 234, 1)'
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
                    return '$' + (value / 1000000).toFixed(1) + 'M';
                  }
                }
              }
            }
          }
        });
      } catch (error) {
        console.error('Error creando gráfico de capital:', error);
      }
    }

    // FUNCIÓN PARA CREAR GRÁFICO INGRESOS 7 DÍAS
    async function crearGraficoIngresos7Dias() {
      const ctx = document.getElementById('graficoIngresos7dias');
      if (!ctx) return;

      try {
        const response = await fetch('/php/obtener_estadisticas.php');
        const data = await response.json();
        const pagos7dias = data.pagos_7dias || [];

        if (graficoIngresos) {
          graficoIngresos.destroy();
        }

        const fechas = pagos7dias.map(p => p.fecha);
        const montos = pagos7dias.map(p => parseFloat(p.total));

        graficoIngresos = new Chart(ctx, {
          type: 'line',
          data: {
            labels: fechas,
            datasets: [{
              label: 'Ingresos Diarios',
              data: montos,
              borderColor: 'rgba(16, 185, 129, 1)',
              backgroundColor: 'rgba(16, 185, 129, 0.2)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointRadius: 5,
              pointBackgroundColor: 'rgba(16, 185, 129, 1)',
              pointBorderColor: '#fff',
              pointBorderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return 'Ingresos: ' + formatMoney(context.parsed.y);
                  }
                }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return '$' + (value / 1000).toFixed(0) + 'K';
                  }
                }
              }
            }
          }
        });
      } catch (error) {
        console.error('Error creando gráfico de ingresos:', error);
      }
    }

    // FUNCIÓN PARA CREAR GRÁFICO ESTADO DE PRÉSTAMOS
    async function crearGraficoEstadoPrestamos() {
      const ctx = document.getElementById('graficoEstadoPrestamos');
      if (!ctx) return;

      try {
        const response = await fetch('/php/obtener_prestamos.php');
        const prestamos = await response.json();

        const activos = prestamos.filter(p => p.estado === 'activo').length;
        const cancelados = prestamos.filter(p => p.estado === 'cancelado').length;
        const total = prestamos.length;

        if (graficoEstado) {
          graficoEstado.destroy();
        }

        graficoEstado = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Activos', 'Cancelados'],
            datasets: [{
              data: [activos, cancelados],
              backgroundColor: [
                'rgba(102, 126, 234, 0.7)',
                'rgba(16, 185, 129, 0.7)'
              ],
              borderColor: [
                'rgba(102, 126, 234, 1)',
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
                position: 'bottom'
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${label}: ${value} (${percentage}%)`;
                  }
                }
              }
            }
          }
        });
      } catch (error) {
        console.error('Error creando gráfico de estado:', error);
      }
    }
  </script>

  <script>
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

    // Variables globales para paginación de clientes
    let clientesData = [];
    let clientesPaginaActual = 1;
    const clientesPorPagina = 10;

    async function cargarClientes() {
      try {
        const response = await fetch('/php/obtener_cliente.php');
        clientesData = await response.json();

        renderizarClientes();
      } catch (error) {
        console.error('Error cargando clientes:', error);
      }
    }

    function renderizarClientes() {
      const tbody = document.getElementById('clientesTable');

      if (clientesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay clientes registrados</td></tr>';
        return;
      }

      // Aplicar filtro de búsqueda si existe
      const busqueda = document.getElementById('buscarCliente')?.value.toLowerCase() || '';
      const clientesFiltrados = clientesData.filter(cliente =>
        cliente.nombre.toLowerCase().includes(busqueda) ||
        cliente.cedula.toLowerCase().includes(busqueda)
      );

      // Calcular paginación
      const totalPaginas = Math.ceil(clientesFiltrados.length / clientesPorPagina);
      const inicio = (clientesPaginaActual - 1) * clientesPorPagina;
      const fin = inicio + clientesPorPagina;
      const clientesPagina = clientesFiltrados.slice(inicio, fin);

      // Renderizar tabla
      tbody.innerHTML = clientesPagina.map(cliente => `
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

      // Renderizar controles de paginación
      renderizarPaginacionClientes(totalPaginas, clientesFiltrados.length);
    }

    function renderizarPaginacionClientes(totalPaginas, totalRegistros) {
      const container = document.querySelector('#clientes .table-container');

      // Buscar si ya existe el div de paginación
      let paginacionDiv = container.querySelector('.pagination-controls');

      if (!paginacionDiv) {
        paginacionDiv = document.createElement('div');
        paginacionDiv.className = 'pagination-controls';
        paginacionDiv.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;';
        container.appendChild(paginacionDiv);
      }

      if (totalPaginas <= 1) {
        paginacionDiv.innerHTML = `
      <div style="color: #6b7280; font-size: 14px;">
        Total: ${totalRegistros} cliente${totalRegistros !== 1 ? 's' : ''}
      </div>
    `;
        return;
      }

      paginacionDiv.innerHTML = `
    <div style="color: #6b7280; font-size: 14px;">
      Mostrando ${((clientesPaginaActual - 1) * clientesPorPagina) + 1} - ${Math.min(clientesPaginaActual * clientesPorPagina, totalRegistros)} de ${totalRegistros}
    </div>
    <div style="display: flex; gap: 5px;">
      <button 
        class="btn btn-sm btn-primary" 
        onclick="cambiarPaginaClientes(${clientesPaginaActual - 1})"
        ${clientesPaginaActual === 1 ? 'disabled' : ''}
        style="${clientesPaginaActual === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
      >
        <i class="fas fa-chevron-left"></i> Anterior
      </button>
      <span style="padding: 8px 15px; background: white; border-radius: 5px; font-weight: 600;">
        ${clientesPaginaActual} / ${totalPaginas}
      </span>
      <button 
        class="btn btn-sm btn-primary" 
        onclick="cambiarPaginaClientes(${clientesPaginaActual + 1})"
        ${clientesPaginaActual === totalPaginas ? 'disabled' : ''}
        style="${clientesPaginaActual === totalPaginas ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
      >
        Siguiente <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  `;
    }

    function cambiarPaginaClientes(nuevaPagina) {
      const totalPaginas = Math.ceil(clientesData.length / clientesPorPagina);
      if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
        clientesPaginaActual = nuevaPagina;
        renderizarClientes();
      }
    }

    function filtrarClientes() {
      clientesPaginaActual = 1; // Resetear a la primera página al filtrar
      renderizarClientes();
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


    // Variables globales para paginación de préstamos
    let prestamosData = [];
    let prestamosPaginaActual = 1;
    const prestamosPorPagina = 10;

    async function cargarPrestamos() {
      try {
        const filtro = document.getElementById('filtroPrestamos').value;
        let url = '/php/obtener_prestamos.php';
        if (filtro) {
          url += `?estado=${filtro}`;
        }

        const response = await fetch(url);
        const txt = await response.text();
        console.log("RESPUESTA BRUTA:", txt);

        prestamosData = JSON.parse(txt);

        // 🔥 Solución: garantiza que sea un array
        if (!Array.isArray(prestamosData)) {
          console.error("La respuesta no es un array:", prestamosData);
          prestamosData = [];
        }

        prestamosPaginaActual = 1;

        renderizarPrestamos();
      } catch (error) {
        console.error('Error cargando préstamos:', error);
      }
    }


    function renderizarPrestamos() {
      const tbody = document.getElementById('prestamosTable');

      if (prestamosData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No hay préstamos registrados</td></tr>';
        return;
      }

      // Aplicar filtro de búsqueda
      const busqueda = document.getElementById('buscarPrestamo')?.value.toLowerCase() || '';
      const prestamosFiltrados = prestamosData.filter(p =>
        p.cliente_nombre.toLowerCase().includes(busqueda) ||
        p.id.toString().includes(busqueda) ||
        p.cliente_cedula.toLowerCase().includes(busqueda)
      );

      if (prestamosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No se encontraron préstamos</td></tr>';
        return;
      }

      // Calcular paginación
      const totalPaginas = Math.ceil(prestamosFiltrados.length / prestamosPorPagina);
      const inicio = (prestamosPaginaActual - 1) * prestamosPorPagina;
      const fin = inicio + prestamosPorPagina;
      const prestamosPagina = prestamosFiltrados.slice(inicio, fin);

      tbody.innerHTML = prestamosData.map(p => `
  <tr>
    <td>${p.cliente_cedula}</td>
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

      renderizarPaginacionPrestamos(totalPaginas, prestamosFiltrados.length);
    }

    function filtrarPrestamos() {
      prestamosPaginaActual = 1; // Resetear a página 1 al filtrar
      renderizarPrestamos();
    }

    function renderizarPaginacionPrestamos(totalPaginas, totalRegistros) {
      const container = document.querySelector('#prestamos .table-container');

      let paginacionDiv = container.querySelector('.pagination-controls');

      if (!paginacionDiv) {
        paginacionDiv = document.createElement('div');
        paginacionDiv.className = 'pagination-controls';
        paginacionDiv.style.cssText = 'display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;';
        container.appendChild(paginacionDiv);
      }

      if (totalPaginas <= 1) {
        paginacionDiv.innerHTML = `
      <div style="color: #6b7280; font-size: 14px;">
        Total: ${totalRegistros} préstamo${totalRegistros !== 1 ? 's' : ''}
      </div>
    `;
        return;
      }

      paginacionDiv.innerHTML = `
    <div style="color: #6b7280; font-size: 14px;">
      Mostrando ${((prestamosPaginaActual - 1) * prestamosPorPagina) + 1} - ${Math.min(prestamosPaginaActual * prestamosPorPagina, totalRegistros)} de ${totalRegistros}
    </div>
    <div style="display: flex; gap: 5px;">
      <button 
        class="btn btn-sm btn-primary" 
        onclick="cambiarPaginaPrestamos(${prestamosPaginaActual - 1})"
        ${prestamosPaginaActual === 1 ? 'disabled' : ''}
        style="${prestamosPaginaActual === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
      >
        <i class="fas fa-chevron-left"></i> Anterior
      </button>
      <span style="padding: 8px 15px; background: white; border-radius: 5px; font-weight: 600;">
        ${prestamosPaginaActual} / ${totalPaginas}
      </span>
      <button 
        class="btn btn-sm btn-primary" 
        onclick="cambiarPaginaPrestamos(${prestamosPaginaActual + 1})"
        ${prestamosPaginaActual === totalPaginas ? 'disabled' : ''}
        style="${prestamosPaginaActual === totalPaginas ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
      >
        Siguiente <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  `;
    }

    function cambiarPaginaPrestamos(nuevaPagina) {
      const busqueda = document.getElementById('buscarPrestamo')?.value.toLowerCase() || '';
      const prestamosFiltrados = prestamosData.filter(p =>
        p.cliente_nombre.toLowerCase().includes(busqueda) ||
        p.id.toString().includes(busqueda) ||
        p.cliente_cedula.toLowerCase().includes(busqueda)
      );

      const totalPaginas = Math.ceil(prestamosFiltrados.length / prestamosPorPagina);
      if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
        prestamosPaginaActual = nuevaPagina;
        renderizarPrestamos();
      }
    }

    // Reemplazar la función verDetallePrestamo en admin.php

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
        const boleta = data.boleta || null;

        // Calcular valores
        const valorBoleta = boleta ? parseFloat(boleta.valor_boleta) : 0;
        const cuotaDiaria = parseFloat(prestamo.cuota_diaria);
        const primeraCuota = cuotaDiaria + valorBoleta;

        // NUEVO: Calcular ganancia
        const montoPrestado = parseFloat(prestamo.monto);
        const montoTotal = parseFloat(prestamo.monto_total);
        const gananciaTotal = montoTotal - montoPrestado;
        const porcentajeGanancia = montoPrestado > 0 ? ((gananciaTotal / montoPrestado) * 100) : 0;

        // NUEVO: Información financiera del préstamo
        const infoFinanciera = `
            <div style="margin-top: 15px; padding: 15px; background: #ecfdf5; border-radius: 8px; border-left: 4px solid #10b981;">
                <h4 style="margin: 0 0 10px 0; color: #10b981;">
                    <i class="fas fa-chart-line"></i> Información Financiera
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div>
                        <p style="margin: 3px 0; font-size: 12px; color: #065f46;">Monto Prestado:</p>
                        <p style="margin: 3px 0; font-size: 18px; font-weight: 600; color: #047857;">
                            ${formatMoney(montoPrestado)}
                        </p>
                    </div>
                    <div>
                        <p style="margin: 3px 0; font-size: 12px; color: #065f46;">Monto Total a Cobrar:</p>
                        <p style="margin: 3px 0; font-size: 18px; font-weight: 600; color: #059669;">
                            ${formatMoney(montoTotal)}
                        </p>
                    </div>
                    <div style="background: #d1fae5; padding: 10px; border-radius: 5px;">
                        <p style="margin: 3px 0; font-size: 12px; color: #065f46;">
                            <i class="fas fa-hand-holding-usd"></i> Ganancia Total:
                        </p>
                        <p style="margin: 3px 0; font-size: 20px; font-weight: 700; color: #047857;">
                            ${formatMoney(gananciaTotal)}
                        </p>
                        <p style="margin: 3px 0; font-size: 11px; color: #065f46;">
                            (${porcentajeGanancia.toFixed(1)}% de ganancia)
                        </p>
                    </div>
                </div>
            </div>
        `;

        // Botón de marcar ganador (solo si está activo y no ha ganado)
        let botonGanador = '';
        if (prestamo.estado === 'activo' && boleta && !boleta.gano_rifa) {
          botonGanador = `
                <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <h4 style="margin: 0 0 10px 0; color: #f59e0b;">
                        <i class="fas fa-ticket-alt"></i> Sistema de Rifas
                    </h4>
                    <p style="margin: 0 0 10px 0; font-size: 13px; color: #78350f;">
                        Si el cliente ganó la rifa, puede cancelar completamente su préstamo:
                    </p>
                    <button class="btn btn-warning" onclick="marcarGanadorRifa(${prestamoId})" 
                            style="width: 100%;">
                        <i class="fas fa-trophy"></i> Marcar como Ganador de Rifa
                    </button>
                </div>
            `;
        }

        // Info si ya ganó
        let infoGanador = '';
        if (boleta && boleta.gano_rifa) {
          infoGanador = `
                <div style="margin-top: 20px; padding: 15px; background: #d1fae5; border-radius: 8px; border-left: 4px solid #10b981;">
                    <h4 style="margin: 0 0 10px 0; color: #10b981;">
                        <i class="fas fa-trophy"></i> ¡Cliente Ganador de Rifa!
                    </h4>
                    <p style="margin: 5px 0;"><strong>Fecha:</strong> ${boleta.fecha_rifa || 'No registrada'}</p>
                    <p style="margin: 5px 0;"><strong>Observación:</strong> ${boleta.observacion_rifa || '-'}</p>
                </div>
            `;
        }

        // Información de la boleta (ahora incluye número)
        let infoBoleta = '';
        if (boleta && valorBoleta > 0) {
          infoBoleta = `
                <div style="background: #fef3c7; padding: 10px; border-radius: 5px; margin-top: 10px;">
                    <h5 style="margin: 0 0 8px 0; color: #f59e0b;">
                        <i class="fas fa-ticket-alt"></i> Información de Boleta
                    </h5>
                    <p style="margin: 3px 0;"><strong>Número de Boleta:</strong> ${boleta.numero_boleta || 'No registrado'}</p>
                    <p style="margin: 3px 0;"><strong>Valor Boleta:</strong> ${formatMoney(valorBoleta)}</p>
                    <p style="margin: 3px 0;"><strong>Primera Cuota Total:</strong> ${formatMoney(primeraCuota)}</p>
                    <p style="margin: 3px 0;">
                        <strong>Estado:</strong> 
                        ${boleta.boleta_descontada ? 
                            '<span style="color: #10b981;">✓ Descontada el ' + boleta.fecha_descuento + '</span>' : 
                            '<span style="color: #f59e0b;">Pendiente de descuento</span>'
                        }
                    </p>
                </div>
            `;
        }

        let htmlPagos = '';
        if (pagos.length > 0) {
          htmlPagos = '<h4 style="margin-top: 20px;">Historial de Pagos:</h4><div style="max-height: 300px; overflow-y: auto;">';
          pagos.forEach((pago, index) => {
            const esPrimerPago = (index === pagos.length - 1);
            const bgColor = esPrimerPago ? '#fef3c7' : '#f3f4f6';
            htmlPagos += `
                    <div style="border: 1px solid #e5e7eb; padding: 10px; margin: 5px 0; border-radius: 5px; background: ${bgColor};">
                        ${esPrimerPago && valorBoleta > 0 ? '<p style="margin: 0 0 5px 0; color: #f59e0b; font-weight: 600;"><i class="fas fa-ticket-alt"></i> PRIMERA CUOTA (Automática - Boleta + Cuota)</p>' : ''}
                        <p style="margin: 3px 0;"><strong>Fecha:</strong> ${pago.fecha_pago}</p>
                        <p style="margin: 3px 0;"><strong>Monto:</strong> ${formatMoney(pago.monto_pagado)}</p>
                        <p style="margin: 3px 0;"><strong>Método:</strong> ${pago.metodo_pago}</p>
                        ${pago.observacion ? `<p style="margin: 3px 0;"><strong>Observación:</strong> ${pago.observacion}</p>` : ''}
                        ${esPrimerPago && valorBoleta > 0 ? `<p style="margin: 5px 0; font-size: 12px; color: #78350f;"><i class="fas fa-info-circle"></i> Incluye: Boleta ${formatMoney(valorBoleta)} + Cuota ${formatMoney(cuotaDiaria)}</p>` : ''}
                    </div>
                `;
          });
          htmlPagos += '</div>';
        } else {
          htmlPagos = '<p style="color: #888; margin-top: 20px;">No hay pagos registrados</p>';
        }

        document.getElementById('contenidoDetallePrestamo').innerHTML = `
            <div style="text-align: left;">
                <h4>Información del Préstamo #${prestamo.id}</h4>
                
                <!-- Información básica -->
                <div style="background: #f9fafb; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                    <p style="margin: 3px 0;"><strong>Cliente:</strong> ${prestamo.cliente_nombre}</p>
                    <p style="margin: 3px 0;"><strong>Cédula:</strong> ${prestamo.cedula}</p>
                    <p style="margin: 3px 0;"><strong>Teléfono:</strong> ${prestamo.telefono}</p>
                    <p style="margin: 3px 0;"><strong>Dirección:</strong> ${prestamo.direccion}</p>
                </div>

                <!-- Información financiera (NUEVO) -->
                ${infoFinanciera}

                <!-- Detalles del préstamo -->
                <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 5px;">
                    <p style="margin: 3px 0;"><strong>Cuota Diaria:</strong> ${formatMoney(cuotaDiaria)}</p>
                    <p style="margin: 3px 0;"><strong>Número de Cuotas:</strong> ${prestamo.cuotas} días</p>
                    <p style="margin: 3px 0;"><strong>Fecha Inicio:</strong> ${prestamo.fecha_inicio}</p>
                    <p style="margin: 3px 0;"><strong>Fecha Fin:</strong> ${prestamo.fecha_fin}</p>
                    <p style="margin: 3px 0;"><strong>Saldo Pendiente:</strong> 
                        <span style="font-size: 18px; font-weight: 600; color: ${prestamo.saldo_pendiente > 0 ? '#ef4444' : '#10b981'};">
                            ${formatMoney(prestamo.saldo_pendiente)}
                        </span>
                    </p>
                    <p style="margin: 3px 0;"><strong>Estado:</strong> 
                        <span class="badge badge-${
                            prestamo.estado === 'activo' ? 'success' : 
                            prestamo.estado === 'cancelado' ? 'danger' : 
                            'secondary'
                        }">${prestamo.estado.toUpperCase()}</span>
                    </p>
                </div>

                ${infoBoleta}
                ${infoGanador}
                ${botonGanador}
                ${htmlPagos}
            </div>
        `;

        openModal('modalDetallePrestamo');
      } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar el detalle del préstamo', 'error');
      }
    }

    // Variables globales para clientes en modal de préstamo
    let clientesModalData = [];
    // NUEVO: Variable para almacenar el saldo disponible
    let saldoDisponibleCaja = 0;

    async function cargarClientesSelect() {
      try {
        const response = await fetch('/php/obtener_cliente.php');
        const clientes = await response.json();

        // Guardar clientes globalmente
        clientesModalData = clientes;

        if (clientesModalData.length === 0) {
          document.getElementById('cliente_id').innerHTML =
            '<option value="">No hay clientes registrados</option>';
          return;
        }

        // Renderizar todos los clientes inicialmente
        renderizarClientesModal(clientesModalData);

        // Configurar evento de cambio en el select
        document.getElementById('cliente_id').addEventListener('change', function() {
          mostrarInfoCliente(this.value);
        });

        // Limpiar búsqueda al abrir modal
        document.getElementById('buscarClienteModal').value = '';

      } catch (error) {
        console.error('Error cargando clientes:', error);
        document.getElementById('cliente_id').innerHTML =
          '<option value="">Error al cargar clientes</option>';
      }
    }

    function renderizarClientesModal(clientes) {
      const select = document.getElementById('cliente_id');

      if (clientes.length === 0) {
        select.innerHTML = '<option value="">No se encontraron clientes</option>';
        return;
      }

      select.innerHTML = '<option value="">-- Seleccione un cliente --</option>' +
        clientes.map(c => {
          return `<option value="${c.id}" 
                        data-nombre="${c.nombre}"
                        data-cedula="${c.cedula}"
                        data-telefono="${c.telefono || 'N/A'}"
                        data-direccion="${c.direccion || 'N/A'}">
                    ${c.nombre} - ${c.cedula} ${c.telefono ? '(' + c.telefono + ')' : ''}
                </option>`;
        }).join('');
    }

    function filtrarClientesModal() {
      const busqueda = document.getElementById('buscarClienteModal').value.toLowerCase();

      if (!busqueda.trim()) {
        // Si no hay búsqueda, mostrar todos
        renderizarClientesModal(clientesModalData);
        return;
      }

      // Filtrar clientes
      const clientesFiltrados = clientesModalData.filter(c =>
        c.nombre.toLowerCase().includes(busqueda) ||
        c.cedula.toLowerCase().includes(busqueda) ||
        (c.telefono && c.telefono.toLowerCase().includes(busqueda))
      );

      renderizarClientesModal(clientesFiltrados);

      // Si solo hay un resultado, seleccionarlo automáticamente
      if (clientesFiltrados.length === 1) {
        const select = document.getElementById('cliente_id');
        select.selectedIndex = 1; // Seleccionar el primer cliente (después de la opción vacía)
        mostrarInfoCliente(clientesFiltrados[0].id);
      }
    }

    function mostrarInfoCliente(clienteId) {
      const infoDiv = document.getElementById('infoCliente');
      const select = document.getElementById('cliente_id');
      const selectedOption = select.options[select.selectedIndex];

      if (!clienteId || clienteId === '') {
        infoDiv.style.display = 'none';
        return;
      }

      // Obtener datos del option seleccionado
      const nombre = selectedOption.getAttribute('data-nombre');
      const cedula = selectedOption.getAttribute('data-cedula');
      const telefono = selectedOption.getAttribute('data-telefono');
      const direccion = selectedOption.getAttribute('data-direccion');

      // Mostrar información
      document.getElementById('infoNombre').textContent = nombre;
      document.getElementById('infoCedula').textContent = cedula;
      document.getElementById('infoTelefono').textContent = telefono;
      document.getElementById('infoDireccion').textContent = direccion;

      // Mostrar el div de información
      infoDiv.style.display = 'block';
    }

    // Actualizar la función closeModal para incluir limpieza del modal de préstamo
    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');

      if (modalId === 'modalCliente') {
        document.getElementById('formCliente').reset();
      }

      if (modalId === 'modalPrestamo') {
        document.getElementById('formPrestamo').reset();
        document.getElementById('buscarClienteModal').value = '';
        document.getElementById('infoCliente').style.display = 'none';

        // Resetear cálculos
        document.getElementById('montoTotal').textContent = '$0';
        document.getElementById('cuotaDiaria').textContent = '$0';
        document.getElementById('fechaVencimiento').textContent = '--';

        // Recargar todos los clientes
        if (clientesModalData.length > 0) {
          renderizarClientesModal(clientesModalData);
        }
      }

      if (modalId === 'modalPago') {
        document.getElementById('formPago').reset();
        document.getElementById('buscarPrestamoModal').value = '';
        document.getElementById('infoPrestamo').style.display = 'none';

        // Recargar todos los préstamos
        if (prestamosActivosData.length > 0) {
          renderizarPrestamosModal(prestamosActivosData);
        }
      }

      if (modalId === 'modalUsuario') {
        document.getElementById('formUsuario').reset();
      }

      if (modalId === 'modalEditarUsuario') {
        document.getElementById('formEditarUsuario').reset();
      }
    }
  </script>

  <script>
    function calcularCuota() {
      const monto = parseFloat(document.getElementById('monto')?.value) || 0;
      const interes = parseFloat(document.getElementById('interes')?.value) || 0;
      const cuotas = parseInt(document.getElementById('cuotas')?.value) || 0;
      const fechaInicio = document.getElementById('fecha_inicio')?.value;
      const periodicidad = document.getElementById('periodicidad')?.value || 'diario';

      if (monto === 0 || cuotas === 0) return;

      const montoTotal = monto + (monto * (interes / 100));
      let cuotaDiaria = 0;
      let primeracuota = 0;
      let montoentregado = 0;
      let cuotaPeriodica = 0;

      // 1. Determinar el multiplicador de días según la periodicidad
      let multiplicadorDias = 1; // Diario por defecto

      if (periodicidad === 'semanal') {
        multiplicadorDias = 7;
        cuotaPeriodica = montoTotal / cuotas;
        cuotaDiaria = cuotaPeriodica / 7; // Valor referencial diario
      } else if (periodicidad === 'quincenal') {
        multiplicadorDias = 15;
        cuotaPeriodica = montoTotal / cuotas;
        cuotaDiaria = cuotaPeriodica / 15; // Valor referencial diario
      } else {
        // Diario
        multiplicadorDias = 1;
        cuotaPeriodica = montoTotal / cuotas;
        cuotaDiaria = cuotaPeriodica;
      }

      // Cálculos financieros
      primeracuota = cuotaPeriodica; // La primera cuota es el valor del periodo
      montoentregado = montoTotal - primeracuota; // Restamos la primera cuota

      // Actualizar valores en pantalla
      document.getElementById('montoTotal').textContent = formatMoney(montoTotal);
      document.getElementById('cuotaPeriodica').textContent = formatMoney(cuotaPeriodica);
      document.getElementById('primeraCuota').textContent = formatMoney(primeracuota);
      document.getElementById('montoEntregado').textContent = formatMoney(montoentregado);

      // Guardar cuota (usamos la periódica como base para el sistema)
      document.getElementById('cuota_diaria').value = cuotaPeriodica.toFixed(2);

      // 2. CALCULAR FECHA FIN CORRECTA (Corrección solicitada)
      if (fechaInicio && cuotas > 0) {
        const fecha = new Date(fechaInicio + 'T00:00:00');

        // Multiplicamos el número de cuotas por los días del periodo
        const diasTotales = cuotas * multiplicadorDias;

        fecha.setDate(fecha.getDate() + diasTotales);

        const vencimiento = fecha.toISOString().split('T')[0];

        document.getElementById('fechaVencimiento').textContent = vencimiento;
        document.getElementById('fecha_fin').value = vencimiento;
      } else {
        document.getElementById('fechaVencimiento').textContent = '--';
        document.getElementById('fecha_fin').value = '';
      }
    }

    // Función para calcular el valor de la boleta según el monto
    function calcularValorBoleta(monto) {
      const tablaBoletas = {
        100000: 10000,
        200000: 12000,
        300000: 20000,
        400000: 25000,
        500000: 30000,
        600000: 35000,
        700000: 35000,
        800000: 40000,
        900000: 40000,
        1000000: 50000
      };

      // Si el monto está exactamente en la tabla
      if (tablaBoletas[monto]) {
        return tablaBoletas[monto];
      }

      // Si es mayor a 1,000,000, calcular 5%
      if (monto > 1000000) {
        return Math.round(monto * 0.05);
      }

      // Para montos intermedios, interpolar
      const montos = Object.keys(tablaBoletas).map(Number).sort((a, b) => a - b);

      for (let i = 0; i < montos.length - 1; i++) {
        const montoInferior = montos[i];
        const montoSuperior = montos[i + 1];

        if (monto > montoInferior && monto < montoSuperior) {
          const valorInferior = tablaBoletas[montoInferior];
          const valorSuperior = tablaBoletas[montoSuperior];

          const porcentaje = (monto - montoInferior) / (montoSuperior - montoInferior);
          const valorBoleta = valorInferior + ((valorSuperior - valorInferior) * porcentaje);

          return Math.round(valorBoleta);
        }
      }

      // Si es menor al mínimo, 10%
      return Math.round(monto * 0.10);
    }

    document.getElementById('formPrestamo').addEventListener('submit', async function(e) {
      e.preventDefault();

      // Validar que los campos calculados tengan valores
      const fecha_fin = document.getElementById('fecha_fin').value;
      const cuota_diaria = document.getElementById('cuota_diaria').value;
      const monto = parseFloat(document.getElementById('monto')?.value) || 0; // Obtener monto

      if (!fecha_fin || !cuota_diaria || cuota_diaria === '0') {
        Swal.fire({
          icon: 'warning',
          title: 'Datos incompletos',
          text: 'Por favor completa todos los campos y espera a que se calculen las cuotas',
          confirmButtonColor: '#667eea'
        });
        return;
      }

      // ❗ VALIDACIÓN DE SALDO EN FRONTEND
      if (saldoDisponibleCaja < monto) {
        Swal.fire({
          icon: 'error',
          title: 'Saldo Insuficiente en Caja',
          html: `
            <p>El Monto del Préstamo (${formatMoney(monto)}) es mayor que el Saldo Disponible en Caja (${formatMoney(saldoDisponibleCaja)}).</p>
            <p style="color: #ef4444; font-weight: 600;">No se puede registrar el préstamo.</p>
        `,
          confirmButtonColor: '#ef4444'
        });
        return;
      }
      // ❗ FIN VALIDACIÓN DE SALDO

      const formData = new FormData(this);

      // Debug: Ver qué datos se están enviando
      console.log('Datos del formulario:');
      for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
      }

      try {
        const response = await fetch('/php/registrar_prestamo.php', {
          method: 'POST',
          body: formData
        });


        // Obtener el texto completo de la respuesta
        const responseText = await response.text();
        console.log('Respuesta del servidor:', responseText);

        // Intentar parsear como JSON
        let data;
        try {
          data = JSON.parse(responseText);
        } catch (jsonError) {
          console.error('Error parseando JSON:', jsonError);
          console.error('Texto recibido:', responseText);
          throw new Error('La respuesta del servidor no es un JSON válido. Revisa la consola para más detalles.');
        }

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: '¡Préstamo Registrado!',
            html: `
          <div style="text-align: left; padding: 10px;">
            <p><strong>Préstamo #${data.prestamo_id}</strong></p>
            <p><strong>Boleta:</strong> ${data.numero_boleta}</p>
            <p><strong>Valor Boleta:</strong> ${formatMoney(data.valor_boleta)}</p>
            <p><strong>Periodicidad:</strong> ${data.periodicidad}</p>
            <p><strong>Primer Descuento:</strong> ${formatMoney(data.primer_descuento)}</p>
            <p><strong>Monto Entregado:</strong> ${formatMoney(data.monto_entregado)}</p>
            <p><strong>Saldo Inicial:</strong> ${formatMoney(data.saldo_inicial)}</p>
          </div>
        `,
            confirmButtonColor: '#667eea'
          });

          closeModal('modalPrestamo');
          this.reset();
          cargarPrestamos();
          cargarEstadisticas();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message,
            confirmButtonColor: '#ef4444'
          });
        }
      } catch (error) {
        console.error('Error en fetch:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: error.message,
          confirmButtonColor: '#ef4444'
        });
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

            ${data.es_primer_pago && data.boleta ? `
        <!-- Información de la Boleta/Rifa -->
        <div style="margin-bottom: 15px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 5px;">
            <h4 style="margin: 0 0 10px 0; color: #f59e0b;">
                <i class="fas fa-ticket-alt"></i> Descuento por Boleta (Primera Cuota)
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <p style="margin: 5px 0;"><strong>Valor de Boleta:</strong></p>
                    <p style="margin: 5px 0; font-size: 18px; color: #f59e0b; font-weight: 600;">
                        -${formatMoney(data.boleta.valor)}
                    </p>
                </div>
                <div>
                    <p style="margin: 5px 0;"><strong>Cuota Esperada:</strong></p>
                    <p style="margin: 5px 0; font-size: 18px; color: #10b981; font-weight: 600;">
                        ${formatMoney(data.boleta.cuota_esperada)}
                    </p>
                </div>
            </div>
            <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 5px;">
                <p style="margin: 0; font-size: 12px; color: #78350f;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Nota:</strong> Esta es la primera cuota. Se ha aplicado el descuento por boleta/rifa.
                    Las siguientes cuotas serán de ${formatMoney(data.prestamo.cuota_diaria)}
                </p>
            </div>
        </div>
       ` : ''}

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
    <td>${p.numero_boleta || 'N/A'}</td>
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

    // Variables globales para préstamos en modal
    let prestamosActivosData = [];

    async function cargarPrestamosSelect() {
      try {
        const response = await fetch('/php/obtener_prestamos.php');
        const prestamos = await response.json();

        // Guardar solo préstamos activos
        prestamosActivosData = prestamos.filter(p => p.estado === 'activo');

        if (prestamosActivosData.length === 0) {
          document.getElementById('prestamo_pago').innerHTML =
            '<option value="">No hay préstamos activos</option>';
          return;
        }

        // Renderizar todos los préstamos inicialmente
        renderizarPrestamosModal(prestamosActivosData);

        // Configurar evento de cambio en el select
        document.getElementById('prestamo_pago').addEventListener('change', function() {
          mostrarInfoPrestamo(this.value);
        });

        // Limpiar búsqueda al abrir modal
        document.getElementById('buscarPrestamoModal').value = '';

      } catch (error) {
        console.error('Error cargando préstamos:', error);
        document.getElementById('prestamo_pago').innerHTML =
          '<option value="">Error al cargar préstamos</option>';
      }
    }

    function renderizarPrestamosModal(prestamos) {
      const select = document.getElementById('prestamo_pago');

      if (prestamosData.length === 0) {
        select.innerHTML = '<option value="">No se encontraron préstamos</option>';
        return;
      }

      select.innerHTML = '<option value="">-- Seleccione un préstamo --</option>' +
        prestamosData.map(p => {
          const cuota = parseFloat(p.cuota_diaria);
          const saldo = parseFloat(p.saldo_pendiente);
          return `<option value="${p.id}" 
                        data-cuota="${cuota}" 
                        data-saldo="${saldo}"
                        data-cliente="${p.cliente_nombre}"
                        data-cedula="${p.cliente_cedula}">
                    ${p.cliente_nombre} (${p.cliente_cedula}) - Préstamo #${p.id} - Cuota: ${formatMoney(cuota)}
                </option>`;
        }).join('');
    }

    function filtrarPrestamosModal() {
      const busqueda = document.getElementById('buscarPrestamoModal').value.toLowerCase();

      if (!busqueda.trim()) {
        // Si no hay búsqueda, mostrar todos
        renderizarPrestamosModal(prestamosActivosData);
        return;
      }

      // Filtrar préstamos
      const prestamosFiltrados = prestamosActivosData.filter(p =>
        p.cliente_nombre.toLowerCase().includes(busqueda) ||
        p.cliente_cedula.toLowerCase().includes(busqueda) ||
        p.id.toString().includes(busqueda)
      );

      renderizarPrestamosModal(prestamosFiltrados);

      // Si solo hay un resultado, seleccionarlo automáticamente
      if (prestamosFiltrados.length === 1) {
        const select = document.getElementById('prestamo_pago');
        select.selectedIndex = 1; // Seleccionar el primer préstamo (después de la opción vacía)
        mostrarInfoPrestamo(prestamosFiltrados[0].id);
      }
    }

    function mostrarInfoPrestamo(prestamoId) {
      const infoDiv = document.getElementById('infoPrestamo');
      const select = document.getElementById('prestamo_pago');
      const selectedOption = select.options[select.selectedIndex];

      if (!prestamoId || prestamoId === '') {
        infoDiv.style.display = 'none';
        document.getElementById('monto_pagado').value = '';
        return;
      }

      // Obtener datos del option seleccionado
      const cuota = selectedOption.getAttribute('data-cuota');
      const saldo = selectedOption.getAttribute('data-saldo');
      const cliente = selectedOption.getAttribute('data-cliente');
      const cedula = selectedOption.getAttribute('data-cedula');

      // Mostrar información - CORREGIDO: usar 'infoPagoCliente' en lugar de 'infoCliente'
      document.getElementById('infoPagoCliente').textContent = `${cliente} (${cedula})`;
      document.getElementById('infoCuota').textContent = formatMoney(parseFloat(cuota));
      document.getElementById('infoSaldo').textContent = formatMoney(parseFloat(saldo));

      // Auto-llenar monto con la cuota diaria
      document.getElementById('monto_pagado').value = cuota;

      // Mostrar el div de información
      infoDiv.style.display = 'block';
    }

    // Limpiar al cerrar modal
    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('active');

      if (modalId === 'modalPago') {
        document.getElementById('formPago').reset();
        document.getElementById('buscarPrestamoModal').value = '';
        document.getElementById('infoPrestamo').style.display = 'none';

        // Recargar todos los préstamos
        if (prestamosActivosData.length > 0) {
          renderizarPrestamosModal(prestamosActivosData);
        }
      }
    }

    //Registrar nuevo pago
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
          cargarPendientesAdmin();

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

    // Función para obtener el saldo disponible en caja
    async function obtenerSaldoCaja() {
      try {
        const response = await fetch('/php/obtener_saldo.php');
        const data = await response.json();
        if (data.success) {
          saldoDisponibleCaja = parseFloat(data.saldo);
        } else {
          saldoDisponibleCaja = 0;
        }
      } catch (error) {
        saldoDisponibleCaja = 0;
        console.error('Error de conexión al obtener saldo:', error);
      }
    }

    // Función para obtener la fecha actual en formato YYYY-MM-DD (zona horaria local)
    function obtenerFechaActual() {
      const hoy = new Date();
      const year = hoy.getFullYear();
      const month = String(hoy.getMonth() + 1).padStart(2, '0');
      const day = String(hoy.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    async function cargarEstadisticas() {
      try {
        const response = await fetch('/php/obtener_estadisticas.php');
        const data = await response.json();

        document.getElementById('total-prestado').textContent = formatMoney(data.total_prestado);
        document.getElementById('total-recuperado').textContent = formatMoney(data.total_recuperado);
        document.getElementById('total-ganancias').textContent = formatMoney(data.total_ganancias); // NUEVO
        document.getElementById('clientes-activos').textContent = data.clientes_activos;
        document.getElementById('clientes-morosos').textContent = data.clientes_morosos;
        document.getElementById('saldo-disponible').textContent = formatMoney(data.saldo_disponible || 0);

        // Gráficos (si los tienes implementados)
        if (typeof crearGraficoCapital === 'function') {
          crearGraficoCapital();
        }
        if (typeof crearGraficoIngresos7Dias === 'function') {
          crearGraficoIngresos7Dias();
        }
        if (typeof crearGraficoEstadoPrestamos === 'function') {
          crearGraficoEstadoPrestamos();
        }
      } catch (error) {
        console.error('Error cargando estadísticas:', error);
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const today = obtenerFechaActual();
      document.getElementById('fecha_inicio').value = today;
      document.getElementById('fechaPago').value = today;

      // Cargar configuración
      cargarConfiguracion();

      // Cargar datos iniciales
      cargarEstadisticas();
      cargarClientes();
    });

    // Configuración de columnas para cada tabla
    const tableRules = {};

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

    // Función para marcar cliente como ganador de rifa
    async function marcarGanadorRifa(prestamoId) {
      const result = await Swal.fire({
        title: '¿Marcar como ganador de rifa?',
        html: `
            <div style="text-align: left; padding: 10px;">
                <p><strong>⚠️ Esta acción:</strong></p>
                <ul style="margin-left: 20px;">
                    <li>Cancelará completamente el préstamo</li>
                    <li>Establecerá el saldo en $0</li>
                    <li>Marcará al cliente como ganador</li>
                    <li><strong>NO se puede deshacer</strong></li>
                </ul>
                <br>
                <label style="display: block; margin-bottom: 5px;">
                    <strong>Observaciones (opcional):</strong>
                </label>
                <textarea id="observacionRifa" class="swal2-input" 
                          style="width: 100%; height: 80px; resize: vertical;"
                          placeholder="Ej: Ganó en sorteo del día 27/11/2025"></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trophy"></i> Sí, marcarlo como ganador',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          return document.getElementById('observacionRifa').value;
        }
      });

      if (result.isConfirmed) {
        try {
          const formData = new FormData();
          formData.append('prestamo_id', prestamoId);
          formData.append('observacion', result.value || 'Cliente ganador de rifa');

          const response = await fetch('/php/marcar_ganador_rifa.php', {
            method: 'POST',
            body: formData
          });

          const data = await response.json();

          if (data.success) {
            await Swal.fire({
              icon: 'success',
              title: '¡Ganador Registrado!',
              html: `
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-trophy" style="font-size: 48px; color: #f59e0b; margin-bottom: 15px;"></i>
                            <p style="font-size: 18px; margin: 10px 0;">
                                El cliente ha sido marcado como <strong>ganador de la rifa</strong>
                            </p>
                            <p style="color: #10b981; font-weight: 600;">
                                ✓ Préstamo cancelado completamente
                            </p>
                        </div>
                    `,
              confirmButtonColor: '#667eea'
            });

            // Recargar datos
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
    }
  </script>

  <script>
    async function cargarReporteCaja() {
      try {
        const tipoReporte = document.getElementById('tipoReporteCaja').value;
        let url = `/php/obtener_reportes_caja.php?tipo=${tipoReporte}`;

        if (tipoReporte === 'personalizado') {
          const fechaInicio = document.getElementById('fechaInicioCaja').value;
          const fechaFin = document.getElementById('fechaFinCaja').value;

          if (!fechaInicio || !fechaFin) {
            Swal.fire('Error', 'Selecciona las fechas de inicio y fin', 'warning');
            return;
          }

          url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (!data.success) {
          Swal.fire('Error', 'No se pudo cargar el reporte', 'error');
          return;
        }

        // Línea agregada correctamente
        ultimoReporteCaja = data;

        // Actualizar tarjetas
        document.getElementById('reporteSaldoInicial').textContent = formatMoney(data.saldo_inicial);
        document.getElementById('reporteTotalIngresos').textContent = formatMoney(data.total_ingresos);
        document.getElementById('reporteTotalEgresos').textContent = formatMoney(data.total_egresos);
        document.getElementById('reporteSaldoFinal').textContent = formatMoney(data.saldo_final);

        const contenedor = document.getElementById('contenedorReporteCaja');

        if (data.movimientos_por_fecha.length === 0) {
          contenedor.innerHTML = '<p style="text-align: center; padding: 40px; color: #6b7280;">No hay movimientos en este período</p>';
          return;
        }

        let html = `
      <div style="margin-bottom: 20px; padding: 15px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #667eea;">
        <h4 style="margin: 0; color: #667eea;">
          <i class="fas fa-calendar"></i> 
          Reporte ${tipoReporte.charAt(0).toUpperCase() + tipoReporte.slice(1)}
        </h4>
        <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 14px;">
          Período: ${data.fecha_inicio} al ${data.fecha_fin} | 
          Total de movimientos: ${data.total_movimientos} |
          Balance: <strong style="color: ${data.balance >= 0 ? '#10b981' : '#ef4444'}">${formatMoney(data.balance)}</strong>
        </p>
      </div>
    `;

        // INICIO PRIMER foreach
        data.movimientos_por_fecha.forEach(grupo => {
          const balance_dia = grupo.ingresos - grupo.egresos;

          html += `
        <div style="margin-bottom: 25px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
          <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="fas fa-calendar-day"></i> ${grupo.fecha}</div>
            <div style="font-size: 14px;">
              <span style="margin-right: 15px;"><i class="fas fa-arrow-up"></i> ${formatMoney(grupo.ingresos)}</span>
              <span style="margin-right: 15px;"><i class="fas fa-arrow-down"></i> ${formatMoney(grupo.egresos)}</span>
              <span style="font-weight: 700;">Balance: ${formatMoney(balance_dia)}</span>
            </div>
          </div>
          
          <table style="width: 100%; margin: 0;">
            <thead style="background: #f9fafb;">
              <tr>
                <th style="padding: 10px; text-align: left;">Hora</th>
                <th style="padding: 10px; text-align: left;">Tipo</th>
                <th style="padding: 10px; text-align: left;">Concepto</th>
                <th style="padding: 10px; text-align: left;">Cédula</th>
                <th style="padding: 10px; text-align: left;">Referencia</th>
                <th style="padding: 10px; text-align: left;">Usuario</th>
                <th style="padding: 10px; text-align: right;">Monto</th>
              </tr>
            </thead>
            <tbody>
      `;

          // INICIO SEGUNDO foreach
          grupo.movimientos.forEach(mov => {
            const fecha = new Date(mov.fecha_movimiento);
            const hora = fecha.toLocaleTimeString('es-CO', {
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: true,
              timeZone: 'America/Bogota'
            });

            const colorMonto = mov.tipo === 'ingreso' ? '#10b981' : '#ef4444';
            const iconoTipo = mov.tipo === 'ingreso' ? 'fa-arrow-circle-up' : 'fa-arrow-circle-down';

            html += `
          <tr style="border-bottom: 1px solid #f3f4f6;">
            <td style="padding: 10px;">${hora}</td>
            <td style="padding: 10px;">
              <span class="badge badge-${mov.tipo === 'ingreso' ? 'success' : 'danger'}">
                <i class="fas ${iconoTipo}"></i> ${mov.tipo.toUpperCase()}
              </span>
            </td>
            <td style="padding: 10px;">${mov.concepto}</td>
            <td style="padding: 10px; font-size: 12px; color: #6b7280;">${mov.cedula_cliente || '-'}</td>
            <td style="padding: 10px; font-size: 12px; color: #6b7280;">${mov.referencia || '-'}</td>
            <td style="padding: 10px;">${mov.usuario_nombre || 'Sistema'}</td>
            <td style="padding: 10px; text-align: right; font-weight: 700; color: ${colorMonto};">
              ${mov.tipo === 'ingreso' ? '+' : '-'}${formatMoney(mov.monto)}
            </td>
          </tr>
        `;
          }); // ← cierre correcto del segundo foreach

          html += `
            </tbody>
          </table>
        </div>
      `;
        }); // ← cierre correcto del primer foreach

        contenedor.innerHTML = html;

      } catch (error) {
        console.error('Error cargando reporte de caja:', error);
        Swal.fire('Error', 'No se pudo cargar el reporte', 'error');
      }
    }
  </script>

  <script>
    /* ========================================================
   LOGICA DE CARTERA VENCIDA PARA ADMIN
   ======================================================== */

    // Variable global para almacenar los pendientes del admin
    let pendientesAdminData = [];

    // 1. Función para cargar los datos del servidor
    async function cargarPendientesAdmin() {
      try {
        // Reutilizamos el mismo archivo que usa el empleado (la lógica es la misma)
        const response = await fetch('/php/obtener_clientes_pendientes.php');
        const data = await response.json();

        pendientesAdminData = data.clientes || [];

        // Actualizar contador
        const totalElem = document.getElementById('total-pendientes-admin');
        if (totalElem) totalElem.textContent = pendientesAdminData.length;

        renderizarPendientesAdmin();

      } catch (error) {
        console.error('Error cargando pendientes:', error);
        const tbody = document.getElementById('tabla-pendientes-admin');
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red;">Error al cargar datos</td></tr>';
      }
    }

    // 2. Función para pintar la tabla
    function renderizarPendientesAdmin() {
      const tbody = document.getElementById('tabla-pendientes-admin');
      if (!tbody) return;

      const busqueda = document.getElementById('buscarPendienteAdmin')?.value.toLowerCase() || '';

      const filtrados = pendientesAdminData.filter(cliente =>
        cliente.cliente_nombre.toLowerCase().includes(busqueda) ||
        cliente.cedula.toLowerCase().includes(busqueda)
      );

      if (filtrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; color: #10b981; font-weight: 600;">✓ No hay cuotas atrasadas pendientes</td></tr>';
        return;
      }

      tbody.innerHTML = filtrados.map(cliente => {
        const cuotasAtrasadas = parseInt(cliente.cuotas_atrasadas);
        const periodicidad = cliente.periodicidad || 'Diario';
        const valorCuota = parseFloat(cliente.valor_cuota);
        const montoEnMora = parseFloat(cliente.falta_pagar);

        // Lógica de Fecha
        let fechaHtml = '-';
        if (cliente.proximo_pago) {
          const fechaObj = new Date(cliente.proximo_pago + 'T00:00:00');
          const hoy = new Date();
          hoy.setHours(0, 0, 0, 0);

          const fechaTexto = fechaObj.toLocaleDateString('es-CO', {
            day: 'numeric',
            month: 'short'
          });
          const colorFecha = fechaObj < hoy ? '#ef4444' : '#10b981'; // Rojo si venció
          fechaHtml = `<div style="font-weight:700; color:${colorFecha};">${fechaTexto}</div>`;
        }

        // Estilos
        let badgeClass = cuotasAtrasadas >= 3 ? 'badge-danger' : 'badge-warning';
        let rowStyle = cuotasAtrasadas >= 3 ? 'background-color: #fee2e2;' : 'background-color: #fffbeb;';

        let badgePeriodo = '';
        if (periodicidad === 'Semanal') badgePeriodo = '<span class="badge badge-info" style="font-size:10px;">Semanal</span>';
        else if (periodicidad === 'Quincenal') badgePeriodo = '<span class="badge badge-success" style="font-size:10px;">Quincenal</span>';
        else badgePeriodo = '<span class="badge badge-secondary" style="font-size:10px;">Diario</span>';

        // ORDEN CORREGIDO DE COLUMNAS (8 Columnas):
        // 1. Cédula | 2. Cliente | 3. Frecuencia | 4. Valor Cuota | 5. Monto Mora | 6. Cuotas Atrasadas | 7. Próximo Pago | 8. Acciones
        return `
            <tr style="${rowStyle}">
                <td>${cliente.cedula}</td>
                <td style="font-weight: 600;">${cliente.cliente_nombre}</td>
                <td>${badgePeriodo}</td>
                <td>${formatMoney(valorCuota)}</td> <td style="color: #ef4444; font-weight: 700;">${formatMoney(montoEnMora)}</td> <td style="text-align: center;"><span class="badge ${badgeClass}">${cuotasAtrasadas}</span></td> <td style="text-align: center;">${fechaHtml}</td> <td>
                    <button class="btn btn-sm btn-success" 
                            onclick="cobrarClienteAdmin(${cliente.prestamo_id}, ${montoEnMora})">
                        <i class="fas fa-hand-holding-usd"></i> Cobrar
                    </button>
                </td>
            </tr>
        `;
      }).join('');
    }

    // 3. Función especial para abrir el modal de cobro desde la tabla de pendientes
    function cobrarClienteAdmin(prestamoId, montoSugerido) {
      // Abrir el modal de pago existente
      openModal('modalPago');

      // Esperar un momento a que el modal se renderice y cargue los préstamos
      setTimeout(() => {
        const select = document.getElementById('prestamo_pago');

        // Seleccionar el préstamo en el select
        select.value = prestamoId;

        // Forzar el evento 'change' para que se llene la info del cliente (nombre, saldo, etc.)
        const event = new Event('change');
        select.dispatchEvent(event);

        // Sobreescribir el monto con la deuda total (Monto en Mora)
        // Usamos un segundo timeout pequeño para asegurar que el 'change' terminó
        setTimeout(() => {
          if (montoSugerido) {
            document.getElementById('monto_pagado').value = montoSugerido;
          }
        }, 200);

      }, 300); // Tiempo para asegurar que openModal cargó los selects
    }
  </script>

</body>

</html>