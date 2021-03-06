[![Latest Stable Version](https://poser.pugx.org/arrilot/bitrix-blade/v/stable.svg)](https://packagist.org/packages/arrilot/bitrix-blade/)
[![Total Downloads](https://img.shields.io/packagist/dt/arrilot/bitrix-blade.svg?style=flat)](https://packagist.org/packages/Arrilot/bitrix-blade)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/arrilot/bitrix-blade/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/arrilot/bitrix-blade/)

# Интеграция шаблонизатора Blade в Битрикс

## Установка

1)```composer require arrilot/bitrix-blade```

2) добавляем в init.php

```php

use Arrilot\BitrixBlade\BladeProvider;

require $_SERVER['DOCUMENT_ROOT']."/vendor/autoload.php";

BladeProvider::register();
```

## Использование

Заменяем шаблон компонента с `template.php` на `template.blade` и можно писать на `Blade`

Директива `@include('path.to.view')` модифицирована следующим образом:

1. Сначала view ищется относительно директории текущего шаблона компонента (там где лежит template.blade)
2. Если не view там не нашёлся, то он ищется относительно базовой директории (по умолчанию `local/view`, но может быть указана другая при вызове `BladeProvider::register()`)

## Пользовательские директивы (custom directives)

Для того чтобы добавить свою директиву, необходимо зарегистрировать её в компиляторе:

```
$compiler = BladeProvider::getCompiler();
$compiler->directive('directiveName', function ($expression) {
    return '...';
});
```
При установке пакета `BladeProvider::register()` за вас уже автоматически зарегистрированы две полезные директивы:

1. ```@component``` - аналог ```$APPLICATION->IncludeComponent()```
2. ```@block('key')``` и ```@endblock``` - всё что заключено между ними будет выведено в месте, где вызван метод ```$APPLICATION->ShowViewContent('key')```


## Некоторые моменты

1. Битрикс позволяет использовать сторонние шаблонизаторы только в шаблонах компонентов. Шаблоны сайтов только на php.
2. По понятным причинам наследованием шаблонов в полную силу воспользоваться не получится.
3. Традиционное расширение `.blade.php` использовать нельзя. Битрикс видя `.php` включает php движок.
4. Вместо `$this` в шаблоне следует использовать `$template` - например `$template->setFrameMode(true);`
5. Проверку `<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true) die();?>` прописывать в blade-шаблоне не нужно, она добавляется в скомпилированные view автоматически. Также вместе с этим выполняется и ```extract($arResult, EXTR_SKIP);```

## Дополнительно

Чтобы включить подсветку синтаксиса в PhpStorm для .blade файлов нужно добавить это расширение в
`Settings->Editor->File Types->Blade`
