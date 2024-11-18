# Gestión de Formación

![PHPUnit](https://github.com/VictorVaqueroUBU/TFG/actions/workflows/symfony-tests.yml/badge.svg?branch=main)
![PHPStan](https://github.com/VictorVaqueroUBU/TFG/actions/workflows/phpstan.yml/badge.svg?branch=main)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Symfony](https://img.shields.io/badge/Symfony-7.1-lightgrey)
![Licencia](https://img.shields.io/badge/licencia-MIT-green)

## Introducción

Este proyecto es una aplicación web desarrollada en **Symfony** para la gestión de cursos formativos, sus ediciones, participantes y formadores, en el ámbito universitario.
- Gestión de cursos: creación, edición, eliminación y visualización de cursos por año de impartición.
- Gestión de ediciones: gestión de las ediciones de estos cursos con datos específicos como fechas de impartición, plazas y modalidad de impartición.
- Gestión de participantes: gestión de participantes y sus inscripciones a las ediciones existentes con datos específicos como fechas de solicitud, aptitud, nota, y certificados
- Gestión de formadores: gestión de formadores y su asignación a ediciones con datos específicos como gestión de retribuciones y evaluaciones por parte de los alumnos.

<p align="left">
   <img src="assets/images/gestor.png" alt="Portal del Gestor" width="800">
</p>

<p align="left">
   <img src="assets/images/cursos.png" alt="Portal del Gestor" width="800">
</p>

<p align="left">
   <img src="assets/images/ediciones.png" alt="Portal del Gestor" width="800">
</p>

---

## Tecnologías utilizadas

- **PHP** 8.2 o superior
- **Symfony** 7.1
- **MySQL** 8.0
- **Composer**
- **Git**
- **Sequel Ace** (opcional, para la gestión visual de la base de datos)

---

## Instalación

1. Clona este repositorio en tu máquina local:

   ```bash
   git clone https://github.com/VictorVaqueroUBU/TFG.git
   ```

2. Ve al directorio del proyecto:

   ```bash
   cd tu-repositorio
   ```

3. Instala las dependencias con Composer:

   ```bash
   composer install
   ```
   
4. Instalar componentes JavaScript

    ```bash
      bin/console importmap:install
    ```

5. Instalar componentes JavaScript

    ```bash
      bin/console sass:build
    ```

6. Crear base de datos

    ```mysql
    CREATE DATABASE IF NOT EXISTS formacion;
    ```

7. Crear usuario

    ```mysql
    CREATE USER IF NOT EXISTS 'Usuario'@'localhost' IDENTIFIED BY 'Clave';
    ```

8. Dar acceso

    ```mysql
    GRANT ALL PRIVILEGES ON formacion.* TO 'Usuario'@'localhost';
    ```

9. Configura las variables de entorno en el archivo `.env`. Asegúrate de incluir la URL de tu base de datos:

   ```dotenv
   DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/formacion?serverVersion=8.0"
   ```

10. Realiza las migraciones de la base de datos:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

11. Levanta el servidor de desarrollo:

   ```bash
   symfony server:start
   ```

12. Accede a la aplicación en tu navegador en la dirección:

   ```
   https://localhost:8000/intranet/forpas/gestor
   ```

---

## Tests

Este proyecto utiliza:
- PHPUnit para las pruebas automatizadas.

Para ejecutar las pruebas: 

```bash
php bin/phpunit
```

- PHPStan para verificar posibles errores estáticos y asegurar la calidad del código.

```bash
php vendor/bin/phpstan analyse --configuration=phpstan.dist.neon > phpstan_report.txt
```

---

## Licencia

Este proyecto está bajo la licencia [MIT](https://opensource.org/licenses/MIT). Consulta el archivo `LICENSE` para más información.

---