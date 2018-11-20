<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\Sitemap\Helper;

/**
 * Class Data
 * @package Conns\Sitemap\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * restricted Urls
     */
    const CONNS_SITEMAP_RESTRICTED_PAGES = 'connssitemap/general/restricted_pages';

    /**
     * @return array
     */
    public function getSitemapRestrictedPages()
    {
        $data = $this->scopeConfig->getValue(
            self::CONNS_SITEMAP_RESTRICTED_PAGES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $data = explode(',',$data);
        return $data;
    }

}