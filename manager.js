const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

class HTTPSServerManager {
  constructor() {
    this.serverProcess = null;
    this.isRunning = false;
  }

  // Verificar si los certificados SSL existen
  checkSSLCertificates() {
    const sslDir = path.join(__dirname, 'ssl');
    const keyFile = path.join(sslDir, 'private-key.pem');
    const certFile = path.join(sslDir, 'certificate.pem');

    return fs.existsSync(keyFile) && fs.existsSync(certFile);
  }

  // Generar certificados SSL si no existen
  async generateCertificatesIfNeeded() {
    if (!this.checkSSLCertificates()) {
      console.log('🔐 Generando certificados SSL...');
      
      return new Promise((resolve, reject) => {
        const certProcess = spawn('node', ['generate-certificates.js'], {
          stdio: 'inherit'
        });

        certProcess.on('close', (code) => {
          if (code === 0) {
            console.log('✅ Certificados generados exitosamente');
            resolve();
          } else {
            reject(new Error(`Error al generar certificados. Código: ${code}`));
          }
        });

        certProcess.on('error', (error) => {
          reject(error);
        });
      });
    }
  }

  // Verificar si XAMPP está ejecutándose
  async checkXAMPP() {
    return new Promise((resolve) => {
      const http = require('http');
      const req = http.request({
        hostname: 'localhost',
        port: 80,
        path: '/',
        method: 'GET',
        timeout: 3000
      }, (res) => {
        resolve(true);
      });

      req.on('error', () => {
        resolve(false);
      });

      req.on('timeout', () => {
        resolve(false);
      });

      req.end();
    });
  }

  // Iniciar el servidor HTTPS
  async startServer() {
    try {
      // Verificar XAMPP
      const xamppRunning = await this.checkXAMPP();
      if (!xamppRunning) {
        console.log('⚠️  XAMPP no parece estar ejecutándose en el puerto 80');
        console.log('   Asegúrate de iniciar Apache en XAMPP antes de continuar');
        const readline = require('readline');
        const rl = readline.createInterface({
          input: process.stdin,
          output: process.stdout
        });
        
        const answer = await new Promise((resolve) => {
          rl.question('¿Continuar de todos modos? (y/N): ', (answer) => {
            rl.close();
            resolve(answer.toLowerCase());
          });
        });

        if (answer !== 'y' && answer !== 'yes') {
          console.log('❌ Operación cancelada');
          return;
        }
      }

      // Generar certificados si es necesario
      await this.generateCertificatesIfNeeded();

      // Iniciar servidor
      console.log('🚀 Iniciando servidor HTTPS...');
      this.serverProcess = spawn('node', ['server.js'], {
        stdio: 'inherit'
      });

      this.isRunning = true;

      this.serverProcess.on('close', (code) => {
        this.isRunning = false;
        if (code === 0) {
          console.log('🛑 Servidor detenido');
        } else {
          console.log(`❌ Servidor terminó con código: ${code}`);
        }
      });

      this.serverProcess.on('error', (error) => {
        console.error('❌ Error al iniciar servidor:', error.message);
        this.isRunning = false;
      });

      // Manejar señales para cierre limpio
      process.on('SIGINT', () => {
        console.log('\n🛑 Cerrando servidor...');
        this.stopServer();
      });

      process.on('SIGTERM', () => {
        console.log('\n🛑 Cerrando servidor...');
        this.stopServer();
      });

    } catch (error) {
      console.error('❌ Error:', error.message);
    }
  }

  // Detener el servidor
  stopServer() {
    if (this.serverProcess && this.isRunning) {
      this.serverProcess.kill('SIGTERM');
      setTimeout(() => {
        if (this.isRunning) {
          this.serverProcess.kill('SIGKILL');
        }
      }, 5000);
    }
  }

  // Mostrar estado del servidor
  showStatus() {
    console.log('\n📊 Estado del Servidor HTTPS');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    
    // Verificar certificados
    const hasSSL = this.checkSSLCertificates();
    console.log(`🔐 Certificados SSL: ${hasSSL ? '✅ Disponibles' : '❌ No encontrados'}`);
    
    // Verificar XAMPP
    this.checkXAMPP().then(xamppRunning => {
      console.log(`🖥️  XAMPP (Puerto 80): ${xamppRunning ? '✅ Ejecutándose' : '❌ No detectado'}`);
      console.log(`🚀 Servidor HTTPS: ${this.isRunning ? '✅ Ejecutándose' : '❌ Detenido'}`);
      
      if (this.isRunning) {
        console.log('\n🌐 URLs disponibles:');
        console.log('   • HTTPS: https://localhost:3443');
        console.log('   • HTTP:  http://localhost:3000 (redirige a HTTPS)');
      }
      
      console.log('\n📋 Comandos disponibles:');
      console.log('   npm start          - Iniciar servidor');
      console.log('   npm run dev        - Iniciar con auto-reload');
      console.log('   npm run generate-certs - Generar certificados SSL');
      console.log('   node manager.js status - Mostrar este estado');
      console.log('');
    });
  }
}

// Ejecutar según el argumento
const manager = new HTTPSServerManager();
const command = process.argv[2];

switch (command) {
  case 'start':
    manager.startServer();
    break;
  case 'status':
    manager.showStatus();
    break;
  case 'certs':
    manager.generateCertificatesIfNeeded()
      .then(() => console.log('✅ Certificados verificados/generados'))
      .catch(error => console.error('❌ Error:', error.message));
    break;
  default:
    console.log('🔧 Gestor del Servidor HTTPS LoginJWT');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('');
    console.log('📋 Comandos disponibles:');
    console.log('   node manager.js start   - Iniciar servidor HTTPS');
    console.log('   node manager.js status  - Mostrar estado');
    console.log('   node manager.js certs   - Verificar/generar certificados');
    console.log('');
    console.log('📖 Para más información, consulta README.md');
    break;
}