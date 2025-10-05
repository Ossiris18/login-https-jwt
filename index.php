<?php
// Siempre mostrar el formulario aunque exista sesión. Solo mostramos un aviso para ir al menú o cerrar sesión.
session_start();
$__yaLogueado = !empty($_SESSION['logged_in']);
$user_email = $_SESSION['user_email'] ?? '';
// Cerrar sesión después de leer los datos necesarios
session_write_close();

function validarPassword($password) {
    // Verificar longitud máxima
    if (strlen($password) > 10) {
        return "La contraseña no puede tener más de 10 caracteres";
    }
    
    // Verificar que no esté vacía
    if (empty($password)) {
        return "La contraseña no puede estar vacía";
    }
    
    // Verificar que la primera letra sea mayúscula
    if (!ctype_upper($password[0])) {
        return "La primera letra debe ser mayúscula";
    }
    
    // Verificar que contenga al menos un signo de pesos
    if (strpos($password, '$') === false) {
        return "La contraseña debe contener al menos un signo de pesos ($)";
    }
    
    // Verificar que los números estén al final (si existen)
    $tieneNumeros = preg_match('/\d/', $password);
    if ($tieneNumeros) {
        // Encontrar la posición del primer número
        $primerNumeroPos = -1;
        for ($i = 0; $i < strlen($password); $i++) {
            if (is_numeric($password[$i])) {
                $primerNumeroPos = $i;
                break;
            }
        }
        
        // Verificar que después del primer número solo haya números
        for ($i = $primerNumeroPos; $i < strlen($password); $i++) {
            if (!is_numeric($password[$i])) {
                return "Los números deben estar todos al final de la contraseña";
            }
        }
    }
    
    // Verificar caracteres permitidos: letras, $, números
    if (!preg_match('/^[A-Za-z\$0-9]+$/', $password)) {
        return "Solo se permiten letras, signo de pesos ($) y números";
    }
    
    // Si pasa todas las validaciones
    return true;
}
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Acceso</title>
	<link rel="icon" type="image/png" href="./vista/images/icons/favicon.ico"/>
	<style>
		:root {
			--rose-50:#fff7fb; --rose-100:#ffe9f3; --rose-200:#ffd3e6; --rose-300:#ffb6d4; --rose-400:#ff8fbb;
			--rose-500:#f56c9f; --rose-600:#db4f84; --rose-700:#b63a68; --rose-800:#8e2f53; --rose-900:#6f2743;
			--error:#c62828; --ok:#1b7f42; --focus-ring:0 0 0 4px rgba(245,108,159,.25);
		}
		* { box-sizing:border-box; }
		body { margin:0; font-family:'Segoe UI',system-ui,Roboto,Arial,sans-serif; background:radial-gradient(circle at 25% 20%, var(--rose-100), var(--rose-200), var(--rose-300)); min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:34px 18px; color:#522b3a; }
		h1 { margin:0 0 4px; font-size:clamp(26px,5vw,40px); font-weight:700; letter-spacing:.5px; background:linear-gradient(90deg,var(--rose-700),var(--rose-500)); -webkit-background-clip:text; background-clip:text; color:transparent; }
		p.subtitle { margin:0 0 24px; font-size:14px; letter-spacing:.5px; color:#7a4c5c; }
		.panel { width:100%; max-width:410px; background:linear-gradient(140deg,rgba(255,255,255,.92),rgba(255,255,255,.75)); backdrop-filter:blur(14px) saturate(160%); padding:34px 34px 32px; border-radius:28px; position:relative; box-shadow:0 18px 50px -12px rgba(240,100,150,.35), 0 4px 16px -4px rgba(240,100,150,.25); }
		.panel::before { content:""; position:absolute; inset:0; border-radius:inherit; padding:1px; background:linear-gradient(160deg,rgba(255,255,255,.4),rgba(255,255,255,0),rgba(240,100,150,.25)); -webkit-mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; pointer-events:none; }
		form { display:flex; flex-direction:column; gap:18px; }
		label { font-size:12px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#8a5165; display:block; margin-bottom:6px; }
		.field { display:flex; flex-direction:column; }
		input[type=email], input[type=password] { border:1px solid var(--rose-300); background:rgba(255,255,255,.75); padding:14px 44px 14px 14px; border-radius:16px; font-size:14px; color:#532c3c; font-weight:500; box-shadow:0 2px 4px rgba(0,0,0,.04) inset; transition:.35s; outline:none; }
		input[type=email]:focus, input[type=password]:focus { border-color:var(--rose-500); box-shadow:var(--focus-ring); }
		.pw-wrap { position:relative; }
		.toggle-btn { position:absolute; top:50%; right:10px; transform:translateY(-50%); border:none; background:transparent; cursor:pointer; font-size:15px; color:var(--rose-600); padding:4px 6px; border-radius:8px; }
		.toggle-btn:hover { background:rgba(245,108,159,.12); }
		button[type=submit] { margin-top:4px; border:none; cursor:pointer; background:linear-gradient(90deg,var(--rose-600),var(--rose-500)); color:#fff; padding:14px 20px; font-weight:600; font-size:15px; border-radius:18px; letter-spacing:.5px; box-shadow:0 10px 24px -8px rgba(240,100,150,.55), 0 4px 10px -4px rgba(240,100,150,.35); transition:.4s cubic-bezier(.65,.05,.36,1); }
		button[type=submit]:hover { transform:translateY(-3px); box-shadow:0 16px 34px -10px rgba(240,100,150,.6); }
		.links { margin-top:4px; display:flex; justify-content:space-between; font-size:12px; }
		.links a { color:var(--rose-600); text-decoration:none; font-weight:600; }
		.links a:hover { text-decoration:underline; }
		.status { display:none; margin-top:10px; font-size:13px; font-weight:500; padding:10px 14px; border-radius:14px; line-height:1.35; }
		.status.ok { background:#e4f8ec; color:#1b7f42; display:block; }
		.status.err { background:#ffe6ea; color:#ab2347; display:block; }
		footer.mini { margin-top:40px; font-size:11px; letter-spacing:.5px; text-align:center; color:#95576a; }
		.session-hint { max-width:410px; margin:0 auto 18px; font-size:13px; background:#ffe2ef; border:1px solid #f7b5cf; padding:12px 16px; border-radius:16px; color:#6d3a4c; line-height:1.4; }
		.session-hint a { color:#b63a68; font-weight:600; text-decoration:none; }
		.session-hint a:hover { text-decoration:underline; }
		@media (max-width:520px) { body { padding:26px 14px; } .panel { padding:30px 26px; border-radius:24px; } }
	</style>
</head>
<body>
	<div id="toastLogin" class="status" role="alert" aria-live="assertive" aria-atomic="true"></div>
	<?php if ($__yaLogueado): ?>
		<div class="session-hint">
			Ya iniciaste sesión como <strong><?php echo htmlspecialchars($user_email); ?></strong>.<br>
			<a href="/loginjwt/vista/menu.php">Ir al menú</a> ·
			<a href="/loginjwt/vista/menu.php?logout=1">Cerrar sesión</a>
		</div>
	<?php endif; ?>
	<div class="panel" aria-labelledby="tituloLogin">
		<header style="text-align:center;margin-bottom:12px;">
			<h1 id="tituloLogin">Acceder</h1>
			<p class="subtitle">Bienvenido</p>
		</header>
		<form id="formLogin" autocomplete="off">
			<div class="field">
				<label for="email">Correo (.mx)</label>
				<input id="email" name="email" type="email" required autocomplete="username" placeholder="usuario@ejemplo.mx" />
			</div>
			<div class="field pw-wrap">
				<label for="password">Contraseña</label>
				<input id="password" name="pass" type="password" required autocomplete="current-password" placeholder="Contraseña" />
				<button type="button" class="toggle-btn" id="togglePassword" aria-label="Mostrar u ocultar contraseña">👁</button>
			</div>
			<button type="submit">Ingresar</button>
			<div class="links">
				<a href="./vista/registro.php">Registro</a>
				<a href="#" onclick="alert('Funcionalidad pendiente');return false;">Olvidé mi contraseña</a>
			</div>
		</form>
	</div>
	<footer class="mini">© <?php echo date('Y'); ?>Login</footer>
	<script src="./controlador/js/jwt-manager.js"></script>
	<script>
	(function(){
		const form = document.getElementById('formLogin');
		const toast = document.getElementById('toastLogin');
		const toggleBtn = document.getElementById('togglePassword');
		const inputPass = document.getElementById('password');
		
		function showStatus(msg, ok){
			toast.textContent = msg;
			toast.className = 'status ' + (ok ? 'ok' : 'err');
		}
		
		// Verificar si ya está autenticado con JWT
		if (window.jwtManager && window.jwtManager.isAuthenticated()) {
			const userData = window.jwtManager.getUserData();
			if (userData) {
				showStatus(`Ya tienes sesión activa como ${userData.email}`, true);
				// Mostrar opción para ir al menú o cerrar sesión
				setTimeout(() => {
					if (confirm('Ya tienes una sesión activa. ¿Ir al menú?')) {
						window.location.href = '/loginjwt/vista/menu.php';
					} else {
						window.jwtManager.logout();
						showStatus('Sesión cerrada', true);
					}
				}, 1000);
			}
		}
		
		if(toggleBtn && inputPass){
			toggleBtn.addEventListener('click', ()=>{
				const isPw = inputPass.type === 'password';
				inputPass.type = isPw ? 'text' : 'password';
				toggleBtn.textContent = isPw ? '' : '👁';
			});
		}
		
		if(form){
			form.addEventListener('submit', async (e)=>{
				e.preventDefault();
				const email = form.email.value.trim();
				const pass = form.pass.value.trim();
				
				if(!email || !pass){ 
					showStatus('Completa todos los campos.', false); 
					return; 
				}
				
				const fd = new FormData(); 
				fd.append('email', email); 
				fd.append('pass', pass);
				
				showStatus('Validando credenciales...', true);
				
				try {
					const response = await fetch('controlador/scripts/valida_login.php', { 
						method: 'POST', 
						body: fd, 
						credentials: 'same-origin' 
					});
					
					const data = await response.json();
					
					if (data.success && data.data && data.data.tokens) {
						showStatus('¡Autenticación exitosa!', true);
						
						// Guardar tokens JWT y datos del usuario
						window.jwtManager.saveTokens(
							data.data.tokens.access_token,
							data.data.tokens.refresh_token
						);
						window.jwtManager.saveUserData(data.data.user);
						
						// Iniciar timer de renovación automática
						window.jwtManager.startTokenRefreshTimer();
						
						showStatus('Redirigiendo al panel...', true);
						setTimeout(() => {
							window.location.href = data.redirect || '/loginjwt/vista/menu.php';
						}, 800);
						
					} else {
						const errorMsg = data.message || data.msg || 'Credenciales inválidas';
						showStatus(errorMsg, false);
						console.error('Error de login:', data);
					}
					
				} catch (error) {
					console.error('Error de conexión:', error);
					showStatus('Error de conexión con el servidor', false);
				}
			});
		}
	})();
	</script>
</body>
</html>