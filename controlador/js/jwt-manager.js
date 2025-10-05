/**
 * Utilidad para manejo de JWT en el frontend
 * Maneja tokens, localStorage y autenticación automática
 */

class JWTManager {
    constructor() {
        this.accessTokenKey = 'loginJWT_access_token';
        this.refreshTokenKey = 'loginJWT_refresh_token';
        this.userDataKey = 'loginJWT_user_data';
        this.baseURL = window.location.origin;
    }

    /**
     * Guardar tokens en localStorage
     */
    saveTokens(accessToken, refreshToken) {
        localStorage.setItem(this.accessTokenKey, accessToken);
        localStorage.setItem(this.refreshTokenKey, refreshToken);
    }

    /**
     * Obtener access token
     */
    getAccessToken() {
        return localStorage.getItem(this.accessTokenKey);
    }

    /**
     * Obtener refresh token
     */
    getRefreshToken() {
        return localStorage.getItem(this.refreshTokenKey);
    }

    /**
     * Guardar datos del usuario
     */
    saveUserData(userData) {
        localStorage.setItem(this.userDataKey, JSON.stringify(userData));
    }

    /**
     * Obtener datos del usuario
     */
    getUserData() {
        const data = localStorage.getItem(this.userDataKey);
        return data ? JSON.parse(data) : null;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    isAuthenticated() {
        const token = this.getAccessToken();
        if (!token) return false;

        // Verificar si el token no ha expirado (básico)
        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const currentTime = Math.floor(Date.now() / 1000);
            return payload.exp > currentTime;
        } catch (e) {
            return false;
        }
    }

    /**
     * Realizar request con token JWT
     */
    async authFetch(url, options = {}) {
        const token = this.getAccessToken();
        
        if (!token) {
            throw new Error('No hay token de acceso disponible');
        }

        const headers = {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
            ...options.headers
        };

        const response = await fetch(url, {
            ...options,
            headers,
            credentials: 'same-origin'
        });

        // Si el token expiró, intentar renovarlo
        if (response.status === 401) {
            const refreshed = await this.refreshAccessToken();
            if (refreshed) {
                // Reintentar con el nuevo token
                headers.Authorization = `Bearer ${this.getAccessToken()}`;
                return fetch(url, { ...options, headers, credentials: 'same-origin' });
            } else {
                // Refresh falló, redirigir al login
                this.logout();
                window.location.href = '/loginjwt/index.php';
                throw new Error('Sesión expirada');
            }
        }

        return response;
    }

    /**
     * Renovar access token usando refresh token
     */
    async refreshAccessToken() {
        const refreshToken = this.getRefreshToken();
        
        if (!refreshToken) {
            return false;
        }

        try {
            const response = await fetch('/loginjwt/controlador/scripts/refresh_token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: refreshToken
                }),
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.data.tokens) {
                    this.saveTokens(
                        data.data.tokens.access_token,
                        data.data.tokens.refresh_token
                    );
                    return true;
                }
            }
        } catch (error) {
            console.error('Error renovando token:', error);
        }

        return false;
    }

    /**
     * Cerrar sesión
     */
    logout() {
        localStorage.removeItem(this.accessTokenKey);
        localStorage.removeItem(this.refreshTokenKey);
        localStorage.removeItem(this.userDataKey);
    }

    /**
     * Obtener perfil del usuario actual
     */
    async getUserProfile() {
        try {
            const response = await this.authFetch('/loginjwt/controlador/scripts/user_profile.php');
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.saveUserData(data.data.user);
                    return data.data.user;
                }
            }
            
            throw new Error('Error obteniendo perfil');
        } catch (error) {
            console.error('Error en getUserProfile:', error);
            return null;
        }
    }

    /**
     * Auto-renovar token antes de que expire
     */
    startTokenRefreshTimer() {
        const token = this.getAccessToken();
        if (!token) return;

        try {
            const payload = JSON.parse(atob(token.split('.')[1]));
            const expiresAt = payload.exp * 1000; // Convertir a milisegundos
            const currentTime = Date.now();
            const timeUntilExpiry = expiresAt - currentTime;
            
            // Renovar 5 minutos antes de que expire
            const refreshTime = Math.max(timeUntilExpiry - (5 * 60 * 1000), 60000);

            if (refreshTime > 0) {
                setTimeout(() => {
                    this.refreshAccessToken().then(success => {
                        if (success) {
                            console.log('Token renovado automáticamente');
                            this.startTokenRefreshTimer(); // Programar el siguiente refresh
                        }
                    });
                }, refreshTime);
            }
        } catch (e) {
            console.error('Error programando renovación de token:', e);
        }
    }

    /**
     * Inicializar el manager
     */
    init() {
        // Verificar si ya está autenticado
        if (this.isAuthenticated()) {
            this.startTokenRefreshTimer();
            
            // Actualizar datos del usuario si es necesario
            if (!this.getUserData()) {
                this.getUserProfile();
            }
        }
    }
}

// Crear instancia global
window.jwtManager = new JWTManager();

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.jwtManager.init();
});