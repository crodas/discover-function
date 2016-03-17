# Function Discovery

Discover PHP functions/methods in any directory using annotations. It walks throught the file system looking for annotations and builds a map to reuse later.

## How to use

```php

require __DIR__ . '/vendor/autoload.php';

/** @API my_name */
function foobar(Array $args) {
  return $args[0];
}

$apis = new FunctionDiscovery(__DIR__);
$functions = $apis->getFunctions('@api');
echo $functions['my_name']($arg1, $arg2);
```
