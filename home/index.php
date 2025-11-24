
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- <title>SB Admin 2 - Login</title> -->

    <!-- Custom fonts for this template-->
    <!-- <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet"> -->

    <!-- Custom styles for this template-->
     <link href="/css/login.css" rel="stylesheet">

</head> 

    <body>
    <div class="container">
        <div class="form-container">
            <div class="form-wrapper">
                <!-- Login Form -->
                <div id="loginForm" class="form active">
                    <div class="icon-wrapper">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <h2>Iniciar Sesión</h2>
                    <div class="success-message" id="loginSuccess">
                        ¡Inicio de sesión exitoso!
                    </div>
                    <form action="/php/login.php" method="POST">
                        <div class="input-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" required placeholder="ejemplo@correo.com">
                        </div>
                        <div class="input-group">
                            <label>Contraseña</label>
                            <input type="password" name="clave" required placeholder="••••••••">
                        </div>
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember">
                            <label for="remember">Recordarme</label>
                        </div>
                        <button type="submit" class="btn">Iniciar Sesión</button>
                    </form>
                    <div class="link-text">
                        <a onclick="showForm('register')">¿No tienes cuenta? Regístrate</a>
                    </div>
                    <div class="link-text">
                        <a onclick="showForm('forgot')">¿Olvidaste tu contraseña?</a>
                    </div>
                </div>

                <!-- Register Form -->
                <div id="registerForm" class="form">
                    <div class="icon-wrapper">
                        <svg viewBox="0 0 24 24">
                            <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                    <h2>Registro</h2>
                    <div class="success-message" id="registerSuccess">
                        ¡Registro exitoso! Ahora puedes iniciar sesión.
                    </div>
                    <form action="/php/registrar.php" method="post">
                        <div class="input-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" required placeholder="Juan Pérez">
                        </div>
                        <div class="input-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" required placeholder="ejemplo@correo.com">
                        </div>
                        <div class="input-group">
                            <label>Contraseña</label>
                            <input type="password" name="clave" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn">Registrarse</button>
                    </form>
                    <div class="link-text">
                        <a onclick="showForm('login')">¿Ya tienes cuenta? Inicia sesión</a>
                    </div>
                </div>

                <!-- Forgot Password Form -->
                <div id="forgotForm" class="form">
                    <div class="icon-wrapper">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                        </svg>
                    </div>
                    <h2>Recuperar Contraseña</h2>
                    <div class="success-message" id="forgotSuccess">
                        ¡Correo enviado! Revisa tu bandeja de entrada.
                    </div>
                    <p style="text-align: center; color: #666; margin-bottom: 25px; font-size: 14px;">
                        Ingresa tu correo y te enviaremos un enlace para restablecer tu contraseña.
                    </p>
                    <form actio="recuperacion.php" method="post">
                        <div class="input-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="clave" required placeholder="ejemplo@correo.com">
                        </div>
                        <button type="submit" class="btn">Enviar Enlace</button>
                    </form>
                    <div class="link-text">
                        <a onclick="showForm('login')">Volver al inicio de sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showForm(formName) {
            const forms = document.querySelectorAll('.form');
            forms.forEach(form => {
                form.classList.remove('active');
            });
            
            const container = document.querySelector('.form-container');
            container.style.animation = 'none';
            setTimeout(() => {
                container.style.animation = 'slideIn 0.6s ease-out';
            }, 10);
            
            const targetForm = document.getElementById(formName + 'Form');
            setTimeout(() => {
                targetForm.classList.add('active');
            }, 100);
        }

        function handleLogin(event) {
            event.preventDefault();
            const successMsg = document.getElementById('loginSuccess');
            successMsg.classList.add('show');
            
            const form = event.target;
            form.style.opacity = '0.5';
            form.style.pointerEvents = 'none';
            
            setTimeout(() => {
                successMsg.classList.remove('show');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
                form.reset();
            }, 2500);
        }

        function handleRegister(event) {
            event.preventDefault();
            const successMsg = document.getElementById('registerSuccess');
            successMsg.classList.add('show');
            
            const form = event.target;
            form.style.opacity = '0.5';
            form.style.pointerEvents = 'none';
            
            setTimeout(() => {
                successMsg.classList.remove('show');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
                showForm('login');
                form.reset();
            }, 2500);
        }

        function handleForgot(event) {
            event.preventDefault();
            const successMsg = document.getElementById('forgotSuccess');
            successMsg.classList.add('show');
            
            const form = event.target;
            form.style.opacity = '0.5';
            form.style.pointerEvents = 'none';
            
            setTimeout(() => {
                successMsg.classList.remove('show');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
                form.reset();
            }, 2500);
        }

        // Agregar efecto de partículas al hacer clic en los botones
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    left: ${x}px;
                    top: ${y}px;
                    width: 0;
                    height: 0;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: translate(-50%, -50%);
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Animación de entrada para los inputs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        function handleLogin(event) {
  event.preventDefault();
  const form = event.target;
  const datos = new FormData(form);

  fetch("php/login.php", {
    method: "POST",
    body: datos
  })
  .then(res => res.text())
  .then(respuesta => {
    if (respuesta.includes("dashboard")) {
      window.location.href = respuesta;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error de inicio de sesión',
        text: respuesta
      });
    }
  })
  .catch(() => {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar con el servidor.'
    });
  });
}

function handleRegister(event) {
  event.preventDefault();
  const form = event.target;
  const datos = new FormData(form);

  fetch("php/registrar.php", {
    method: "POST",
    body: datos
  })
  .then(res => res.text())
  .then(respuesta => {
    if (respuesta.includes("Registro exitoso")) {
      Swal.fire({
        icon: 'success',
        title: '¡Registro exitoso!',
        text: 'Ahora puedes iniciar sesión.'
      });
      form.reset();
      showForm("login");
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error al registrar',
        text: respuesta
      });
    }
  })
  .catch(() => {
    Swal.fire({
      icon: 'error',
      title: 'Error de conexión',
      text: 'No se pudo conectar con el servidor.'
    });
  });
}
    </script>

<!-- <body class="bg-gradient-primary">

    <div class="container"> -->

        <!-- Outer Row -->
        <!-- <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0"> -->
                        <!-- Nested Row within Card Body -->
                        <!-- <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address...">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" placeholder="Password">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <a href="index.html" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </a>
                                        <hr>
                                        <a href="index.html" class="btn btn-google btn-user btn-block">
                                            <i class="fab fa-google fa-fw"></i> Login with Google
                                        </a>
                                        <a href="index.html" class="btn btn-facebook btn-user btn-block">
                                            <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                                        </a>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="register.html">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div> -->

    <!-- Bootstrap core JavaScript-->
    <!-- <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script> -->

    <!-- Core plugin JavaScript-->
    <!-- <script src="vendor/jquery-easing/jquery.easing.min.js"></script> -->

    <!-- Custom scripts for all pages-->
    <!-- <script src="js/sb-admin-2.min.js"></script> -->

</body>

</html>