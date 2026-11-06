# Actualizaciones de la base de datos

`ddl/sigeru_ddl.sql` **borra toda la base** y la crea vacía. Solo se usa la primera vez que se instala
el sistema en una computadora.

Cuando cambia la estructura de la base (una tabla o columna nueva), el cambio se agrega acá en un archivo
numerado, por ejemplo `001_columna_nueva.sql`. Esos archivos **agregan lo nuevo sin borrar los datos**.

Cómo se usan:

1. Entrar a http://localhost/phpmyadmin y elegir la base **sigeru** (a la izquierda).
2. **Importar** → elegir el archivo de actualización → **Importar**.
3. Se importa cada archivo una sola vez, en orden.

Hasta ahora no hay actualizaciones: la base que se creó con `ddl/sigeru_ddl.sql` ya está al día.
