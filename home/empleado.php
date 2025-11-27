<?php
include(__DIR__ . "/../php/verificar_sesion.php");
// Verificar que sea empleado
if (!esEmpleado()) {
    header("Location: admin.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Empleado';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Empleado - CREDITOS_CR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="sidebar-header">
            <h2>CREDITOS CR</h2>
            <p>Panel Empleado</p>
        </div>

        <ul class="menu">
            <li class="menu-item">
                <a href="#" class="menu-link active" onclick="showSection('clientes')">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="showSection('pagos')">
                    <i class="fas fa-receipt"></i>
                    <span>Pagos Diarios</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link" onclick="showSection('clientes-pendientes')">
                    <i class="fas fa-clock"></i>
                    <span>Pendientes Hoy</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Panel de Cobros</h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                    <span class="badge badge-success">Empleado</span>
                </div>
                <button class="logout-btn" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content">
            <!-- Clientes Section -->
            <div id="clientes" class="section active">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Lista de Clientes</h3>
                        <div class="search-box">
                            <input type="text" class="search-input" id="buscarCliente" placeholder="Buscar por cédula o nombre..." onkeyup="filtrarClientes()">
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
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-clientes">
                                <tr>
                                    <td colspan="5" style="text-align: center;">Cargando clientes...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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

            <!-- Clientes Pendientes Section -->
            <div id="clientes-pendientes" class="section">
                <div class="table-container">
                    <div class="table-header">
                        <h3>Clientes con Pagos Pendientes Hoy</h3>
                        <div class="search-box">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: 600; color: #667eea;">Fecha: <span id="fecha-pendientes"></span></span>
                                <span style="font-weight: 600; color: #ef4444;">Total Pendientes: <span id="total-pendientes">0</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Cuota Diaria</th>
                                    <th>Falta Pagar</th>
                                    <th>Saldo Total</th>
                                    <th>Días Mora</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-pendientes">
                                <tr>
                                    <td colspan="9" style="text-align: center;">Cargando información...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Seleccionar Préstamo *</label>
                    <select name="prestamo_id" id="prestamo_pago" required>
                        <option value="">-- Seleccione un préstamo --</option>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Establecer fecha actual
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('fechaPago').value = today;

            // Cargar datos iniciales
            cargarClientes();
            cargarPagos();
            cargarClientesPendientes();
        });

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

        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            document.querySelectorAll('.menu-link').forEach(link => {
                link.classList.remove('active');
            });

            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.add('active');
            }

            // Buscar y activar el enlace correspondiente en el menú
            const menuLinks = document.querySelectorAll('.menu-link');
            menuLinks.forEach(link => {
                if (link.getAttribute('onclick') && link.getAttribute('onclick').includes(sectionId)) {
                    link.classList.add('active');
                }
            });

            // Cargar datos según la sección
            if (sectionId === 'clientes') {
                cargarClientes();
            } else if (sectionId === 'pagos') {
                cargarPagos();
            } else if (sectionId === 'clientes-pendientes') {
                cargarClientesPendientes();
            }
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            if (modalId === 'modalPago') cargarPrestamosSelect();
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('active');

            // Limpiar formulario
            if (modalId === 'modalPago') {
                document.getElementById('formPago').reset();
            }
        }

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        });

        async function cargarClientes() {
            try {
                const response = await fetch('/php/obtener_cliente.php');
                const clientes = await response.json();

                const tbody = document.getElementById('tabla-clientes');

                if (!clientes || clientes.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No hay clientes registrados</td></tr>';
                    return;
                }

                tbody.innerHTML = clientes.map(cliente => `
                    <tr>
                        <td>${cliente.cedula}</td>
                        <td>${cliente.nombre}</td>
                        <td>${cliente.telefono || 'N/A'}</td>
                        <td>${cliente.direccion || 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="verDetalleCliente('${cliente.cedula}')">
                                <i class="fas fa-eye"></i> Ver Detalle
                            </button>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('tabla-clientes').innerHTML =
                    '<tr><td colspan="5" style="text-align: center; color: red;">Error al cargar clientes</td></tr>';
            }
        }

        function filtrarClientes() {
            const busqueda = document.getElementById('buscarCliente').value.toLowerCase();
            const filas = document.querySelectorAll('#tabla-clientes tr');

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(busqueda) ? '' : 'none';
            });
        }

        async function verDetalleCliente(cedula) {
            try {
                const response = await fetch(`/php/obtener_prestamos_cliente.php?cedula=${cedula}`);
                const data = await response.json();

                if (!data.success) {
                    Swal.fire('Error', data.message, 'error');
                    return;
                }

                const cliente = data.cliente;
                const prestamos = data.prestamos;

                let htmlPrestamos = '';
                if (prestamos.length > 0) {
                    htmlPrestamos = '<h4 style="margin-top: 20px;">Préstamos Activos:</h4>';
                    prestamos.forEach(p => {
                        const estadoBadge = p.estado === 'activo' ? 'badge-success' : 'badge-warning';
                        htmlPrestamos += `
                            <div class="card" style="margin-top: 10px;">
                                <p><strong>Préstamo #${p.id}</strong></p>
                                <p><strong>Monto:</strong> ${formatMoney(parseFloat(p.monto))}</p>
                                <p><strong>Cuota Diaria:</strong> ${formatMoney(parseFloat(p.cuota_diaria))}</p>
                                <p><strong>Saldo Pendiente:</strong> ${formatMoney(parseFloat(p.saldo_pendiente))}</p>
                                <p><strong>Estado:</strong> <span class="badge ${estadoBadge}">${p.estado.toUpperCase()}</span></p>
                            </div>
                        `;
                    });
                } else {
                    htmlPrestamos = '<p style="color: #888; margin-top: 20px;">No tiene préstamos activos</p>';
                }

                Swal.fire({
                    title: 'Detalle del Cliente',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Nombre:</strong> ${cliente.nombre}</p>
                            <p><strong>Cédula:</strong> ${cedula}</p>
                            <p><strong>Teléfono:</strong> ${cliente.telefono || 'N/A'}</p>
                            <p><strong>Dirección:</strong> ${cliente.direccion || 'N/A'}</p>
                            <p><strong>Correo:</strong> ${cliente.correo || 'N/A'}</p>
                            ${htmlPrestamos}
                        </div>
                    `,
                    width: '600px',
                    confirmButtonText: 'Cerrar'
                });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar la información del cliente', 'error');
            }
        }

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

                select.innerHTML = '<option value="">-- Seleccione un préstamo --</option>' +
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

                    // Cerrar modal ANTES de mostrar el mensaje
                    closeModal('modalPago');

                    // Actualizar todas las listas
                    await cargarPagos();
                    await cargarClientesPendientes();

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pago Registrado Exitosamente!',
                        html: `
                            <div style="text-align: left; padding: 10px;">
                                <p style="margin: 8px 0;"><strong>Tipo de Pago:</strong> 
                                    <span style="color: ${tipoPago === 'CUOTA COMPLETA' ? '#10b981' : '#f59e0b'}; font-weight: 600;">
                                        ${tipoPago}
                                    </span>
                                </p>
                                <p style="margin: 8px 0;"><strong>Monto Pagado:</strong> ${formatMoney(montoPagado)}</p>
                                <p style="margin: 8px 0;"><strong>Saldo Restante:</strong> ${formatMoney(data.nuevo_saldo)}</p>
                                ${data.nuevo_saldo <= 0 ? '<p style="color: #10b981; font-weight: 600; margin-top: 15px;">🎉 ¡Préstamo cancelado completamente!</p>' : ''}
                            </div>
                        `,
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#667eea',
                        timer: 5000
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Registrar',
                        text: data.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        async function cargarClientesPendientes() {
            try {
                const response = await fetch('/php/obtener_clientes_pendientes.php');
                const data = await response.json();

                const tbody = document.getElementById('tabla-pendientes');

                // Actualizar información de fecha y total
                if (data.fecha_consulta) {
                    document.getElementById('fecha-pendientes').textContent = data.fecha_consulta;
                }
                if (data.total_pendientes !== undefined) {
                    document.getElementById('total-pendientes').textContent = data.total_pendientes;
                }

                if (!data.success || !data.clientes || data.clientes.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; color: #10b981; font-weight: 600;">✓ ¡Excelente! No hay clientes pendientes de pago hoy</td></tr>';
                    return;
                }

                tbody.innerHTML = data.clientes.map(cliente => {
                    const diasMora = parseInt(cliente.dias_mora);
                    const moraBadge = diasMora > 0 ?
                        `<span class="badge badge-danger">${diasMora} día${diasMora > 1 ? 's' : ''}</span>` :
                        '<span class="badge badge-success">Al día</span>';

                    const pagadoHoy = parseFloat(cliente.pagado_hoy);
                    const faltaPagar = parseFloat(cliente.falta_pagar);

                    // Color de la fila según el estado
                    const rowClass = diasMora > 3 ? 'style="background-color: #fee2e2;"' :
                        diasMora > 0 ? 'style="background-color: #fef3c7;"' : '';

                    return `
                        <tr ${rowClass}>
                            <td>${cliente.cedula}</td>
                            <td style="font-weight: 600;">${cliente.cliente_nombre}</td>
                            <td>${cliente.telefono || 'N/A'}</td>
                            <td>${formatMoney(parseFloat(cliente.cuota_diaria))}</td>
                            <td style="color: #ef4444; font-weight: 600;">
                                ${formatMoney(faltaPagar)}
                            </td>
                            <td>${formatMoney(parseFloat(cliente.saldo_pendiente))}</td>
                            <td>${moraBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="cobrarCliente(${cliente.prestamo_id}, ${cliente.cuota_diaria})" title="Registrar pago de este cliente">
                                    <i class="fas fa-hand-holding-usd"></i> Cobrar
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('tabla-pendientes').innerHTML =
                    '<tr><td colspan="9" style="text-align: center; color: red;">Error al cargar pendientes</td></tr>';
            }
        }

        function cobrarCliente(prestamoId, cuotaDiaria = null) {
            // Primero cambiar a la sección de pagos
            showSection('pagos');

            // Esperar un momento antes de abrir el modal
            setTimeout(() => {
                openModal('modalPago');

                // Esperar a que se cargue el select
                setTimeout(() => {
                    const select = document.getElementById('prestamo_pago');
                    select.value = prestamoId;

                    // Si se proporciona la cuota, llenarla directamente
                    if (cuotaDiaria) {
                        document.getElementById('monto_pagado').value = cuotaDiaria;
                    } else {
                        // Disparar el evento change para auto-llenar el monto
                        const event = new Event('change');
                        select.dispatchEvent(event);
                    }
                }, 300);
            }, 100);
        }

        function verComprobante(pagoId) {
            Swal.fire({
                title: 'Comprobante',
                text: 'Funcionalidad de comprobante en desarrollo',
                icon: 'info'
            });
        }

        function formatMoney(amount) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(amount);
        }

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