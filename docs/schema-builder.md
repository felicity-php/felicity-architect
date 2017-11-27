# Felicity Schema Builder

To get the schema builder, you can use the static function `felicity\architect\Architect::schemaBuilder()`.

If you'd like to dependency inject the `Architect` class so that you can get a new instance of the schema builder any time in a dependency injected class, you can use `felicity\architect\Architect::getInstance()`. When using the instance, you can use the `getSchemaBuilder()` non-static method.

## Usage

The schema builder allows you to create, alter, and drop tables. For examples, see the test: [SchemaBuilderTest.php](../tests/services/SchemaBuilderTest.php)
