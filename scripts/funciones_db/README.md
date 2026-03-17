# Documentación de las APIs (VIVA)

Esta carpeta contiene todos los endpoints "headless" (APIs) que interactúan con la base de datos y devuelven datos limpios (en formato JSON o bloques HTML parciales) para ser consumidos asíncronamente desde el *Frontend* mediante Javascript (`fetch` o `AJAX`).

A continuación se detallan las APIs disponibles, los métodos admitidos, qué parámetros esperan y qué responden.

---

## 1. Catálogo Dinámico de Productos
**Archivo:** `productos.php`  
**Ruta de Enrutador (Recomendada):** `GET /api/productos`  
**Ruta Física:** `GET src/api/productos.php`

**Descripción:**  
Recibe un conjunto de filtros opcionales por la URL y devuelve un JSON con el listado de productos. El frontend (`catalogo.js`) se encarga de construir el HTML de las tarjetas a partir de estos datos.

**Parámetros Aceptados (Querystring - GET):**
Todos son opcionales.

*   `q` *(String)*: Término de búsqueda de texto (busca en el nombre y la descripción).
*   `cat` *(Integer)*: ID de la Categoría.
*   `oficio` *(Integer)*: ID del Oficio Artesanal.
*   `materia` *(Integer)*: ID de la Materia Prima.
*   `min_price` *(Float)*: Precio mínimo.
*   `max_price` *(Float)*: Precio máximo.

**Respuesta devuelta (JSON):**
*   **Éxito con resultados (`HTTP 200`)**:
    ```json
    {
      "success": true,
      "total": 12,
      "data": [
        {
          "id_producto": 1,
          "nom_producto": "Mochila Wayuu",
          "precio_producto": 120000,
          "id_productor": 5,
          "nom_stand": "Artesanías del Norte",
          "img_stand": "http://localhost/viva/images/stands/logo.jpg",
          "imagen_producto": "http://localhost/viva/images/productos/mochila.jpg",
          "url_producto": "http://localhost/viva/producto?id=1",
          "url_stand": "http://localhost/viva/stand/5"
        }
      ]
    }
    ```
*   **Sin resultados (`HTTP 200`)**: `{ "success": true, "total": 0, "data": [] }`
*   **Error (`HTTP 500`)**: `{ "success": false, "message": "...", "data": [] }`


---

## 2. Buscador de Ciudades
**Archivo:** `get_ciudades.php`  
**Ruta Física:** `GET src/api/get_ciudades.php`

**Descripción:**  
Se utiliza comúnmente en los formularios de registro de vendedores para llenar dinámicamente el cuadro desplegable `<select>` de "Ciudad/Municipio" una vez que el usuario elige un "Departamento".

**Parámetros Aceptados (GET o POST):**
*   `id_departamento` *(Integer)* **Obligatorio**: El ID del departamento del cual se quieren descargar las ciudades.

**Respuesta devuelta (JSON):**
*   **Éxito (`HTTP 200`)**: 
    ```json
    {
      "success": true,
      "data": [
        {"id_ciudad": 1, "nom_ciudad": "Barranquilla"},
        {"id_ciudad": 2, "nom_ciudad": "Soledad"}
      ]
    }
    ```
*   **Error (`HTTP 500`)**: (Ej. Faltó el parámetro)
    ```json
    {
      "success": false,
      "message": "ID de departamento no proporcionado"
    }
    ```

---

## 3. Registro de Productor (Vendedor)
**Archivo:** `post_registro_vendedor.php`  
**Ruta Física:** `POST src/api/post_registro_vendedor.php`

**Descripción:**  
Recibe los datos del formulario de inscripción para convertir a un "Usuario Normal" (comprador) en un "Usuario Productor" (que puede vender artesanías). 
*Nota: Exige de forma estricta que exista una sesión iniciada activa en PHP (`$_SESSION['id_user']`).*

**Módulo Admitido:** Únicamente `POST`.  

**Parámetros Aceptados (Enviados en el cuerpo - FormData):**
Todos los campos abajo en listados son **Obligatorios**. Si falta alguno, la petición fracasará.

*   `tipo_documento` *(Integer)*: ID del documento (Ej. CC, CE, NIT).
*   `numero_documento` *(String)*: El número de cédula sin puntos ni comas.
*   `direccion` *(String)*: La dirección física de residencia o taller.
*   `departamento` *(Integer)*: ID del departamento en la base de datos.
*   `ciudad` *(Integer)*: ID del municipio o ciudad.
*   `grupo_artesanal` *(Integer)*: ID del grupo étnico o poblacional artesano al que pertenece.
*   `banco` *(Integer)*: ID de la entidad bancaria.
*   `tipo_cuenta` *(String)*: El texto exacto `"Ahorros"` o `"Corriente"`.
*   `numero_cuenta` *(String)*: Número de la cuenta bancaria donde recibirá los pagos.

**Respuesta devuelta (JSON):**
*   **Registro Exitoso (`HTTP 200`)**: 
    ```json
    {
      "success": true,
      "message": "¡Registro exitoso! Bienvenido a VIVA.",
      "debug_params": {...} 
    }
    ```
*   **Fallo de Validación o de Reglas SQL (`HTTP 200`)**: (Ej. El número de identificación ya estaba registrado previamente.)
    ```json
    {
      "success": false,
      "message": "No se pudo completar el registro. Verifique sus datos.",
      "debug_params": {...} 
    }
    ```
*   **Fallo de Seguridad (`HTTP 401`)**: Si la sesión de usuario expiró.
    ```json
    {
      "success": false,
      "message": "Usuario no autenticado. Por favor inicie sesión."
    }
    ```
*   **Fallo de Método (`HTTP 405`)**: Si se intentó acceder por la URL directo (`GET`) en lugar de enviar un formulario.
    ```json
    {
      "success": false,
      "message": "Método no permitido"
    }
    ```
