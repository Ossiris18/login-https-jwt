const express = require('express');
const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');
const helmet = require('helmet');
const cors = require('cors');
const rateLimit = require('express-rate-limit');
const compression = require('compression');
const { createProxyMiddleware } = require('http-proxy-middleware');

const app = express();

// Configuración de puertos
const HTTP_PORT = 3000;
const HTTPS_PORT = 3443;
const PHP_SERVER_PORT = 80; // Puerto donde corre tu servidor PHP (XAMPP)

// Configuración de seguridad con Helmet
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
      scriptSrc: ["'self'", "'unsafe-inline'"],
      fontSrc: ["'self'", "https://fonts.gstatic.com"],
      imgSrc: ["'self'", "data:", "https:"],
      connectSrc: ["'self'"]
    }
  },
  hsts: {
    maxAge: 31536000,
    includeSubDomains: true,
    preload: true
  }
}));

// Configuración CORS
app.use(cors({
  origin: ['https://localhost:3443', 'https://127.0.0.1:3443'],
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));

// Compresión
app.use(compression());

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 100, // máximo 100 requests por IP en 15 minutos
  message: {
    error: 'Demasiadas solicitudes desde esta IP, intenta de nuevo en 15 minutos.'
  },
  standardHeaders: true,
  legacyHeaders: false,
});

const loginLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutos
  max: 5, // máximo 5 intentos de login por IP en 15 minutos
  message: {
    error: 'Demasiados intentos de login, intenta de nuevo en 15 minutos.'
  },
  skipSuccessfulRequests: true,
});

app.use(limiter);
app.use('/controlador/scripts/valida_login.php', loginLimiter);

// Middleware para redirigir HTTP a HTTPS
app.use((req, res, next) => {
  if (req.header('x-forwarded-proto') !== 'https') {
    res.redirect(`https://${req.header('host')}${req.url}`);
  } else {
    next();
  }
});

// Configuración del proxy hacia el servidor PHP
const phpProxy = createProxyMiddleware({
  target: `http://localhost:${PHP_SERVER_PORT}`,
  changeOrigin: true,
  pathRewrite: {
    '^/': '/loginJWT/', // Redirige las requests al directorio de tu proyecto
  },
  onProxyReq: (proxyReq, req, res) => {
    // Agregar headers de seguridad
    proxyReq.setHeader('X-Real-IP', req.ip);
    proxyReq.setHeader('X-Forwarded-For', req.ip);
    proxyReq.setHeader('X-Forwarded-Proto', 'https');
  },
  onError: (err, req, res) => {
    console.error('Error del proxy:', err);
    res.status(500).json({
      error: 'Error interno del servidor',
      message: 'No se pudo conectar con el servidor PHP'
    });
  }
});

// Aplicar el proxy a todas las rutas
app.use('/', phpProxy);

// Manejo de errores
app.use((error, req, res, next) => {
  console.error('Error:', error);
  res.status(500).json({
    error: 'Error interno del servidor',
    message: process.env.NODE_ENV === 'development' ? error.message : 'Algo salió mal'
  });
});

// Función para verificar y cargar certificados SSL
function loadSSLCertificates() {
  const certPath = path.join(__dirname, 'ssl');
  const keyFile = path.join(certPath, 'private-key.pem');
  const certFile = path.join(certPath, 'certificate.pem');

  try {
    if (!fs.existsSync(keyFile) || !fs.existsSync(certFile)) {
      console.log('⚠️  No se encontraron certificados SSL.');
      console.log('📋 Ejecuta: npm run generate-certs para crear certificados de desarrollo.');
      return null;
    }

    return {
      key: fs.readFileSync(keyFile, 'utf8'),
      cert: fs.readFileSync(certFile, 'utf8')
    };
  } catch (error) {
    console.error('❌ Error al cargar certificados SSL:', error.message);
    return null;
  }
}

// Servidor HTTP que redirige a HTTPS
const httpServer = http.createServer((req, res) => {
  const host = req.headers.host.split(':')[0];
  const httpsUrl = `https://${host}:${HTTPS_PORT}${req.url}`;
  
  res.writeHead(301, {
    'Location': httpsUrl,
    'Strict-Transport-Security': 'max-age=31536000; includeSubDomains; preload'
  });
  res.end();
});

// Iniciar servidores
const sslOptions = loadSSLCertificates();

if (sslOptions) {
  // Servidor HTTPS
  const httpsServer = https.createServer(sslOptions, app);
  
  httpsServer.listen(HTTPS_PORT, () => {
    console.log('🔒 Servidor HTTPS iniciado en:', `https://localhost:${HTTPS_PORT}`);
    console.log('🔑 SSL/TLS habilitado');
    console.log('🛡️  Características de seguridad activas:');
    console.log('   • Headers de seguridad (Helmet)');
    console.log('   • Rate limiting');
    console.log('   • Compresión');
    console.log('   • CORS configurado');
    console.log('   • HSTS habilitado');
  });

  // Servidor HTTP (solo para redirección)
  httpServer.listen(HTTP_PORT, () => {
    console.log(`🔄 Servidor HTTP iniciado en puerto ${HTTP_PORT} (redirige a HTTPS)`);
    console.log('📱 Accede a tu aplicación en: https://localhost:3443');
  });
} else {
  console.log('❌ No se pudo iniciar el servidor HTTPS sin certificados.');
  console.log('🔧 Genera los certificados primero con: npm run generate-certs');
  process.exit(1);
}