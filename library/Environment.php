<?php

/**
 * A helper class to tell us more about the application environment in a Zend Framework
 * application.
 */
final class ZFE_Environment
{
    // The application autoloader
    private static $autoloader;

    /**
     * Get the application autoloader
     *
     * This is the autoloader of class Zend_Application_Module_Autoloader.
     * In a default Zend application, there is one.
     * TODO I am not sure about modularized Zend applications - maybe there is
     * one per module, but I don't have a modularized example to test this with
     */
    public static function getApplicationAutoloader()
    {
        if (is_null(self::$autoloader)) {
            $autoloader = Zend_Loader_Autoloader::getInstance();
            $autoloaders = $autoloader->getAutoloaders();

            self::$autoloader = array_reduce($autoloader->getAutoloaders(), function($u, $v) {
                return $u instanceof Zend_Application_Module_Autoloader ? $u : $v;
            });
        }

        return self::$autoloader;
    }

    /**
     * Get the application namespace
     *
     * It takes the namespace set in the application's autoloader and
     * returns this.
     */
    public static function getApplicationNamespace()
    {
        $autoloader = self::getApplicationAutoloader();

        return $autoloader ? $autoloader->getNamespace() : '';
    }

    public static function getModuleName()
    {
        if (php_sapi_name() == 'cli') {
            $app = Zend_Registry::get('CliApplication');

            return $app->getModule();
        }

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        return $request->getParam('module');
    }

    /**
     * Fetches a Zend plugin resource object
     * TODO: In some cases it is not available yet because there is no FrontController yet!
     */
    public static function getResource($name)
    {
        if (php_sapi_name() == 'cli') {
            $app = Zend_Registry::get('CliApplication');
            $bootstrap = $app->getBootstrap();
        } else {
            $front = Zend_Controller_Front::getInstance();
            $bootstrap = $front->getParam('bootstrap');
        }

        return $bootstrap->getPluginResource($name);
    }

    /**
     * Resource namespace/prefix
     *
     * This function will be useful in object models, as some objects
     * will want to refer to other objects, and it will be good if the
     * library knows which class name to use for these objects.
     *
     * This function returns the namespace reserved for the given type.
     */
    public static function getResourcePrefix($type)
    {
        $autoloader = self::getApplicationAutoloader();

        if (!$autoloader->hasResourceType($type)) return '';

        $resourceTypes = $autoloader->getResourceTypes();
        return $resourceTypes[$type]['namespace'];
    }

    public static function getModulePath()
    {

        $front = Zend_Controller_Front::getInstance();
        $path = $front->getModuleDirectory(self::getModuleName());

        return $path;
    }

    public static function getLibraryPath()
    {
        return realpath(dirname(__FILE__));
    }

    public static function getDocumentRoot()
    {
        if (php_sapi_name() == 'cli') {
            $app = Zend_Registry::get('CliApplication');
            $path = APPLICATION_PATH . "/../" . $app->getModule();
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'];
        }

        return realpath($path);
    }

    public static function getFilePath($path)
    {
        return self::getDocumentRoot() . $path;
    }

    public static function isDevelopment()
    {
        $env = php_sapi_name() == 'cli' ? SCRIPT_ENV : APPLICATION_ENV;

        return strpos($env, 'development') !== false;
    }

    public static function isProduction()
    {
        $env = php_sapi_name() == 'cli' ? SCRIPT_ENV : APPLICATION_ENV;

        return strpos($env, 'production') !== false;
    }
}
