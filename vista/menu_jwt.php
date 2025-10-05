<?php
/**
 * Menú principal con autenticación JWT
 * Esta versión usa tokens JWT en lugar de sesiones PHP
 */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Menu</title>
    <meta name="description" content="Panel de control con autenticación JWT" />
    <style>
        :root {
            --rose-50:#fff8fb; --rose-100:#ffe9f1; --rose-200:#ffd2e3; --rose-300:#ffb2cf; --rose-400:#ff89b4;
            --rose-500:#f16496; --rose-600:#d64c7f; --rose-700:#b53866; --rose-800:#8f2d52; --rose-900:#732643;
            --bg-grad: radial-gradient(circle at 25% 20%, #ffe9f3 0%, #ffe1ec 25%, #ffd6e6 45%, #ffd0e2 60%, #ffc2db 100%);
            --glass-bg: linear-gradient(135deg, rgba(255,255,255,0.78), rgba(255,255,255,0.40));
            --shadow-soft: 0 10px 28px -6px rgba(240,100,150,.28), 0 4px 12px -2px rgba(240,100,150,.18);
            --shadow-card: 0 8px 24px -6px rgba(240,100,150,.25), 0 2px 8px rgba(0,0,0,.06);
            --radius:18px;
        }
        * { box-sizing:border-box; }
        html,body { margin:0; padding:0; font-family:'Segoe UI',system-ui,Roboto,Arial,sans-serif; background:var(--bg-grad); min-height:100vh; color:#492535; -webkit-font-smoothing:antialiased; }
        body { display:flex; flex-direction:column; }
        
        .loading-screen { 
            position: fixed; 
            inset: 0; 
            background: var(--bg-grad); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-direction: column; 
            z-index: 1000; 
            transition: opacity 0.5s ease;
        }
        .loading-screen.hidden { opacity: 0; pointer-events: none; }
        .spinner { 
            width: 40px; 
            height: 40px; 
            border: 3px solid rgba(241,100,150,0.3); 
            border-top: 3px solid var(--rose-500); 
            border-radius: 50%; 
            animation: spin 1s linear infinite; 
            margin-bottom: 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        header.minibar { display:flex; align-items:center; gap:18px; padding:18px 34px 8px; justify-content: space-between; }
        .logo { font-weight:700; font-size:22px; letter-spacing:.5px; display:flex; align-items:center; gap:10px; color:var(--rose-700); }
        .logo span { background:linear-gradient(90deg,var(--rose-600),var(--rose-400)); -webkit-background-clip:text; background-clip:text; color:transparent; }
        
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { 
            width: 40px; height: 40px; 
            background: linear-gradient(90deg,var(--rose-500),var(--rose-400)); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-weight: 600; 
            font-size: 16px;
        }
        .user-details { font-size: 14px; }
        .user-name { font-weight: 600; color: var(--rose-700); }
        .user-email { color: #6f3952; font-size: 12px; }
        
        .logout-btn {
            background: linear-gradient(90deg,var(--rose-600),var(--rose-500));
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .logout-btn:hover { transform: translateY(-2px); }
        
        main { flex:1; width:100%; max-width:1320px; margin:0 auto; padding:10px 38px 60px; }
        .hero { text-align: center; margin: 40px 0; }
        h1 { margin:0 0 14px; font-size:clamp(30px,5.2vw,56px); line-height:1.05; font-weight:700; background:linear-gradient(90deg,var(--rose-700),var(--rose-500)); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .lead { margin:0 0 26px; font-size:16px; line-height:1.55; color:#6f3952; max-width:560px; margin-left: auto; margin-right: auto; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 40px 0; }
        .stat-card { 
            background: var(--glass-bg); 
            border: 1px solid rgba(255,255,255,.65); 
            box-shadow: var(--shadow-card); 
            padding: 20px; 
            border-radius: 22px; 
            backdrop-filter: blur(12px) saturate(160%);
            text-align: center;
        }
        .stat-number { font-size: 2.5em; font-weight: 700; background: linear-gradient(90deg,var(--rose-700),var(--rose-500)); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .stat-label { color: #6f3952; font-weight: 600; margin-top: 10px; }
        
        .jwt-info { 
            background: var(--glass-bg); 
            border: 1px solid rgba(255,255,255,.65); 
            box-shadow: var(--shadow-card); 
            padding: 25px; 
            border-radius: 22px; 
            backdrop-filter: blur(12px) saturate(160%);
            margin: 30px 0;
        }
        .jwt-info h2 { 
            margin: 0 0 20px; 
            background: linear-gradient(90deg,var(--rose-700),var(--rose-500)); 
            -webkit-background-clip:text; 
            background-clip:text; 
            color:transparent; 
        }
        .jwt-token { 
            background: #f8f9fa; 
            border: 1px solid #e9ecef; 
            border-radius: 12px; 
            padding: 15px; 
            font-family: 'Courier New', monospace; 
            font-size: 11px; 
            word-break: break-all; 
            margin: 10px 0;
            max-height: 120px;
            overflow-y: auto;
        }
        .jwt-payload {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 12px;
            padding: 15px;
            margin: 10px 0;
        }
        .refresh-btn {
            background: linear-gradient(90deg,var(--rose-400),var(--rose-300));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 10px 5px;
        }
        
        footer { margin-top:70px; text-align:center; padding:34px 20px 54px; font-size:13px; color:#7f4d62; }
        
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #a00;
            padding: 15px;
            border-radius: 12px;
            margin: 20px 0;
            text-align: center;
        }
        
        @media (max-width:840px){ 
            main{ padding:20px 24px 60px; } 
            .user-info { flex-direction: column; gap: 10px; }
            .stat-number { font-size: 2em; }
        }
    </style>
</head>
<body>
    <div id="loadingScreen" class="loading-screen">
        <div class="spinner"></div>
        <p>Verificando autenticación...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <header class="minibar">
            <div class="logo"><span>Panel</span></div>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">?</div>
                <div class="user-details">
                    <div class="user-name" id="userName">Cargando...</div>
                    <div class="user-email" id="userEmail">...</div>
                </div>
                <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
            </div>
        </header>

        <main>
            <section class="hero">
                <h1 id="welcomeMessage">Panel de Control JWT</h1>
                <p class="lead">Bienvenido</p>
            </section>

            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="tokenExpiry">--</div>
                    <div class="stat-label">Minutos hasta expirar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="sessionDuration">--</div>
                    <div class="stat-label">Minutos de sesión</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">✅</div>
                    <div class="stat-label">Autenticación JWT Activa</div>
                </div>
            </section>

            <section class="jwt-info">
                <h2>Información del Token JWT</h2>
                
                <h3>Access Token:</h3>
                <div class="jwt-token" id="accessToken">Cargando token...</div>
                
                <h3>Payload Decodificado:</h3>
                <div class="jwt-payload" id="tokenPayload">Decodificando...</div>
                
                <h3>Controles:</h3>
                <button class="refresh-btn" onclick="refreshToken()">🔄 Renovar Token</button>
                <button class="refresh-btn" onclick="updateProfile()">👤 Actualizar Perfil</button>
                <button class="refresh-btn" onclick="testAuthEndpoint()">🧪 Probar Endpoint Protegido</button>
            </section>

            <div id="apiResponse" style="display: none;"></div>
        </main>

        <footer>
            © <?php echo date('Y'); ?> Flores
        </footer>
    </div>

    <div id="errorContent" style="display: none;">
        <div class="error-message">
            <h2>Error de Autenticación</h2>
            <p>No se pudo verificar tu identidad. Por favor, inicia sesión nuevamente.</p>
            <button onclick="window.location.href='/loginjwt/index.php'" class="refresh-btn">
                Ir al Login
            </button>
        </div>
    </div>

    <script src="../controlador/js/jwt-manager.js"></script>
    <script>
        let userProfile = null;
        let tokenTimer = null;

        async function initializePage() {
            const loadingScreen = document.getElementById('loadingScreen');
            const mainContent = document.getElementById('mainContent');
            const errorContent = document.getElementById('errorContent');

            try {
                // Verificar autenticación
                if (!window.jwtManager.isAuthenticated()) {
                    throw new Error('No authenticated');
                }

                // Obtener perfil del usuario
                userProfile = await window.jwtManager.getUserProfile();
                
                if (!userProfile) {
                    throw new Error('No se pudo obtener el perfil del usuario');
                }

                // Actualizar UI con datos del usuario
                updateUserInterface();
                updateTokenInfo();
                startTokenTimer();

                // Mostrar contenido principal
                loadingScreen.classList.add('hidden');
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    mainContent.style.display = 'block';
                }, 500);

            } catch (error) {
                console.error('Error inicializando página:', error);
                
                // Mostrar error
                loadingScreen.classList.add('hidden');
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    errorContent.style.display = 'block';
                }, 500);
            }
        }

        function updateUserInterface() {
            if (!userProfile) return;

            // Actualizar avatar (primera letra del nombre)
            const avatar = document.getElementById('userAvatar');
            avatar.textContent = userProfile.name.charAt(0).toUpperCase();

            // Actualizar nombre y email
            document.getElementById('userName').textContent = userProfile.full_name || userProfile.name;
            document.getElementById('userEmail').textContent = userProfile.email;
            
            // Actualizar mensaje de bienvenida
            document.getElementById('welcomeMessage').textContent = `¡Hola, ${userProfile.name}!`;
        }

        function updateTokenInfo() {
            const token = window.jwtManager.getAccessToken();
            
            if (!token) return;

            try {
                // Mostrar token (truncado para seguridad)
                const tokenDisplay = document.getElementById('accessToken');
                tokenDisplay.textContent = token.substring(0, 100) + '...' + token.substring(token.length - 20);

                // Decodificar y mostrar payload
                const payload = JSON.parse(atob(token.split('.')[1]));
                const payloadDisplay = document.getElementById('tokenPayload');
                payloadDisplay.innerHTML = `
                    <strong>Usuario ID:</strong> ${payload.sub}<br>
                    <strong>Email:</strong> ${payload.user_data.email}<br>
                    <strong>Emitido:</strong> ${new Date(payload.iat * 1000).toLocaleString()}<br>
                    <strong>Expira:</strong> ${new Date(payload.exp * 1000).toLocaleString()}<br>
                    <strong>Válido desde:</strong> ${new Date(payload.nbf * 1000).toLocaleString()}<br>
                    <strong>Issuer:</strong> ${payload.iss}<br>
                    <strong>Audience:</strong> ${payload.aud}
                `;

                // Calcular tiempo hasta expiración
                const now = Math.floor(Date.now() / 1000);
                const timeUntilExpiry = Math.max(0, payload.exp - now);
                const minutesUntilExpiry = Math.floor(timeUntilExpiry / 60);
                
                document.getElementById('tokenExpiry').textContent = minutesUntilExpiry;
                
                // Calcular duración de la sesión
                const sessionDuration = Math.floor((now - payload.iat) / 60);
                document.getElementById('sessionDuration').textContent = sessionDuration;

            } catch (error) {
                console.error('Error actualizando info del token:', error);
            }
        }

        function startTokenTimer() {
            // Actualizar cada minuto
            tokenTimer = setInterval(updateTokenInfo, 60000);
        }

        async function refreshToken() {
            try {
                const success = await window.jwtManager.refreshAccessToken();
                
                if (success) {
                    updateTokenInfo();
                    showMessage('Token renovado exitosamente', 'success');
                } else {
                    showMessage('Error renovando token', 'error');
                }
            } catch (error) {
                console.error('Error renovando token:', error);
                showMessage('Error renovando token', 'error');
            }
        }

        async function updateProfile() {
            try {
                userProfile = await window.jwtManager.getUserProfile();
                updateUserInterface();
                showMessage('Perfil actualizado', 'success');
            } catch (error) {
                console.error('Error actualizando perfil:', error);
                showMessage('Error actualizando perfil', 'error');
            }
        }

        async function testAuthEndpoint() {
            try {
                const response = await window.jwtManager.authFetch('/loginjwt/controlador/scripts/user_profile.php');
                const data = await response.json();
                
                const responseDiv = document.getElementById('apiResponse');
                responseDiv.style.display = 'block';
                responseDiv.innerHTML = `
                    <div class="jwt-info">
                        <h3>Respuesta del Endpoint Protegido:</h3>
                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; overflow-x: auto;">${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
                
                showMessage('Endpoint protegido accedido exitosamente', 'success');
            } catch (error) {
                console.error('Error probando endpoint:', error);
                showMessage('Error accediendo al endpoint protegido', 'error');
            }
        }

        function showMessage(message, type = 'info') {
            // Crear toast de notificación
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                padding: 15px 20px;
                border-radius: 12px;
                border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
                z-index: 1000;
                font-weight: 600;
                transition: all 0.3s ease;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        function logout() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.jwtManager.logout();
                
                if (tokenTimer) {
                    clearInterval(tokenTimer);
                }
                
                showMessage('Sesión cerrada exitosamente', 'success');
                setTimeout(() => {
                    window.location.href = '/loginjwt/index.php';
                }, 1000);
            }
        }

        // Inicializar página cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializePage);

        // Limpiar timer al salir de la página
        window.addEventListener('beforeunload', () => {
            if (tokenTimer) {
                clearInterval(tokenTimer);
            }
        });
    </script>
</body>
</html>