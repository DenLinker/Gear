<?php

namespace gear;

defined('GEAR') or define('GEAR', __DIR__);
defined('DEBUG') or define('DEBUG', false);

/**
 * Ядро Gear Framework
 * 
 * @package Gear Framework
 * @final
 * @author Denisk Kukushkin
 * @copyright Denisk Kukushkin 2013
 * @version 1.0.0
 * @since 01.08.2013
 * @license MIT
 */
final class Core
{
    /* Const */
    const HTTP = 1;
    const CLI = 2;
    const MODE_DEVELOPMENT = 1;
    const MODE_PRODUCTION = 2;
    const ACCESS_PRIVATE = 0;
    const ACCESS_PROTECTED = 1;
    const ACCESS_PUBLIC = 2;
    /* Private */
    private static $_config =           // Текущая конфигурация ядра
    [         
        'preloads' => 
        [
            '\gear\library\GException',
            '\gear\library\GEvent',
            '\gear\CoreException',
            '\gear\interfaces\IModule',
            '\gear\interfaces\IComponent',
            '\gear\interfaces\IPlugin',
            '\gear\library\GObject',
            '\gear\library\GModule',
            '\gear\library\GComponent',
            '\gear\library\GPlugin',
            '\gear\interfaces\ILoader',
            '\gear\GServicesContainer',
/*            'library' => 
            [
                '\gear\library\GException',
                '\gear\library\GEvent',
                '\gear\CoreException',
                '\gear\interfaces\IModule',
                '\gear\interfaces\IComponent',
                '\gear\interfaces\IPlugin',
                '\gear\library\GObject',
                '\gear\library\GModule',
                '\gear\library\GComponent',
                '\gear\library\GPlugin',
                '\gear\interfaces\ILoader',
                '\gear\GServiceContainer',
            ],
            'modules' => [],
            'components' =>
            [
                'syslog' => ['class' => '\gear\components\gear\syslog\GSyslog'],
                // Автозагрузчик классов
                'loader' => 
                [
                    'class' => '\gear\components\gear\loader\GLoader',
                    'aliases' => 
                    [
                        'Arrays' => ['class' => 'gear\helpers\GArray'],
                        'Calendar' => ['class' => 'gear\helpers\GCalendar'],
                        'Html' => ['class' => 'gear\helpers\GHtml'],
                    ],
                ],
                // Обработчик ошибок
                'errorHandler' => ['class' => '\gear\components\gear\handlers\GErrorsHandler'],
                // Обработчик неперехваченных исключений
                'exceptionHandler' => ['class' => '\gear\components\gear\handlers\GExceptionsHandler'],
            ],*/
        ],
        'modules' => [],
        'components' => 
        [
            'syslog' => 
            [
                'class' => '\gear\components\gear\syslog\GSyslog',
                'autoload' => true,
            ],
            // Автозагрузчик классов
            'loader' => 
            [
                'class' => '\gear\components\gear\loader\GLoader',
                'aliases' => 
                [
                    'Arrays' => ['class' => 'gear\helpers\GArray'],
                    'Calendar' => ['class' => 'gear\helpers\GCalendar'],
                    'Html' => ['class' => 'gear\helpers\GHtml'],
                ],
                'autoload' => true,
            ],
            // Обработчик ошибок
            'errorHandler' => 
            [
                'class' => '\gear\components\gear\handlers\GErrorsHandler',
                'autoload' => true,
            ],
            // Обработчик неперехваченных исключений
            'exceptionHandler' => 
            [
                'class' => '\gear\components\gear\handlers\GExceptionsHandler',
                'autoload' => true,
            ],
        ],
        'params' => 
        [
            'baseDir' => GEAR, 
            'locale' => 'ru_RU',
            'encoding' => 'utf-8',
            'services' => ['class' => '\gear\GServicesContainer'],
        ],
    ];
    private static $_modules = [];      // Подключенные модули
    private static $_components = [];   // Подключённые компоненты
    private static $_events = [];       // Обработчики событий
    private static $_coreMode = null;   // Режим запуска PRODUCTION или DEVELOPMENT
    private static $_runMode = null;    // Окружение: http или консоль
    private static $_version = '1.0.0'; // Версия ядра
    /* Protected */
    /* Public */
    
