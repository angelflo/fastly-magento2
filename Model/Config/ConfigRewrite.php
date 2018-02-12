<?php

namespace Fastly\Cdn\Model\Config;

use Fastly\Cdn\Model\Api;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Used for sending purge after disabling Fastly as caching service
 *
 * @author Inchoo
 */
class ConfigRewrite
{
    private $purge = false;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig = null;

    /**
     * @var Api
     */
    private $api;

    /**
     * ConfigRewrite constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Api $api
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Api $api
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->api = $api;
    }

    /**
     * Trigger purge if set
     * @param \Magento\Config\Model\Config $subject
     */
    public function afterSave(\Magento\Config\Model\Config $subject)
    {
        if ($this->purge) {
            $this->api->cleanBySurrogateKey(['text']);
        }
    }

    /**
     * Set flag for purging if Fastly is switched off
     * @param \Magento\Config\Model\Config $subject
     */
    public function beforeSave(\Magento\Config\Model\Config $subject)
    {
        $data = $subject->getData();
        if (!empty($data['groups']['full_page_cache']['fields']['caching_application']['value'])) {
            $currentCacheConfig = $data['groups']['full_page_cache']['fields']['caching_application']['value'];
            $oldCacheConfig = $this->scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TYPE);

            if ($oldCacheConfig == \Fastly\Cdn\Model\Config::FASTLY && $currentCacheConfig != $oldCacheConfig) {
                $this->purge = true;
            }
        }
    }
}
