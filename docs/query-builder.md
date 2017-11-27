# Felicity Architect Query Builder

To get the query builder, you can use the static function `felicity\architect\Architect::get()`.

If you'd like to dependency inject the `Architect` class so that you can get a new instance of the query builder any time in a dependency injected class, you can use `felicity\architect\Architect::getInstance()`. When using the instance, you can use the `getBuilder()` non-static method.

The Felicity Architect Query Builder extends [Pixie Query Builder](https://github.com/usmanhalalit/pixie) and can do everything that the Pixie Query Builder can do. Those docs are not re-iterated here so go there to learn about 99% of the functionality. However, Felicity Architect Query Builder does add a couple of things:

## Audit Columns

Because the Felicity Schema Builder automatically adds the audit columns `dateCreated`, `dateUpdated`, and `uid`, the `insert` and `update` functions have been extended/updated to automatically handle updating the values of those columns unless you use a second parameter on either function to disable updating those columns.

## tableExists()

You can check if a table exists by using:

```php
<?php

$tableExists = felicity\architect\Architect::get()->tableExists('myTable');
```

## columnExists()

You can check if a column exists on a table by using:

```php
<?php

$columnExists = felicity\architect\Architect::get()->columnExists('myColumn', 'myTable');
```
