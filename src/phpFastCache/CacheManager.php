<?php
/**
 *
 * This file is part of phpFastCache.
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the docs/CREDITS.txt file.
 *
 * @author Khoa Bui (khoaofgod)  <khoaofgod@gmail.com> http://www.phpfastcache.com
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 *
 */

namespace phpFastCache;

use phpFastCache\Core\phpFastCache;
use phpFastCache\Core\DriverAbstract;

/**
 * Class CacheManager
 * @package phpFastCache
 *
 * @method static DriverAbstract Apc() Apc($config = array()) Return a driver "apc" instance
 * @method static DriverAbstract Cookie() Cookie($config = array()) Return a driver "cookie" instance
 * @method static DriverAbstract Files() Files($config = array()) Return  a driver "files" instance
 * @method static DriverAbstract Memcache() Memcache($config = array()) Return a driver "memcache" instance
 * @method static DriverAbstract Memcached() Memcached($config = array()) Return a driver "memcached" instance
 * @method static DriverAbstract Predis() Predis($config = array()) Return a driver "predis" instance
 * @method static DriverAbstract Redis() Redis($config = array()) Return a driver "redis" instance
 * @method static DriverAbstract Sqlite() Sqlite($config = array()) Return a driver "sqlite" instance
 * @method static DriverAbstract Ssdb() Ssdb($config = array()) Return a driver "ssdb" instance
 * @method static DriverAbstract Wincache() Wincache($config = array()) Return a driver "wincache" instance
 * @method static DriverAbstract Xcache() Xcache($config = array()) Return a driver "xcache" instance
 *
 */
class CacheManager
{
    public static $instances = array();
    public static $memory = array();

    /**
     * @param string $storage
     * @param array $config
     * @return DriverAbstract
     */
    public static function getInstance($storage = 'auto', $config = array())
    {
        $storage = strtolower($storage);
        if (empty($config)) {
            $config = phpFastCache::$config;
        }
        if(!isset($config['cache_method'])) {
            $config['cache_method'] = phpFastCache::$config['cache_method'];
        }
        if (isset(phpFastCache::$config[ 'overwrite' ]) && !in_array(phpFastCache::$config[ 'overwrite' ], array('auto', ''), true)) {
            phpFastCache::$config[ 'storage' ] = phpFastCache::$config[ 'overwrite' ];
            $storage = phpFastCache::$config[ 'overwrite' ];
        } else if (isset(phpFastCache::$config[ 'storage' ]) && !in_array(phpFastCache::$config[ 'storage' ], array('auto', ''), true)) {
            $storage = phpFastCache::$config[ 'storage' ];
        } else if (in_array($storage, array('auto', ''), true)) {
            $storage = phpFastCache::getAutoClass($config);
        }

      //  echo $storage."<br>";
        $instance = md5(serialize($config) . $storage);
        if (!isset(self::$instances[ $instance ]) || is_null(self::$instances[ $instance ])) {
            $class = '\phpFastCache\Drivers\\' . $storage;
            $config['storage'] = $storage;
            $config['instance'] = $instance;
            $config['class'] = $class;
            if(!isset(self::$memory[$instance])) {
                self::$memory[$instance] = array();
            }
            self::$instances[ $instance ] = new $class($config);
        }

        return self::$instances[ $instance ];
    }

    /*
     * Setup Method
     * @param string $string | tradtiional(normal), memory (fast), phpfastcache (fastest)
     */
    public static function CachingMethod($string = "phpFastCache") {
        $string = strtolower($string);
        if(in_array($string,array("normal","traditional"))) {
            phpFastCache::$config['cache_method'] = 1;
        }else if(in_array($string,array("fastest","phpfastcache"))) {
            phpFastCache::$config['cache_method'] = 2;
        }else if(in_array($string,array("fast","memory"))) {
            phpFastCache::$config['cache_method'] = 3;
        }
    }

    /**
     * CacheManager::Files();
     * CacheManager::Memcached();
     * CacheManager::get($keyword);
     * CacheManager::set(), touch, other @method supported
     */
    public static function __callStatic($name, $arguments)
    {
        $driver = strtolower($name);
        if(!isset(self::$instances['loaded'][$driver])) {
            // check only first time
            if(file_exists(__DIR__."/Drivers/".$driver.".php")) {
                self::$instances['loaded'][$driver] = true;
            }
        }
        if(isset(self::$instances['loaded'][$driver])) {
            return self::getInstance($name, (isset($arguments[ 0 ]) ? $arguments[ 0 ] : array()));
        } else {
            return call_user_func_array(array(self::getInstance(),$name),$arguments);
        }

    }

    /**
     * Shortcut to phpFastCache::setup()
     */
    public static function setup($name, $value = '')
    {
        phpFastCache::setup($name, $value);
    }

}
