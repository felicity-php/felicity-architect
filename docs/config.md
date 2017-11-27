# Config

Felicity Architect makes use of [Felicity Config](https://github.com/felicity-php/felicity-config) so you can set up the database settings.

## Config Items

```php
<?php

use felicity\config\Config;

Config::set('felicity.architect', [
    'driver' => 'mysql', // `mysql` is default
    'host' => 'localhost', // `localhost` is default
    'database' => 'db_name',
    'username' => 'db_username',
    'password' => 'db_password',
    'charset' => 'utf8mb4', // `utf8mb4` is default
    'collation' => 'utf8mb4_general_ci', // `utf8mb4_general_ci` is default
    'prefix' => 'myprefix_',
]);
```
