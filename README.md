# Botiga Electrònica

Aplicación de comercio electrónico desarrollada en PHP para la gestión de usuarios, productos, cestas y pedidos.

## Requisitos

- PHP 7.x o superior
- Apache 2.4 o superior
- Composer (para instalar dependencias)
- Extensión PHP `mbstring` y `openssl`
- Acceso a Internet para envío de correos (PHPMailer)
- XAMPP recomendado para entorno local

## Instalación

1. **Clona el repositorio:**
   ```sh
   git clone https://github.com/tu_usuario/botiga-electronica.git
   ```

2. **Coloca la carpeta en tu directorio de servidor web:**
   ```
   C:\xampp\htdocs\PROYECTO\Projecto
   ```

3. **Instala las dependencias con Composer:**
   ```sh
   cd C:\xampp\htdocs\PROYECTO\Projecto
   composer install
   ```

4. **Configura permisos de escritura** en las carpetas:
   - `botiga\apl`
   - `botiga\cistelles`
   - `botiga\comandes`
   - `botiga\productes`

5. **Configura el correo en los archivos PHP** (PHPMailer):
   - Edita el usuario y contraseña SMTP en los scripts que envían correo.

6. **Protege los archivos sensibles**:
   - Asegúrate de tener archivos `.htaccess` en `apl`, `cistelles` y `comandes` para proteger los `.txt`.

## Uso

1. **Accede a la aplicación:**
   ```
   http://localhost/PROYECTO/Projecto/
   ```

2. **Usuarios iniciales:**
   - Admin: `admin` / `FjeClot2425#`
   - Puedes crear gestores y clientes desde el panel de administrador.

3. **Funcionalidades principales:**
   - Gestión de usuarios (admin, gestor, cliente)
   - Gestión de productos (gestor)
   - Gestión de cestas y pedidos (cliente)
   - Exportación a PDF y envío de correos

## Dependencias

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) (envío de correos)
- [dompdf/dompdf](https://github.com/dompdf/dompdf) (generación de PDF)
- [composer](https://getcomposer.org/) (gestión de dependencias)

## Seguridad

- Contraseñas almacenadas con hash seguro.
- Acceso restringido por roles.
- Archivos `.txt` protegidos con `.htaccess`.
- Uso recomendado de HTTPS.

## Notas

- Si tienes problemas con el correo, revisa la configuración SMTP y permisos de aplicaciones en Gmail.
- Para desarrollo, puedes modificar los archivos `.txt` directamente para pruebas.

---

**Autor:**  
Tu nombre o equipo  
