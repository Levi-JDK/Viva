# Skill: create-sql-function

## Descripción
Esta skill está entrenada para diseñar, generar o refactorizar arquitecturas en base de datos PostgreSQL mediante funciones aisladas (Stored Procedures / Functions).

## Reglas de Construcción (Obligatorias)
1. **Nomenclatura de Operación**:
   - Creación: `fun_c_[nombre]`
   - Actualización: `fun_u_[nombre]`
   - Eliminación: `fun_d_[nombre]`
2. **Manejo de IDs en Funciones de Creación (`fun_c_`)**:
   - **IDs Numéricos (Enteros / Decimal)**: La función **NO** debe recibir la ID como parámetro. En su lugar, el `INSERT` debe calcularla automáticamente usando `COALESCE((SELECT MAX(columna_id) FROM tabla_destino), 0) + 1`.
   - **IDs Alfanuméricos (String / VARCHAR)**: Si la tabla usa un PK de texto (ej. `tab_color` con su hexadecimal), el parámetro **SÍ** se requiere. En la UI (panel de admin), este campo debe solicitarse con un nombre muy descriptivo (ej. "RGB" en lugar de "ID") para que el administrador entienda qué ingresar.
3. **Ubicación Fija**:
   - Todos los scripts resultantes se deben guardar forzosamente en `scripts/funciones_db/` o en sus subcarpetas correspondientes.
3. **Parámetros con Casting Directo (`%TYPE`)**:
   - **Regla de Oro**: Aunque la variable o entrada pura a nivel de aplicación sea tratada como un VARCHAR/JSON/escalar, **los parámetros de entrada directos en la firma de la función SQL DEBEN asignarse utilizando `tabla.columna%TYPE`**.
   - No crees variables clónicas redundantes en el `DECLARE`. El parámetro de entrada es la que usaremos directamente en el código de la función.
   - **PROHIBIDO EL CASTEO NATIVO (`::`)**: No utilices `::VARCHAR`, `::TEXT` ni similares bajo ninguna circunstancia, ni en la firma, ni en el cuerpo (tampoco para funciones como `btrim`). Confía en el motor de base de datos.
   Ejemplo Correcto:
   ```sql
   CREATE OR REPLACE FUNCTION fun_c_banco(
       p_id_banco tab_bancos.id_banco%TYPE,
       p_nom_banco tab_bancos.nom_banco%TYPE
   ) RETURNS BOOLEAN AS $$
   BEGIN
       ...
   END;
   $$ LANGUAGE plpgsql;
   ```
4. **Validaciones en Caliente**:
   - Antes de proceder con la transacción mutante pura (`INSERT`, `UPDATE`, `DELETE`), debes ejecutar en código PSQL reglas robustas y lógicas: confirmar existencias previas (`PERFORM 1 FROM tab...`), y validar nulidad de campos requeridos.
5. **Control de Retornos (`RETURNS`)**:
   - **Funciones API-Directas**: Si el backend/API consume esto directamente para mostrar datos en JSON relacionales, devuelve `JSON` (ej. casteos nativos en PSQL).
   - **Funciones Transaccionales Normales**: Para todo lo demás (updates, deletes), la función finaliza y devuelve exclusivamente un `BOOLEAN` (`TRUE` o `FALSE`) de forma limpia para ser evaluada por PHP.
