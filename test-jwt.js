/**
 * Script para probar JWT
 * Ejecutar en la consola del navegador cuando estés logueado
 */

async function testJWT() {
    try {
        console.log('🧪 Iniciando prueba de JWT...');
        
        // 1. Verificar si existe el token
        const token = localStorage.getItem('access_token');
        
        if (!token) {
            console.error('❌ No hay token en localStorage');
            return false;
        }
        
        console.log('✅ Token encontrado en localStorage');
        
        // 2. Decodificar el payload (sin verificar firma)
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            console.log('📄 Payload del token:', payload);
            
            // 3. Verificar expiración
            const now = Math.floor(Date.now() / 1000);
            const timeLeft = payload.exp - now;
            
            if (timeLeft <= 0) {
                console.error('❌ Token expirado');
                return false;
            }
            
            console.log(`⏰ Token válido por ${Math.floor(timeLeft / 60)} minutos más`);
            
        } catch (e) {
            console.error('❌ Error decodificando token:', e);
            return false;
        }
        
        // 4. Probar endpoint protegido
        console.log('🔗 Probando endpoint protegido...');
        
        const response = await fetch('/loginjwt/test-jwt.php', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Endpoint protegido respondió correctamente:', data);
            return true;
        } else {
            console.error('❌ Error en endpoint protegido:', data);
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error general:', error);
        return false;
    }
}

// Función para probar renovación de token
async function testTokenRefresh() {
    try {
        console.log('🔄 Probando renovación de token...');
        
        const refreshToken = localStorage.getItem('refresh_token');
        
        if (!refreshToken) {
            console.error('❌ No hay refresh token');
            return false;
        }
        
        const response = await fetch('/loginjwt/controlador/scripts/refresh_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                refresh_token: refreshToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Token renovado exitosamente:', data);
            localStorage.setItem('access_token', data.access_token);
            return true;
        } else {
            console.error('❌ Error renovando token:', data);
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error en renovación:', error);
        return false;
    }
}

// Ejecutar automáticamente si hay token
if (localStorage.getItem('access_token')) {
    console.log('🚀 Ejecutando prueba automática de JWT...');
    testJWT();
} else {
    console.log('ℹ️ No hay token para probar. Inicia sesión primero.');
    console.log('ℹ️ Luego ejecuta: testJWT()');
}