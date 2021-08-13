<?php

namespace Magezil\BoughtTogether\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Options
{
    const MODULE_ENABLE = 'magezil_bought_together/general/enable';
    const BOUGHT_TOGETHER_LOGGED_IN = 'magezil_bought_together/general/user_logged';
    const BOUGHT_TOGETHER_TITLE = 'magezil_bought_together/general/title';
    const BOUGHT_TOGETHER_QUANTITY = 'magezil_bought_together/general/products_qty';
    const SHOW_WISHLIST = 'magezil_bought_together/cards_configuration/show_wishlist';
    const SHOW_COMPARE = 'magezil_bought_together/cards_configuration/show_compare';
    const SHOW_QTY_PRODUCTS = 'magezil_bought_together/cards_configuration/qty_products';

    protected ScopeConfigInterface $scopeConfig;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isBoughtTogetherLoggedIn(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BOUGHT_TOGETHER_LOGGED_IN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function hasBoughtTogetherTitle(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BOUGHT_TOGETHER_TITLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getBoughtTogetherTitle(): string
    {
        return $this->scopeConfig->getValue(
            self::BOUGHT_TOGETHER_TITLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function hasBoughtTogetherProductsQty(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::BOUGHT_TOGETHER_QUANTITY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getBoughtTogetherProductsQty(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::BOUGHT_TOGETHER_QUANTITY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isShowWishlist(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::SHOW_WISHLIST,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isShowCompare(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::SHOW_COMPARE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isShowQtyProducts(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::SHOW_QTY_PRODUCTS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