    public static function __callStatic($name, $args)
    {
        if (self::isModuleRegistered($name))
            return self::m($name);
        if (self::isComponentRegistered($name))
            return self::c($name, count($args) ? $args[0] : false);
        if (isset(self::$_config['helpers'][$name]))
        {
            array_unshift($args, $name);
            return call_user_func_array([__CLASS__, 'h'], $args);
        }
        array_unshift($args, $name);
        return call_user_func_array(array(__CLASS__, 'params'), $args);
            
    }
    
    public static function getConfig() { return self::$_config; }
    
    public function services()
    {
        $services = self::params('services');
        if (!$services)
            self::e('Services container not defined');
        else
        if (!is_object($services))
        {
            list($class, $config, $properties) = self::getRecords($services);
            $services = new $class($properties);
            self::params('services', $services);
        }
        return $services;
    }
    
    /**
     * Инициализация ядра
     * 
     * @access public
     * @static
     * @param string as path to configuration file|array of configuration $config
     * @param integer Core::MODE_DEVELOPMENT|Core::MODE_PRODUCTION $coreMode
     * @throws \Exception
     * @return boolean
     */
    public static function init($config = null, $coreMode = self::MODE_DEVELOPMENT)
    {
        $modes = [self::MODE_DEVELOPMENT => 'debug', self::MODE_PRODUCTION => 'production'];
        self::$_coreMode = $coreMode;
        if ($config === null)
            $config = dirname($_SERVER['SCRIPT_FILENAME']) . '/config.' . $modes[self::$_coreMode] . '.php';
        if (is_string($config))
        {
        echo 'ss';
            $fileConfig = self::resolvePath($config, true);
            if (is_dir($fileConfig))
                $fileConfig .= '/config.' . $modes[self::$_coreMode] . '.php';
        echo 'ss';
            $config = is_file($fileConfig) ? require($fileConfig) : null;
        echo 'ss';
        }
        echo 'ss';
        if (!is_array($config))
            $config = ['modules' => ['app' => ['class' => '\gear\library\GApplication']]];
        echo 'ss';
        self::$_config = array_replace_recursive(self::$_config, $config);
        self::_preloads();
        foreach(self::$_config as $sectionName => $section)
        {
            if ($sectionName != 'params' && $sectionName != 'preloads')
            {
                $serviceLocation = self::class . '.' . $sectionName;
                foreach($section as $serviceName => $service)
                {
                    $serviceLocation .= '.' . $serviceName;
                    \gear\GServiceContainer::registerService($serviceLocation, $service);
                }
            }
        }
        return true;
    }
    
    /**
     * Подгрузка необходимых библиотек, модулей и компонентов на этапе
     * конфигурации ядра
     * 
     * @access private
     * @static
     * @return boolean
     */
    private static function _preloads()
    {
        if (isset(self::$_config['preloads']))
        {
            foreach(self::$_config['preloads'] as $preload)
            {
                $pathFile = self::resolvePath($preload, true) . '.php';
                if (!file_exists($pathFile))
                    self::e('File ":pathFile" not found', array('pathFile' => $pathFile));
                require($pathFile);
            }
        }
        return true;
        foreach(self::$_config['preloads'] as $section => $preloads)
        {
            foreach($preloads as $preloadName => $preload)
            {
                switch($section)
                {
                    case 'library' :
                    {
                        $pathFile = self::resolvePath($preload) . '.php';
                        if (!file_exists($pathFile))
                            self::e('File ":pathFile" not found', array('pathFile' => $pathFile));
                        require($pathFile);
                        break;
                    }
                    case 'modules' :
                    case 'components' :
                    {
                        list($class, $config, $properties) = self::getRecords($preload);
                        $pathFile = self::resolvePath($class) . '.php';
                        if (!file_exists($pathFile))
                            self::e('File ":preloadName" not found', array('preloadName' => $preloadName));
                        require($pathFile);
                        self::${'_' . $section}[$preloadName] = $class::install($config, $properties);
                    }
                }
            }
        }
        return true;
    }

