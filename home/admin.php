<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créditos CR - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="css/admin.css" rel="stylesheet">
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
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 id="pageTitle">Dashboard</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">JD</div>
                    <div>
                        <div style="font-weight: 600;">Juan Pérez</div>
                        <div style="font-size: 12px; color: #6b7280;">Administrador</div>
                    </div>
                </div>
                <button class="logout-btn">
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
                        <div class="card-value">$25,450,000</div>
                        <div class="card-footer">
                            <i class="fas fa-arrow-up" style="color: var(--success-color);"></i>
                            <span>12% vs mes anterior</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="card-title">Total Recuperado</div>
                        <div class="card-value">$18,320,000</div>
                        <div class="card-footer">
                            <i class="fas fa-arrow-up" style="color: var(--success-color);"></i>
                            <span>8% vs mes anterior</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="card-title">Clientes Activos</div>
                        <div class="card-value">156</div>
                        <div class="card-footer">
                            <i class="fas fa-users"></i>
                            <span>Total de clientes</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="card-title">Clientes Morosos</div>
                        <div class="card-value">23</div>
                        <div class="card-footer">
                            <i class="fas fa-arrow-down" style="color: var(--danger-color);"></i>
                            <span>-5% vs mes anterior</span>
                        </div>
                    </div>
                </div>

                <div class="chart-container">
                    <h3>Cobros Diarios - Últimos 7 días</h3>
                    <canvas id="cobrosChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h3>Préstamos Recientes</h3>
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> Ver Todos
                        </button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Cuota Diaria</th>
                                <th>Días Restantes</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>María González</td>
                                <td>$500,000</td>
                                <td>$20,000</td>
                                <td>15 días</td>
                                <td><span class="badge badge-success">Al día</span></td>
                            </tr>
                            <tr>
                                <td>Carlos Rodríguez</td>
                                <td>$300,000</td>
                                <td>$12,500</td>
                                <td>8 días</td>
                                <td><span class="badge badge-success">Al día</span></td>
                            </tr>
                            <tr>
                                <td>Ana Martínez</td>
                                <td>$400,000</td>
                                <td>$17,000</td>
                                <td>3 días</td>
                                <td><span class="badge badge-warning">Venciendo</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Clientes Section -->
          <section id="clientes" class="section">
  <div class="table-container">
    <div class="table-header">
      <h3>Gestión de Clientes</h3>
      <div class="search-box">
        <input type="text" class="search-input" placeholder="Buscar por nombre o cédula...">
        <button class="btn btn-primary" onclick="openModal('modalCliente')">
          <i class="fas fa-plus"></i> Nuevo Cliente
        </button>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Cédula</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Dirección</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="clientesTable">
        <?php
        include('php/conexion.php');
        $sql = "SELECT nombre, cedula, telefono, direccion FROM clientes ORDER BY id DESC";
        $resultado = mysqli_query($conexion, $sql);

        while ($row = mysqli_fetch_assoc($resultado)) {
          echo "<tr>";
          echo "<td>{$row['cedula']}</td>";
          echo "<td>{$row['nombre']}</td>";
          echo "<td>{$row['telefono']}</td>";
          echo "<td>{$row['direccion']}</td>";
          echo "<td><span class='badge badge-success'>Activo</span></td>";
          echo "<td>
                  <button class='btn btn-primary btn-sm' onclick=\"abrirEditarCliente('{$row['cedula']}')\">
                    <i class='fas fa-edit'></i>
                  </button>
                
                    <button type='submit' class='btn btn-danger btn-sm' onclick=\"eliminarCliente(' {$row['cedula']}')\">
                    <i class='fas fa-trash'></i>
                    </button>
                 
                </td>";
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</section>




            <!-- Préstamos Section -->
            <section id="prestamos" class="section">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Gestión de Préstamos</h3>
                        <div class="search-box">
                            <select class="search-input">
                                <option>Todos los estados</option>
                                <option>Activos</option>
                                <option>Cancelados</option>
                                <option>Morosos</option>
                            </select>
                            <button class="btn btn-primary" onclick="openModal('modalPrestamo')">
                                <i class="fas fa-plus"></i> Nuevo Préstamo
                            </button>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Interés</th>
                                <th>Cuota Diaria</th>
                                <th>Fecha Inicio</th>
                                <th>Días Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="prestamosTable">
                            <tr>
                                <td>#001</td>
                                <td>María González</td>
                                <td>$500,000</td>
                                <td>20%</td>
                                <td>$20,000</td>
                                <td>2025-01-15</td>
                                <td>30</td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Pagos Section -->
            <section id="pagos" class="section">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Registro de Pagos Diarios</h3>
                        <div class="search-box">
                            <input type="date" class="search-input" value="2025-11-18">
                            <button class="btn btn-success" onclick="openModal('modalPago')">
                                <i class="fas fa-plus"></i> Registrar Pago
                            </button>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Préstamo</th>
                                <th>Cuota Esperada</th>
                                <th>Monto Pagado</th>
                                <th>Fecha Pago</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="pagosTable">
                            <tr>
                                <td>María González</td>
                                <td>#001</td>
                                <td>$20,000</td>
                                <td>$20,000</td>
                                <td>2025-11-18 10:30 AM</td>
                                <td><span class="badge badge-success">Pagado</span></td>
                                <td>
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-print"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section> 

            <!-- Reportes Section -->
            <section id="reportes" class="section">
                <div class="cards-grid" style="margin-bottom: 20px;">
                    <div class="card">
                        <div class="card-title">Ingresos Hoy</div>
                        <div class="card-value">$1,250,000</div>
                    </div>
                    <div class="card">
                        <div class="card-title">Ingresos Semana</div>
                        <div class="card-value">$8,750,000</div>
                    </div>
                    <div class="card">
                        <div class="card-title">Ingresos Mes</div>
                        <div class="card-value">$32,500,000</div>
                    </div>
                </div>

                <div class="chart-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Ingresos Mensuales</h3>
                        <div>
                            <button class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Excel</button>
                            <button class="btn btn-danger btn-sm"><i class="fas fa-file-pdf"></i> PDF</button>
                        </div>
                    </div>
                    <canvas id="ingresosChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="table-container">
                    <h3>Resumen por Fecha</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Pagos Recibidos</th>
                                <th>Total Cobrado</th>
                                <th>Préstamos Nuevos</th>
                                <th>Total Prestado</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-11-18</td>
                                <td>45</td>
                                <td>$1,250,000</td>
                                <td>3</td>
                                <td>$900,000</td>
                                <td style="color: var(--success-color); font-weight: 600;">+$350,000</td>
                            </tr>
                            <tr>
                                <td>2025-11-17</td>
                                <td>42</td>
                                <td>$1,180,000</td>
                                <td>2</td>
                                <td>$600,000</td>
                                <td style="color: var(--success-color); font-weight: 600;">+$580,000</td>
                            </tr>
                        </tbody>
                    </table>
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
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Última Conexión</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juan Pérez</td>
                                <td>admin</td>
                                <td>juan@creditoscr.com</td>
                                <td><span class="badge badge-danger">Administrador</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>Hace 5 min</td>
                                <td>
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-warning btn-sm"><i class="fas fa-key"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>María López</td>
                                <td>mlopes</td>
                                <td>maria@creditoscr.com</td>
                                <td><span class="badge badge-info">Cobrador</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>Hace 1 hora</td>
                                <td>
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-warning btn-sm"><i class="fas fa-key"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section> 

            <!-- Configuración Section -->
            <section id="configuracion" class="section">
                <div class="table-container">
                    <h3>Parámetros del Sistema</h3>
                    <form class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Interés por Defecto (%)</label>
                            <input type="number" value="20" min="0" max="100">
                        </div>
                        <div class="form-group">
                            <label>Días de Cobro por Defecto</label>
                            <input type="number" value="30" min="1">
                        </div>
                        <div class="form-group">
                            <label>Días de Gracia</label>
                            <input type="number" value="3" min="0">
                        </div>
                        <div class="form-group">
                            <label>Mora Diaria (%)</label>
                            <input type="number" value="2" min="0">
                        </div>
                        <div class="form-group">
                            <label>Moneda</label>
                            <select>
                                <option>COP - Peso Colombiano</option>
                                <option>USD - Dólar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Zona Horaria</label>
                            <select>
                                <option>América/Bogotá</option>
                                <option>América/Mexico_City</option>
                            </select>
                        </div>
                    </form>
                    <button class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>

                <div class="table-container" style="margin-top: 30px;">
                    <h3>Personalización Visual</h3>
                    <form class="form-grid" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Color Principal</label>
                            <input type="color" value="#667eea">
                        </div>
                        <div class="form-group">
                            <label>Color Secundario</label>
                            <input type="color" value="#764ba2">
                        </div>
                        <div class="form-group">
                            <label>Logo de la Empresa</label>
                            <input type="file" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Nombre de la Empresa</label>
                            <input type="text" value="Créditos CR">
                        </div>
                    </form>
                    <button class="btn btn-success" style="margin-top: 20px;">
                        <i class="fas fa-save"></i> Guardar Personalización
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

    <form class="form-grid" id="formCliente" action="php/registrar_cliente.php" method="POST">
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
    <form class="form-grid" id="formEditarCliente" action="php/editar_cliente.php" method="POST">
      <div class="form-group">
        <label for="cedula">Cédula *</label>
        <input type="text" name="cedula" id="editCedula"  placeholder="1234567890">
      </div>

      <div class="form-group">
        <label for="nombre">Nombre Completo *</label>
        <input type="text" name="nombre" id="editNombre" placeholder="María González">
      </div>

      <div class="form-group">
        <label for="telefono">Teléfono *</label>
        <input type="tel" name="telefono" id="editTelefono"  placeholder="300-1234567">
      </div>

      <div class="form-group" style="grid-column: 1 / -1;">
        <label for="direccion">Dirección *</label>
        <input type="text" name="direccion" id="editDireccion"  placeholder="Calle 123 #45-67">
      </div>

      <div class="form-group">
        <label for="correo">Email</label>
        <input type="email" name="correo" id="editCorreo" placeholder="maria@ejemplo.com">
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


    </div> 
    <div class="modal" id="modalPrestamo">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Nuevo Préstamo</h3>
      <button class="close-modal" onclick="closeModal('modalPrestamo')">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form class="form-grid" action="php/registrar_prestamo.php" method="POST" oninput="calcularCuota()">
      <!-- cliente_id -->
      <div class="form-group" style="grid-column: 1 / -1;">
        <label for="cliente_id">Seleccionar Cliente *</label>
        <select name="cliente_id" id="cliente_id" required>
          <option value="">-- Seleccione un cliente --</option>
          <!-- Opciones dinámicas desde la BD -->
        </select>
      </div>

      <!-- monto -->
      <div class="form-group">
        <label for="monto">Monto del Préstamo *</label>
        <input type="number" name="monto" id="monto" required min="0" placeholder="500000">
      </div>

      <!-- interes -->
      <div class="form-group">
        <label for="interes">Interés Diario (%) *</label>
        <input type="number" name="interes" id="interes" required value="20" min="0" max="100">
      </div>

      <!-- cuotas -->
      <div class="form-group">
        <label for="cuotas">Número de Cuotas (días) *</label>
        <input type="number" name="cuotas" id="cuotas" required value="30" min="1">
      </div>

      <!-- fecha_inicio -->
      <div class="form-group">
        <label for="fecha_inicio">Fecha de Inicio *</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" required>
      </div>

      <!-- resumen dinámico -->
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

      <!-- campos ocultos para cuota_diaria, fecha_fin y estado -->
      <input type="hidden" name="cuota_diaria" id="cuota_diaria">
      <input type="hidden" name="fecha_fin" id="fecha_fin">
      <input type="hidden" name="estado" value="activo">

      <!-- botones -->
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

    <!-- Modal Pago -->
    <div class="modal" id="modalPago">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Registrar Pago</h3>
                <button class="close-modal" onclick="closeModal('modalPago')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Seleccionar Cliente/Préstamo *</label>
                    <select required>
                        <option value="">-- Seleccione --</option>
                        <option>María González - Préstamo #001 - Cuota: $20,000</option>
                        <option>Carlos Rodríguez - Préstamo #002 - Cuota: $12,500</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Monto a Pagar *</label>
                    <input type="number" required placeholder="20000" min="0">
                </div>
                <div class="form-group">
                    <label>Fecha de Pago *</label>
                    <input type="date" required value="2025-11-18">
                </div>
                <div class="form-group">
                    <label>Método de Pago *</label>
                    <select required>
                        <option>Efectivo</option>
                        <option>Transferencia</option>
                        <option>Nequi</option>
                        <option>Daviplata</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Recibido por *</label>
                    <select required>
                        <option>Juan Pérez</option>
                        <option>María López</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Observaciones</label>
                    <textarea rows="2" placeholder="Notas sobre el pago..."></textarea>
                </div>
            </form>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-save"></i> Registrar Pago
                </button>
                <button class="btn btn-danger" onclick="closeModal('modalPago')" style="flex: 1;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
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
            <form class="form-grid">
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" required placeholder="Pedro Gómez">
                </div>
                <div class="form-group">
                    <label>Usuario *</label>
                    <input type="text" required placeholder="pgomez">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" required placeholder="pedro@creditoscr.com">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" placeholder="300-1234567">
                </div>
                <div class="form-group">
                    <label>Contraseña *</label>
                    <input type="password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Confirmar Contraseña *</label>
                    <input type="password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Rol *</label>
                    <select required>
                        <option value="">-- Seleccione --</option>
                        <option>Administrador</option>
                        <option>Cobrador</option>
                        <option>Consulta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado *</label>
                    <select required>
                        <option>Activo</option>
                        <option>Inactivo</option>
                    </select>
                </div>
            </form>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-save"></i> Crear Usuario
                </button>
                <button class="btn btn-danger" onclick="closeModal('modalUsuario')" style="flex: 1;">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
function calcularCuota() {
  const monto = parseFloat(document.getElementById('monto').value) || 0;
  const interes = parseFloat(document.getElementById('interes').value) || 0;
  const cuotas = parseInt(document.getElementById('cuotas').value) || 0;
  const fechaInicio = document.getElementById('fecha_inicio').value;

  const montoTotal = monto * (interes / 100);
  const cuotaDiaria = montoTotal / cuotas;

  document.getElementById('montoTotal').textContent = `$${montoTotal.toLocaleString('es-CO', {maximumFractionDigits: 0})}`;
  document.getElementById('cuotaDiaria').textContent = `$${cuotaDiaria.toLocaleString('es-CO', {maximumFractionDigits: 0})}`;
  document.getElementById('cuota_diaria').value = cuotaDiaria.toFixed(2);

  if (fechaInicio) {
    const fecha = new Date(fechaInicio);
    fecha.setDate(fecha.getDate() + cuotas);
    const vencimiento = fecha.toISOString().split('T')[0];
    document.getElementById('fechaVencimiento').textContent = vencimiento;
    document.getElementById('fecha_fin').value = vencimiento;
  }
}
</script>

    <script> 
       // Initialize calculations when modal opens
        document.addEventListener('DOMContentLoaded', function() {
           // Initialize date inputs
            const today = new Date().toISOString().split('T')[0];
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                if (!input.value) input.value = today;
            });

            // Add event listeners for calculation
            const prestamoModal = document.getElementById('modalPrestamo');
            if (prestamoModal) {
                const inputs = prestamoModal.querySelectorAll('input[type="number"], input[type="date"]');
                inputs.forEach(input => {
                    input.addEventListener('input', calcularCuota);
                });
            }
        });
    </script>


    <script>
        function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'flex'; // o 'block' si prefieres
    modal.classList.add('active');
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('active');
  }
}

