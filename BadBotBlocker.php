<?php

/**
 * class BadBotBlocker
 * 
 * @package  BadBotBlocker
 */
class BadBotBlocker {

    var $error = false;
    var $memcacheHost = false;
    var $memcachePort = false;
    var $requestsLimit = 50;
    var $requestsPeriod = 1200; // In seconds where 60 * 20 minutes
    var $blockByAgent = false;
    var $debugContent = '';
    var $debug = false;
    var $showCaptcha = false;
    var $cacheKeyPrefix = 'BBB||';

    function __construct($debug = false) {
        if ($this->checkConfig() === false) {
            $this->error = true;
            return false;
        }
        $this->memcacheHost = MEMCACHE_HOST;
        $this->memcachePort = MEMCACHE_PORT;
        $this->memcacheConnect();
    }

    private function checkConfig() {
        if (!constant('MEMCACHE_HOST') || !constant('MEMCACHE_PORT')) {
            $this->debugContent .= 'Missing config constants.' . PHP_EOL;
            return false;
        }
        return true;
    }

    public function checkAccess() {
        if ($this->error == true) {
            return 'error';
        }
        $requestsCount = (int) $this->getData($this->cacheKeyPrefix . $_SERVER['REMOTE_ADDR']);
        if ($requestsCount >= $this->requestsLimit) {
            $this->showCaptcha = true;
            return 'captcha';
        }
        $requestsCount++;
        $this->setData($this->cacheKeyPrefix . $_SERVER['REMOTE_ADDR'], $requestsCount, $this->requestsPeriod);
        return 'ok|' . $requestsCount;
    }

    public function enableAccess() {
        if ($this->error == true) {
            return 'error';
        }
        $this->deleteData($this->cacheKeyPrefix . $_SERVER['REMOTE_ADDR']);
        return 'ok';
    }

    private function memcacheConnect() {
        if (!class_exists('Memcache') || !function_exists('memcache_connect')) {
            $this->debugContent .= 'Memcache Library not loaded.' . PHP_EOL;
            $this->obj_cache = null;
            $this->enabled_memcache = false;
            $this->error = true;
            return false;
        }
        $this->obj_cache = new Memcache();
        $this->enabled_memcache = true;
        if (!$this->obj_cache->pconnect($this->memcacheHost, $this->memcachePort)) {
            $this->obj_cache = null;
            $this->enabled_memcache = false;
            $this->error = true;
            $this->debugContent .= 'Can\'t connect to memcache server.' . PHP_EOL;
        }
    }

    /*  get data from cache server
     * @paramater string $sKey
     * @return string value
     * 
     */

    private function getData($sKey) {
        $vData = $this->obj_cache->get(md5($sKey));
        if (strlen($vData) > 0) {
            $vData = unserialize($vData);
        }
        return false === $vData ? null : $vData;
    }

    /*  save data to cache server
     * @paramater string $sKey
     * @paramater string $vData
     * @paramater int $cache_time // cache time in seconds
     * @return string value
     * 
     */

    private function setData($sKey, $vData, $cache_time = 600) {
        return $this->obj_cache->set(md5($sKey), serialize($vData), MEMCACHE_COMPRESSED, $cache_time);
    }

    /* @paramater string $sKey
     * @return bool value
     * 
     */

    private function deleteData($sKey) {
        return $this->obj_cache->delete(md5($sKey));
    }

}