    /**
     * Получение доступа к модулю приложения
     * 
     * @access public
     * @static
     * @return \gear\library\GApplication
     */
    public static function app() { return self::m('app'); }
    
    /**
     * Вызов компонента реализцющего системное протоколирование
     * Производит протоколирование оперций в случае, если такой компонент
     * установлен и константа DEBUG установлена в TRUE
     * 
     * @access public
     * @static
     * @return void
     */
    public static function syslog()
    {
        if (self::isComponentInstalled('syslog'))
            call_user_func_array(self::c('syslog'), func_get_args());
    }
    
    /**
     * Получение или установка значения для глобального аргумента
     * 
     * @access public
     * @static
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function params($name, $value = null)
    {
        if ($value === null)
            return isset(self::$_config['params'][$name]) ? self::$_config['params'][$name] : null;
        self::$_config['params'][$name] = $value;
        return $value;
    }
    
    /**
     * Получение модуля
     * 
     * @access public
     * @static
     * @param string $name
     * @return \gear\library\GModule
     */
    public static function m($name)
    {
        return self::services()->getRegisteredService(self::class . '.modules.' . $name);
        if (!isset(self::$_modules[$name]))
        {
            if (!($module = self::isModuleRegistered($name)))
                self::e('Module ":moduleName" is not registered', array('moduleName' => $name));
            self::installModule($name, $module);
        }
        return self::$_modules[$name];
    }
    
    /**
     * Возвращает запись модуля, если таковой зарегистрирован иначе false
     * 
     * @access public
     * @static
     * @param string $name
     * @return false|array
     */
    public static function isModuleRegistered($name)
    {
        return self::services()->isRegisteredService(self::class . '.modules.' . $name);
        if (isset(self::$_config['preloads']['modules'][$name]))
            return self::$_config['preloads']['modules'][$name];
        else
        if (isset(self::$_config['modules'][$name]))
            return self::$_config['modules'][$name];
        else
            return false;
    }
    
    /**
     * Регистрация модуля
     * 
     * @access public
     * @static
     * @param string $name
     * @param array $module
     * @throws \Exception
     * @return true
     */
    public static function registerModule($name, array $module)
    {
        return self::services()->registerService(self::class . '.modules.' . $name, $module);
        if ($existModule = self::isModuleRegistered($name))
        {
            if (!isset($existModule['override']) || !$existModule['override'])
                self::e('Module ":moduleName" can not be overloaded', array('moduleName' => $name));
        }
        self::$_config['modules'][$name] = $module;
        return true;
    }
    
    /**
     * Возвращает установленный модуль или false иначе
     * 
     * @access public
     * @static
     * @param string $name
     * @return null|object
     */
    public static function isModuleInstalled($name)
    {
        return self::services()->isInstalledService(self::class . '.modules.' . $name);
        return isset(self::$_modules[$name]) ? self::$_modules[$name] : null;
    }
    
    /**
     * Установка модуля
     * 
     * @access public
     * @static
     * @param string $name
     * @param array $module
     * @param null|object $owner
     * @return \gear\library\GModule
     */
    public static function installModule($name, array $module)
    {
        return self::services()->installService(self::class . '.modules.' . $name, $module);
        if (isset(self::$_modules[$name]))
        {
            if (!self::$_modules[$name]->hasOverride())
                self::e('Module ":moduleName" can not be overloaded', array('moduleName' => $name));
            self::uninstallModule($name);
        }
        return self::$_modules[$name] = self::_processElement($module);
    }
    