// Cerrar modal al hacer clic fuera del contenido
window.addEventListener('click', function(event) {
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (event.target === modal) {
      modal.style.display = 'none';
      modal.classList.remove('active');
    }
  });
});
        </script>

    <script>
function showSection(id) {
  const secciones = document.querySelectorAll('.section');
  secciones.forEach(sec => sec.style.display = 'none');

  const activa = document.getElementById(id);
  if (activa) {
    activa.style.display = 'block';
    document.getElementById('pageTitle').textContent = activa.querySelector('h2')?.textContent || 'Dashboard';
  }

  const links = document.querySelectorAll('.menu-link');
  links.forEach(link => link.classList.remove('active'));
  const activo = [...links].find(link => link.getAttribute('onclick')?.includes(id));
  if (activo) activo.classList.add('active');
}
</script>

<script>
document.getElementById('formCliente').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  fetch('php/registrar_cliente.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Cliente registrado',
        text: data.message
      });
      form.reset();
      closeModal('modalCliente');
      // Opcional: recargar tabla de clientes
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: data.message
      });
    }
  })
  .catch(err => {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo registrar el cliente.'
    });
    console.error(err);
  });
});
</script>

<script>
function abrirEditarCliente(cedula) {
  fetch('php/obtener_cliente.php?cedula=' + cedula)
    .then(res => res.json())
    .then(data => {
      document.getElementById('editCedula').value = data.cedula;
      document.getElementById('editNombre').value = data.nombre;
      document.getElementById('editTelefono').value = data.telefono;
      document.getElementById('editDireccion').value = data.direccion;
      document.getElementById('editCorreo').value = data.correo;
      openModal('modalEditarCliente');
    });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const formEditar = document.getElementById('formEditarCliente');
  if (!formEditar) return;

  formEditar.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(formEditar);

    fetch('php/editar_cliente.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.success ? 'Cliente actualizado' : 'Error',
        text: data.message,
        showConfirmButton: false,
        timer: 2000
      });
      if (data.success) {
        closeModal('modalEditarCliente');
        // Opcional: recargar tabla o actualizar fila
      }
    })
    .catch(err => {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo actualizar el cliente.'
      });
      console.error(err);
    });
  });
});
</script>

<script>
function eliminarCliente(cedula) {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción eliminará al cliente de forma permanente.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('cedula', cedula);

      fetch('php/eliminar_cliente.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        Swal.fire({
          icon: data.success ? 'success' : 'error',
          title: data.success ? 'Eliminado' : 'Error',
          text: data.message,
          showConfirmButton: false,
          timer: 2000
        });
        if (data.success) {
          setTimeout(() => location.reload(), 2000); // o elimina la fila dinámicamente
        }
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No se pudo eliminar el cliente.'
        });
        console.error(err);
      });
    }
  });
}
</script>


</body>
</html>