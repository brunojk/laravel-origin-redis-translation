laravel-origin-redis-translation
================================

Redis Translator for new pattern keys: 

```php
// like origin.locale.context.key`
$fullkey = 'app.en.default.hello_world';

// usage
$translated = trans($fullkey);
```