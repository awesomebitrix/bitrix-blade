<?php

namespace Arrilot\BitrixBlade;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;

class BladeProvider
{
    /**
     * Path to a folder view common view can be stored.
     *
     * @var string
     */
    protected static $baseViewPath;

    /**
     * Local path to blade cache storage.
     *
     * @var string
     */
    protected static $cachePath;

    /**
     * View factory.
     *
     * @var Factory
     */
    protected static $viewFactory;

    /**
     * Service container factory.
     *
     * @var Container
     */
    protected static $container;

    /**
     * Register blade engine in Bitrix.
     *
     * @param string $baseViewPath
     * @param string $cachePath
     */
    public static function register($baseViewPath = 'local/views', $cachePath = 'bitrix/cache/blade')
    {
        static::$baseViewPath = $_SERVER['DOCUMENT_ROOT'].'/'.$baseViewPath;
        static::$cachePath = $_SERVER['DOCUMENT_ROOT'].'/'.$cachePath;

        static::instantiateServiceContainer();
        static::instantiateViewFactory();
        static::registerBitrixDirectives();

        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['blade'] = [
            'templateExt' => ['blade'],
            'function'    => 'renderBladeTemplate',
        ];
    }

    /**
     * Get view factory.
     *
     * @return Factory
     */
    public static function getViewFactory()
    {
        return static::$viewFactory;
    }

    /**
     * @return BladeCompiler
     */
    public function getCompiler()
    {
        return static::$container['blade.compiler'];
    }

    /**
     * Update paths where blade tries to find additional views.
     *
     * @param string $templateDir
     */
    public static function updateViewPaths($templateDir)
    {
        $newPaths = [
            $_SERVER['DOCUMENT_ROOT'].$templateDir,
            static::$baseViewPath,
        ];

        $finder = Container::getInstance()->make('view.finder');
        $finder->setPaths($newPaths);
    }

    /**
     * Instantiate service container if it's not instantiated yet.
     */
    protected static function instantiateServiceContainer()
    {
        $container = Container::getInstance();

        if (!$container) {
            $container = new Container();
            Container::setInstance($container);
        }

        static::$container = $container;
    }

    /**
     * Instantiate view factory.
     */
    protected static function instantiateViewFactory()
    {
        static::createDirIfNotExist(static::$baseViewPath);
        static::createDirIfNotExist(static::$cachePath);

        $viewPaths = [
            static::$baseViewPath,
        ];
        $cache = static::$cachePath;

        $blade = new Blade($viewPaths, $cache, static::$container);

        static::$viewFactory = $blade->view();
        static::$viewFactory->addExtension('blade', 'blade');
    }

    /**
     * Create dir if it does not exist.
     *
     * @param string $path
     */
    protected static function createDirIfNotExist($path)
    {
        if (!file_exists($path)) {
            $mask = umask(0);
            mkdir($path, 0777, true);
            umask($mask);
        }
    }

    /**
     * Register bitrix directives.
     */
    protected static function registerBitrixDirectives()
    {
        $compiler = static::getCompiler();
        $compiler->directive('component', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php $APPLICATION->IncludeComponent('.$expression.'); ?>';
        });

        $compiler->directive('block', function ($expression) {
            $expression = rtrim($expression, ')');
            $expression = ltrim($expression, '(');

            return '<?php ob_start(); $__bx_block = ' . $expression . '; ?>';
        });
    
        $compiler->directive('endblock', function () {
            return '<?php $APPLICATION->AddViewContent($__bx_block, ob_get_clean()); ?>';
        });
    }
}
