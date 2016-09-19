<?php
/**
 * Created by PhpStorm.
 * User: BrunoJK
 * Date: 19/09/2016
 * Time: 15:25
 */

namespace brunojk\LaravelOriginRedisTranslation;


trait TranslatorResolveKeys
{
    public function resolveKeys(&$id, &$context = null, &$lang = null) {
        if( strpos($id, '.') === false )
            $id = $context . '.' . $id;

        list($namespace, $group, $item) = $this->parseKey($id);

        $id = $item; //last element, the key
        $context = $group;
        $lang = $this->resolveLang($lang);

        $keyapp = "app.$lang.$context.$id";
        $keyplt = "plt.$lang.$context.$id";

        return [$keyapp, $keyplt];
    }
}