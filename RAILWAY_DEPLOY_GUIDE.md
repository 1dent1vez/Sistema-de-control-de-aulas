# Guía de Despliegue en Railway — G.A.M.A. AulaControl

Esta guía detalla los pasos exactos para desplegar el proyecto **G.A.M.A. AulaControl** en **Railway** usando la arquitectura oficial de **Majestic Monolith** (1 código base que ejecuta la App Web, el Worker de Colas, y el Cron del Planificador, conectándose a una base de datos MySQL central).

---

## 1. Preparación de la Base de Datos

1. Ingresa a tu panel de **Railway** y crea un nuevo proyecto.
2. Agrega una base de datos **MySQL** haciendo clic en **New** -> **Database** -> **Add MySQL**.
3. Espera a que se inicialice. Railway creará automáticamente variables internas como `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, y `MYSQLDATABASE`.

---

## 2. Creación de los Servicios (1 Código Base, 3 Servicios)

Para desplegar la arquitectura, conectarás tu repositorio de GitHub tres veces, creando tres servicios independientes en Railway.

### Servicio 1: App (La Aplicación Web Principal)

1. En tu proyecto de Railway, haz clic en **New** -> **GitHub Repo** y selecciona tu repositorio.
2. Por defecto, Railway lo nombrará igual que tu repositorio. Cambia su nombre a **App** en la configuración del servicio.
3. **Configuración de Construcción (Build):**
   * Nixpacks detectará automáticamente que es un proyecto PHP (Laravel).
   * Ve a la pestaña **Settings** -> sección **Build** -> localiza **Release Command**.
   * Configura el siguiente comando de liberación (se ejecuta después de construir pero antes de que la nueva versión se publique):
     ```bash
     chmod +x railway/init-app.sh && ./railway/init-app.sh
     ```
4. **Configuración de Red (Networking):**
   * Ve a la pestaña **Settings** -> sección **Networking**.
   * Haz clic en **Generate Domain** para crear una URL pública (ej: `aulas-production.up.railway.app`).
5. **Variables de Entorno:**
   * Ve a la pestaña **Variables** y agrega las variables descritas en la sección 3.

---

### Servicio 2: Worker (Procesamiento de Colas de Correo y Tareas)

1. Haz clic en **New** -> **GitHub Repo** y selecciona el mismo repositorio.
2. Cambia el nombre del servicio a **Worker**.
3. **Configuración de Despliegue (Deploy):**
   * Ve a la pestaña **Settings** -> sección **Deploy** -> localiza **Start Command**.
   * Activa el checkbox para usar un comando personalizado y escribe:
     ```bash
     chmod +x railway/run-worker.sh && ./railway/run-worker.sh
     ```
4. **Configuración de Red (Networking):**
   * **IMPORTANTE:** No generes ningún dominio público para este servicio. Desactiva o elimina cualquier dominio asociado, ya que los workers de colas no reciben peticiones HTTP.
5. **Variables de Entorno:**
   * Comparte o copia las mismas variables de entorno de la aplicación web principal.

---

### Servicio 3: Cron (Ejecutor del Planificador / Scheduler)

1. Haz clic en **New** -> **GitHub Repo** y selecciona el mismo repositorio.
2. Cambia el nombre del servicio a **Cron**.
3. **Configuración de Despliegue (Deploy):**
   * Ve a la pestaña **Settings** -> sección **Deploy** -> localiza **Start Command**.
   * Activa el checkbox para usar un comando personalizado y escribe:
     ```bash
     chmod +x railway/run-cron.sh && ./railway/run-cron.sh
     ```
4. **Configuración de Red (Networking):**
   * **IMPORTANTE:** Tampoco generes dominios públicos para este servicio.
5. **Variables de Entorno:**
   * Comparte o copia las mismas variables de entorno.

---

## 3. Configuración de Variables de Entorno

Puedes copiar y pegar las variables desde el archivo [.env.railway](file:///.env.railway) en la opción **Raw Editor** de la pestaña **Variables** de cada servicio, o utilizar la opción de variables compartidas.

### Variables Críticas que se Enlazan Automáticamente:
Railway resolverá la sintaxis `${{MYSQL...}}` directamente a la base de datos MySQL creada en el mismo entorno:
* `DB_HOST=${{MYSQLHOST}}`
* `DB_PORT=${{MYSQLPORT}}`
* `DB_DATABASE=${{MYSQLDATABASE}}`
* `DB_USERNAME=${{MYSQLUSER}}`
* `DB_PASSWORD=${{MYSQLPASSWORD}}`

### Variables que debes Configurar Manualmente:
* `APP_KEY`: Clave única de cifrado de Laravel. Puedes generarla en tu terminal local con `php artisan key:generate --show` y pegarla en Railway.
* `SAM_URL`: Dirección de la API REST del SAM (`http://10.156.1.145:8090/SAM`).

---

## 4. Monitoreo y Verificación

1. **Deployments:** Verifica que el despliegue del servicio **App** ejecute el Release Command (`init-app.sh`) de manera exitosa, lo cual confirmará que las migraciones corrieron contra la base de datos de Railway.
2. **Logs:**
   * Revisa los logs del servicio **App** para comprobar la llegada de peticiones HTTP en la ruta `/up` y `/`.
   * Revisa los logs del servicio **Worker** para verificar que esté listo escuchando la base de datos (`gama_jobs`).
   * Revisa los logs del servicio **Cron** para verificar que el bucle ejecute `php artisan schedule:run` cada 60 segundos.
