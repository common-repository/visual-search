# impresee-creative-search-bar-php-base
Código base para plugins de búsqueda en PHP

Para cargar este código en un proyecto php hay que opcupar composer ([https://getcomposer.org/](https://getcomposer.org/)) y seguir estos pasos:

1. El proyecto que ocupamos debe ser un proyecto composer. Para lograr esto debemos entrar por consola a la carpeta base y ejecutar:
`composer init` (o `php composer.phar init` dependiendo de como se instaló composer).
Esto va a generar un archivo `composer.json` en el raiz

2. Agregar lo siguiente al `composer.json`
```
    "repositories":[
        {
            "type": "vcs",
            "url": "https://github.com/Impresee/guzzle"
        },
        {
            "type": "vcs",
            "url": "git@github.com:Impresee/impresee-creative-search-bar-php-base.git"
        }
    ],
    "require": {
        "php": ">=7.0.0",
        "impresee/impresee-creative-search-bar-php-base": "1.0.x-dev"
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "config": {
        "preferred-install": "dist",
        "prepend-autoloader": false
    }
```
Esto asignará este paquete (y sus dependencias) al plugin que queramos ocupar. Como referencia se puede mirar el `composer.json` de [https://github.com/Impresee/woocommerce-plugin](https://github.com/Impresee/woocommerce-plugin).

4. Agregar la llave ssh de la máquina en la que se este trabajando a github (para poder clonar el repo a través de composer). Para hacer esto hay que ir a las settings de la cuenta personas > ssh and gpg keys y agregar ahi la llave de la maquina.

3. En la carpeta base del plugin ejecutar `composer update`. Esto descargará el repo `impresee-creative-search-bar-php-base` y lo dejará en la carpeta `vendor`. Con esto podemos empezar a utilizar este código como librería

4. Ejecutar `composer composer dump-autoload`. Esto va a generar los archivos necesarios para poder ocupar autoload.

5. En el archivo base del plugin (por ejemplo para woocommerce es este [https://github.com/Impresee/woocommerce-plugin/blob/master/impresee-creativesearch.php](https://github.com/Impresee/woocommerce-plugin/blob/master/impresee-creativesearch.php)) hay que agregar: 
```
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}
```
Notar que esto debe ser antes de cualquier referencia al namespace de alguna librería. Con esto vamos a poder ocupar autoload en el nuevo proyecto.

6. Cuando se haga algún cambio a este librería hay que volver al plugin y ejecutar `composer update`.

7. Cuando se vaya a subir el plugin a producción hay que borrar la carpeta `vendor` y ejecutar `composer install --no-dev`, para que no se instalen las dependencias de desarrollo. Además entramos a `vendor/impresee/impresee-creative-search-bar-php-base` y borramos la carpeta `tests`
