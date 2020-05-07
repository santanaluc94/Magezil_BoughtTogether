<?php

namespace CustomModules\BoughtTogether\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = 'custom_bought_together/general/enable';
    const BOUGHT_TOGETHER_TITLE = 'custom_bought_together/general/title';
    const BOUGHT_TOGETHER_QUANTITY = 'custom_bought_together/general/products_qty';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Check module is enable
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::MODULE_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Has Frequently Bought Together Title
     *
     * @return boolean
     */
    public function hasBoughtTogetherTitle(): bool
    {
        return $this->scopeConfig->isSetFlag(self::BOUGHT_TOGETHER_TITLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get Frequently Bought Together Title
     *
     * @return string
     */
    public function getBoughtTogetherTitle(): string
    {
        return $this->scopeConfig->getValue(self::BOUGHT_TOGETHER_TITLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Has quantity Products of Frequently Bought Together
     *
     * @return boolean
     */
    public function hasBoughtTogetherProductsQty(): bool
    {
        return $this->scopeConfig->isSetFlag(self::BOUGHT_TOGETHER_QUANTITY, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get quantity Products of Frequently Bought Together
     *
     * @return string
     */
    public function getBoughtTogetherProductsQty(): string
    {
        return $this->scopeConfig->getValue(self::BOUGHT_TOGETHER_QUANTITY, ScopeInterface::SCOPE_WEBSITE);
    }
}