    /**
     * Удаление установленного модуля
     * 
     * @access public
     * @static
     * @param string $name
     * @return boolean
     */
    public static function uninstallModule($name)
    {
        self::params('services')->uninstallService(self::class . '.modules.' . $name);
        return true;
        if (isset(self::$_modules[$name]))
        {
            self::$_modules[$name]->event('onUninstall');
            unset(self::$_modules[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Возвращает список модулей
     * 
     * @access public
     * @static
     * @param bool $instances
     * @return array
     */
    public static function getModules($instances = false)
    {
        $modules = array();
        return $modules;
    }

    /**
     * Получение компонента
     * 
     * @access public
     * @static
     * @param string $name
     * @return \gear\library\GComponent
     */
    public static function c($name, $instance = false)
    {
        return self::params('services')->getRegisteredService(self::class . '.components.' . $name, $instance);
        if (!isset(self::$_components[$name]))
        {
            if (!($component = self::isComponentRegistered($name)))
                self::e('Component ":componentName" is not registered', array('componentName' => $name));
            self::installComponent($name, $component);
        }
        if ($instance)
        {
            $component = clone self::$_components[$name];
            return is_object($instance) ? $component->setOwner($instance) : $component;
        } 
        return self::$_components[$name];
    }
    
    /**
     * Возвращает запись компонента, если таковой зарегистрирован иначе false
     * 
     * @access public
     * @static
     * @param string $name
     * @return false|array
     */
    public static function isComponentRegistered($name)
    {
        return self::params('services')->isRegisteredService(self::class . '.components.' . $name);
        if (isset(self::$_config['preloads']['components'][$name]))
            return self::$_config['preloads']['components'][$name];
        else
        if (isset(self::$_config['components'][$name]))
            return self::$_config['components'][$name];
        else
            return false;
    }
    
    /**
     * Регистрация компонента
     * 
     * @access public
     * @static
     * @param string $name
     * @param array $module
     * @throws \Exception
     * @return true
     */
    public static function registerComponent($name, array $component)
    {
        return self::services()->registerService(self::class . '.components.' . $name, $component);
        if ($c = self::isComponentRegistered($name))
        {
            if (!isset($c['override']) || !$c['override'])
                self::e('Component ":componentName" can not be overloaded', array('componentName' => $name));
        }
        self::$_config['components'][$name] = $component;
        return true;
    }
    
    /**
     * Возвращает установленный компонент или false иначе
     * 
     * @access public
     * @static
     * @param string $name
     * @return object|false
     */
    public static function isComponentInstalled($name)
    {
        return self::services()->isInstalledService(self::class . '.components.' . $name);
        return isset(self::$_components[$name]) ? self::$_components[$name] : null;
    }
    
    /**
     * Установка компонента
     * 
     * @access public
     * @static
     * @param string $name
     * @param array $module
     * @param null|object $owner
     * @return \gear\library\GModule
     */
    public static function installComponent($name, array $component, $owner = null)
    {
        if ($owner)
            $properties['owner'] = $owner;
        return self::services()->installService(self::class . '.components.' . $name, $component);
        if (isset(self::$_components[$name]))
        {
            if (!self::$_components[$name]->hasOverride())
                self::e('Component ":componentName" can not be overloaded', array('componentName' => $name));
            self::uninstallComponent($name);
        }
        return self::$_components[$name] = self::_processElement($component, $owner);
    }
    
    /**
     * Удаление установленного компонента
     * 
     * @access public
     * @static
     * @param string $name
     * @return boolean
     */
    public static function uninstallComponent($name)
    {
        self::params('services')->uninstallService(self::class . '.components.' . $name);
        if (isset(self::$_components[$name]))
        {
            self::$_components[$name]->event('onUninstall');
            unset(self::$_components[$name]);
            return true;
        }
        return false;
    }

    /**
     * Обработка элемента (получение и устновка модуля или компонента)
     * 
     * @access private
     * @static
     * @param array $element
     * @param null|object $owner
     * @return object
     */
    private static function _processElement(array $element, $owner = null)
    {
        list($class, $config, $properties) = self::getRecords($element);
        return $class::install($config, $properties, $owner);
    }
    
    /**
     * генерация события
     * 
     * @access public
     * @static
     * @param string $name
     * @param object $event
     * @return void
     */
    public static function event($name, $event)
    {
        $result = false;
        if (isset(self::$_events[$name]))
        {
            $args = func_get_args();
            array_shift($args);
            if (!$event)
                $args[0] = new \gear\library\GEvent(null);
            foreach(self::$_events[$name] as $handler)
            {
                $result = call_user_func_array($handler, $args);
                if (($result instanceof \gear\library\GEvent && 
                    $result->stopPropagation === true) || !$result)
                    break;
            }
        }
        return $result;
    }
    
    /**
     * Добавление обработчика события
     * 
     * @access public
     * @static
     * @param string $eventName
     * @param mixed $handler callable value
     * @return boolean
     */
    public static function attachEvent($eventName, $handler)
    {
        if (!is_callable($handler))
            self::e('Invalid handler of event ":eventName"', ['eventName' => $eventName]);
        self::$_events[$eventName][] = $handler;
        return true;
    }
    
    /**
     * Получение значений класса, конфигурации и свойств из
     * специально сформированной структуры
     * 
     * @access public
     * @static
     * @param array $properties
     * @return array as Array(className, configuration, properties)
     */
    public static function getRecords(array $properties)
    {
        $class = $properties['class'];
        unset($properties['class']);
        $config = array();
        if (is_array($class))
        {
            $config = $class;
            $class = $config['name'];
            unset($config['name']);
            if (isset($config['#config']))
                $config = self::resolvePath($config['#config']);
        }
        if (isset($properties['#import']))
        {
            if (is_array($properties['#import']))
            {
                foreach($properties['#import'] as $nameArg)
                    $properties = array_merge($properties, self::arg($properties['#import']));
            }
            else
                $properties = array_merge($properties, self::arg($properties['#import']));
            unset($properties['#import']);
        }
        return array($class, $config, $properties);
    }
    
    /**
     * Получение физического пути для указанного пространства имён.
     * 
     * @access public
     * @static
     * @param string $path
     * @return null|string
     */
    public static function resolvePath($path, $internalResolver = false)
    {
        $resolved = null;
        if (!$internalResolver)
        {
            if (self::isComponentInstalled('loader') && method_exists(Core::c('loader'), 'resolvePath'))
                $resolved = Core::c('loader')->resolvePath($path);
        }
        else
        {
            /* Абсолютный путь */
            if (preg_match('/^[a-zA-Z]{1}\:/', $path) || $path[0] === '/')
                $resolved = $path;
            /* Относительный путь или пространство имён */
            else
            if ($path[0] === '\\')
                $resolved = GEAR . '/..' . str_replace('\\', '/', $path);
            else
            {
                $resolved = GEAR . '/..' . (Core::isModuleInstalled('app') ? Core::app()->getNamespace() . '/' : '/gear/')
                          . str_replace('\\', '/', $path);
            }
        }
        return $resolved;
    }
    
    /**
     * Возвращает режим запуска приложения
     * 
     * @access public
     * @static
     * @return boolean
     */
    public static function getMode() 
    { 
        return self::$_runMode ? self::$_runMode : (self::$_runMode = php_sapi_name() === 'cli' ? self::CLI : self::HTTP); 
    }
    
    /**
     * Возвращает true если приложение запущено из браузера, иначе false
     * 
     * @access public
     * @static
     * @return boolean
     */
    public static function isHttp() { return self::getMode() === self::HTTP; }
    
    /**
     * Возвращает true если приложение запущено из консоли, иначе false
     * 
     * @access public
     * @static
     * @return boolean
     */
    public static function isCli() { return self::getMode() === self::CLI; }
    
    /**
     * Возвращает версию ядра фреймворка
     * 
     * @access public
     * @static
     * @return string
     */
    public static function getVersion()
    {
        return self::$_version;
    }
    
    /**
     * Генерация исключения
     * 
     * @access public
     * @static
     * @param string $message
     * @param array $params
     * @throws \Exception|\gear\CoreException
     * @return void
     */
    public static function e($message, array $params = [])
    {
        if (!class_exists('\gear\CoreException', false))
        {
            foreach($params as $name => $value)
                $message = str_replace(':' . $name, $value, $message);
            throw new \Exception($message);
        }
        else
            throw new \gear\CoreException($message, $params);
    }
    
    public static function dump($value)
    {
        echo '<pre>';
        if (is_array($value) || is_object($value))
            echo print_r($value, 1);
        else
            var_dump($value);
        echo '</pre>';
    }
}
