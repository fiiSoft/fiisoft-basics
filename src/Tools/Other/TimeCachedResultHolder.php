<?php

namespace FiiSoft\Tools\Other;

trait TimeCachedResultHolder
{
    /** @var integer time (in seconds) of keeping cached entities before reload them from repositories */
    protected $cacheRefreshTime = 60;
    
    /** @var array keeps times of loaded entities, to clear them after few seconds */
    private $cacheLoadTime = [];
    
    /**
     * @param string|int $key
     * @param int|null $refreshTime
     * @return bool
     */
    final protected function isTimeToRefresh($key, $refreshTime = null)
    {
        if (isset($this->cacheLoadTime[$key])) {
            return time() - $this->cacheLoadTime[$key] > ($refreshTime ?: $this->cacheRefreshTime);
        }
        
        return false;
    }
    
    /**
     * @param string $key
     */
    final protected function updateCacheTime($key)
    {
        $this->cacheLoadTime[$key] = time();
    }
}