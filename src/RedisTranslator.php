<?php

namespace brunojk\LaravelOriginRedisTranslation;

use Illuminate\Redis\Database;
use Illuminate\Support\Str;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorInterface;

class RedisTranslator implements TranslatorInterface
{
    /**
     * The default locale being used by the translator.
     *
     * @var string
     */
    protected $locale;

    /**
     * The fallback locale used by the translator.
     *
     * @var string
     */
    protected $fallback;

    /**
     * The message selector.
     *
     * @var \Symfony\Component\Translation\MessageSelector
     */
    protected $selector;

    /**
     * Create a new translator instance.
     *
     * @param  string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Database
     */
    protected function redis() {
        return app()->make('redis');
    }

    /**
     * Determine if a translation exists for a given locale.
     *
     * @param  string  $key
     * @param  string|null  $locale
     * @return bool
     */
    public function hasForLocale($key, $locale = null) {
        return $this->has($key, $locale, false);
    }

    /**
     * Determine if a translation exists.
     *
     * @param  string  $key
     * @param  string|null  $locale
     * @param  bool  $fallback
     * @return bool
     */
    public function has($key, $locale = null, $fallback = true) {
        return $this->get($key, [], $locale, $fallback) !== $key;
    }

    /**
     * @param null $lang
     * @return string
     */
    protected function resolveLang($lang = null) {
        return $lang ?: app()->getLocale();
    }

    protected function resolvedKeys(&$id, &$context = null, &$lang = null) {
        $keys = explode('.', $id);
        $id = array_pop($keys); //last element, the key
        $context = count($keys) ? array_pop($keys) : $context;
        $lang = count($keys) ? array_pop($keys) : $this->resolveLang($lang);

        $keyapp = "app.$lang.$context.$id";
        $keyplt = "plt.$lang.$context.$id";

        return [$keyapp, $keyplt];
    }

    public function get($id, array $parameters = [], $context = null, $lang = null) {
        $keys = $this->resolvedKeys($id, $context, $lang);
        $res = null;

        foreach( $keys as $key ) {
            $res = $this->redis()->get($key);
            if( !empty($res) ) break;
        }

        if( empty($res) && $lang != $this->fallback )
            return $this->get($id, $parameters, $context, $this->fallback);

        $res = !empty($res) ?
            $this->makeReplacements($res, $parameters) :
            "$context.$id";

        return $res;
    }

    /**
     * Get a translation according to an integer value.
     *
     * @param $id
     * @param int $number
     * @param array $parameters
     * @param null $context
     * @param  string $locale
     * @return string
     */
    public function choice($id, $number, array $parameters = [], $context = null, $locale = null) {
        $line = $this->get($id, $parameters, $context, $locale);

        return $this->makeReplacements($this->getSelector()->choose($line, $number, $locale), $parameters);
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @param  array   $replace
     * @return string
     */
    protected function makeReplacements($line, array $replace)
    {
        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.$key, ':'.Str::upper($key), ':'.Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $line
            );
        }

        return $line;
    }

    /**
     * Get the translation for a given key.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string|array|null
     */
    public function trans($id, array $parameters = [], $domain = 'messages', $locale = null) {
        return $this->get($id, $parameters, !$domain || $domain == 'messages' ? 'default' : $domain, $locale);
    }

    /**
     * Get a translation according to an integer value.
     *
     * @param  string  $id
     * @param  int|array|\Countable  $number
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    public function transChoice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {
        return $this->choice($id, $number, $parameters, !$domain || $domain == 'messages' ? 'default' : $domain, $locale);
    }


    /**
     * Get the message selector instance.
     *
     * @return \Symfony\Component\Translation\MessageSelector
     */
    public function getSelector()
    {
        if (! isset($this->selector)) {
            $this->selector = new MessageSelector;
        }

        return $this->selector;
    }

    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function locale()
    {
        return $this->getLocale();
    }

    /**
     * Get the default locale being used.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the default locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get the fallback locale being used.
     *
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * Set the fallback locale being used.
     *
     * @param  string  $fallback
     * @return void
     */
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }
}
