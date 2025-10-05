const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Crear directorio SSL si no existe
const sslDir = path.join(__dirname, 'ssl');
if (!fs.existsSync(sslDir)) {
  fs.mkdirSync(sslDir, { recursive: true });
}

console.log('🔐 Generando certificados SSL para desarrollo...');

try {
  // Verificar si OpenSSL está disponible
  try {
    execSync('openssl version', { stdio: 'pipe' });
    generateWithOpenSSL();
  } catch (error) {
    console.log('❌ OpenSSL no está disponible en el sistema.');
    console.log('💡 Usando generador alternativo con Node.js...');
    generateWithNodeJS();
  }
} catch (error) {
  console.error('❌ Error al generar certificados:', error.message);
  process.exit(1);
}

function generateWithOpenSSL() {
  const keyPath = path.join(sslDir, 'private-key.pem');
  const certPath = path.join(sslDir, 'certificate.pem');

  // Configuración del certificado
  const subject = '/C=MX/ST=Mexico/L=Mexico/O=LoginJWT/OU=Development/CN=localhost/emailAddress=admin@localhost';

  try {
    // Generar clave privada
    console.log('🔑 Generando clave privada...');
    execSync(`openssl genrsa -out "${keyPath}" 2048`, { stdio: 'pipe' });

    // Generar certificado
    console.log('📜 Generando certificado...');
    execSync(`openssl req -new -x509 -key "${keyPath}" -out "${certPath}" -days 365 -subj "${subject}" -extensions v3_req -config <(echo "[req]"; echo "distinguished_name=req"; echo "[v3_req]"; echo "subjectAltName=@alt_names"; echo "[alt_names]"; echo "DNS.1=localhost"; echo "DNS.2=127.0.0.1"; echo "IP.1=127.0.0.1")`, { 
      stdio: 'pipe',
      shell: '/bin/bash'
    });

    console.log('✅ Certificados SSL generados exitosamente con OpenSSL!');
    logSuccess(keyPath, certPath);
  } catch (error) {
    console.log('⚠️  Error con OpenSSL, usando método alternativo...');
    generateWithNodeJS();
  }
}

function generateWithNodeJS() {
  try {
    const selfsigned = require('selfsigned');
    
    const attrs = [
      { name: 'countryName', value: 'MX' },
      { name: 'stateOrProvinceName', value: 'Mexico' },
      { name: 'localityName', value: 'Mexico' },
      { name: 'organizationName', value: 'LoginJWT' },
      { name: 'organizationalUnitName', value: 'Development' },
      { name: 'commonName', value: 'localhost' },
      { name: 'emailAddress', value: 'admin@localhost' }
    ];

    const options = {
      keySize: 2048,
      days: 365,
      algorithm: 'sha256',
      extensions: [
        {
          name: 'basicConstraints',
          cA: false
        },
        {
          name: 'keyUsage',
          keyCertSign: true,
          digitalSignature: true,
          nonRepudiation: true,
          keyEncipherment: true,
          dataEncipherment: true
        },
        {
          name: 'extKeyUsage',
          serverAuth: true,
          clientAuth: true,
          codeSigning: true,
          timeStamping: true
        },
        {
          name: 'subjectAltName',
          altNames: [
            {
              type: 2, // DNS
              value: 'localhost'
            },
            {
              type: 2, // DNS
              value: '127.0.0.1'
            },
            {
              type: 7, // IP
              ip: '127.0.0.1'
            },
            {
              type: 7, // IP
              ip: '::1'
            }
          ]
        }
      ]
    };

    console.log('� Generando certificado auto-firmado...');
    const pems = selfsigned.generate(attrs, options);

    const keyPath = path.join(sslDir, 'private-key.pem');
    const certPath = path.join(sslDir, 'certificate.pem');

    fs.writeFileSync(keyPath, pems.private);
    fs.writeFileSync(certPath, pems.cert);

    console.log('✅ Certificados SSL generados exitosamente con Node.js!');
    logSuccess(keyPath, certPath);

  } catch (error) {
    console.error('❌ Error al generar certificados con Node.js:', error.message);
    throw error;
  }
}

function logSuccess(keyPath, certPath) {
  console.log(`📁 Ubicación: ${sslDir}`);
  console.log('📋 Archivos creados:');
  console.log(`   • Clave privada: ${keyPath}`);
  console.log(`   • Certificado: ${certPath}`);
  console.log('');
  console.log('⚠️  IMPORTANTE: Estos son certificados de desarrollo auto-firmados.');
  console.log('   El navegador mostrará una advertencia de seguridad que puedes ignorar.');
  console.log('   Para producción, usa certificados de una CA válida como Let\'s Encrypt.');
  console.log('');
  console.log('🚀 Ahora puedes ejecutar: npm start');
}

