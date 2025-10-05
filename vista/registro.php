<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" type="image/png" href="../vista/images/icons/favicon.ico"/>
    <!-- Eliminamos Bootstrap para un estilo propio ligero -->
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
        .panel { width:100%; max-width:480px; background:linear-gradient(140deg,rgba(255,255,255,.95),rgba(255,255,255,.78)); backdrop-filter:blur(14px) saturate(160%); padding:40px 40px 34px; border-radius:32px; position:relative; box-shadow:0 18px 50px -12px rgba(240,100,150,.35), 0 4px 16px -4px rgba(240,100,150,.25); }
        .panel::before { content:""; position:absolute; inset:0; border-radius:inherit; padding:1px; background:linear-gradient(160deg,rgba(255,255,255,.5),rgba(255,255,255,0),rgba(240,100,150,.25)); -webkit-mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite:xor; mask-composite:exclude; pointer-events:none; }
        form { display:flex; flex-direction:column; gap:18px; }
        label { font-size:12px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#8a5165; display:block; margin-bottom:6px; }
        .field { display:flex; flex-direction:column; }
        input[type=text], input[type=email], input[type=password] { border:1px solid var(--rose-300); background:rgba(255,255,255,.78); padding:14px 16px; border-radius:18px; font-size:14px; color:#532c3c; font-weight:500; transition:.35s; outline:none; }
        input:focus { border-color:var(--rose-500); box-shadow:var(--focus-ring); }
        button[type=submit] { margin-top:6px; border:none; cursor:pointer; background:linear-gradient(90deg,var(--rose-600),var(--rose-500)); color:#fff; padding:15px 22px; font-weight:600; font-size:15px; border-radius:22px; letter-spacing:.5px; box-shadow:0 10px 24px -8px rgba(240,100,150,.55), 0 4px 10px -4px rgba(240,100,150,.35); transition:.4s cubic-bezier(.65,.05,.36,1); }
        button[type=submit]:hover { transform:translateY(-3px); box-shadow:0 16px 34px -10px rgba(240,100,150,.6); }
        .status { display:none; margin-top:10px; font-size:13px; font-weight:500; padding:11px 16px; border-radius:16px; line-height:1.35; }
        .status.ok { background:#e4f8ec; color:#1b7f42; display:block; }
        .status.err { background:#ffe6ea; color:#ab2347; display:block; }
        .links { margin-top:8px; font-size:12px; text-align:center; }
        .links a { color:var(--rose-600); font-weight:600; text-decoration:none; }
        .links a:hover { text-decoration:underline; }
        footer.mini { margin-top:40px; font-size:11px; letter-spacing:.5px; text-align:center; color:#95576a; }
        .petal { position:fixed; top:-10vh; width:14px; height:14px; background:radial-gradient(circle at 30% 30%, var(--rose-400), var(--rose-600)); border-radius:60% 40% 70% 30%/50% 60% 40% 50%; opacity:.75; animation:fall linear forwards; pointer-events:none; }
        @keyframes fall { to { transform:translateY(110vh) rotate(360deg); opacity:0; } }
        @media (max-width:560px) { body { padding:26px 16px; } .panel { padding:34px 30px; border-radius:26px; } }
    </style>
</head>
<body>
    <div class="panel" aria-labelledby="tituloRegistro">
        <header style="text-align:center;margin-bottom:10px;">
            <h1 id="tituloRegistro">Registro</h1>
            <p class="subtitle">Crea tu cuenta floral</p>
        </header>
        <form action="../controlador/scripts/valida_usuario.php" method="POST" id="formRegistro" autocomplete="off">
            <div class="field">
                <label for="correo">Correo (.mx)</label>
                <input type="email" name="correo" id="correo" placeholder="usuario@ejemplo.mx" required>
            </div>
            <div class="field">
                <label for="pass">Clave (10 caracteres)</label>
                <input type="password" name="pass" id="pass" placeholder="Contraseña" required maxlength="10">
            </div>
            <div class="field">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Nombre" required>
            </div>
            <div class="field">
                <label for="apaterno">Apellido Paterno</label>
                <input type="text" name="apaterno" id="apaterno" placeholder="Apellido Paterno" required>
            </div>
            <div class="field">
                <label for="amaterno">Apellido Materno</label>
                <input type="text" name="amaterno" id="amaterno" placeholder="Apellido Materno" required>
            </div>
            <button type="submit">Registrar</button>
            <div class="links">
                <a href="../index.php">Volver al inicio de sesión</a>
            </div>
            <div id="registroStatus" class="status" role="alert" aria-live="assertive"></div>
        </form>
    </div>
    <footer class="mini">© <?php echo date('Y'); ?> Jardín Floral</footer>
    <script>
    // Pétalos decorativos ligeros
    (function(){
        const total = 14;
        for(let i=0;i<total;i++){
            const p=document.createElement('div');
            p.className='petal';
            const delay=Math.random()*5;
            const dur=7+Math.random()*8;
            const left=Math.random()*100;
            p.style.left=left+'vw';
            p.style.animationDuration=dur+'s';
            p.style.animationDelay=delay+'s';
            p.style.opacity=0.45+Math.random()*0.4;
            document.body.appendChild(p);
            setTimeout(()=>p.remove(), (dur+delay)*1000);
        }
        setInterval(()=>{
            const p=document.querySelectorAll('.petal').length;
            if(p<12){
                const n=document.createElement('div');
                n.className='petal';
                const dur=7+Math.random()*8;
                n.style.left=Math.random()*100+'vw';
                n.style.animationDuration=dur+'s';
                n.style.opacity=0.45+Math.random()*0.4;
                document.body.appendChild(n);
                setTimeout(()=>n.remove(), dur*1000);
            }
        }, 3500);
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formRegistro');
        const status = document.getElementById('registroStatus');
        if(!form) return;
        function showStatus(msg, ok){
            status.textContent = msg;
            status.className = 'status ' + (ok ? 'ok' : 'err');
        }
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const datos = new FormData(form);
            showStatus('Registrando...', true);
            fetch('../controlador/scripts/valida_usuario.php', { 
                method: 'POST', 
                body: datos,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showStatus('¡Registro exitoso! Redirigiendo...', true);
                    setTimeout(() => { 
                        window.location.href = '../index.php'; 
                    }, 1500);
                } else {
                    showStatus(data.message || 'Error en el registro.', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showStatus('Error de conexión.', false);
            });
        });
    });
    </script>
</body>
</html>