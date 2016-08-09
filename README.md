laravel-origin-redis-translation
================================

Redis Translator for new pattern keys: 

```php
// pattern origin.locale.context.key`
$fullkey = 'app.en.default.hello_world';

// usage (normal like laravel default)
$translated = trans($fullkey);
```