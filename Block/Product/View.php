<?php

namespace Magezil\BoughtTogether\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data;
use Magezil\BoughtTogether\Model\Config\Source\Options;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;

class View extends ListProduct
{
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        protected \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Customer\Model\SessionFactory $customerSession,
        protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        protected Options $boughtTogetherConfig,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        $data = []
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    public function getCurrentProduct(): Product
    {
        return $this->registry->registry('current_product');
    }

    protected function _getProductCollection(): ?ProductCollection
    {
        $ordersCollection = $this->orderCollectionFactory->create()->addAttributeToSelect('*');

        $productId = (int) $this->getCurrentProduct()->getId();

        $mostBought = $this->getMostBoughtTogether($productId, $ordersCollection);

        $collection = $this->productCollectionFactory->create();
        $collection->addMinimalPrice()
            ->addIdFilter($mostBought)
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToFilter('status', '1')
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->storeManager->getStore()->getId());

        if ($this->boughtTogetherConfig->hasBoughtTogetherProductsQty()) {
            $collection->setPageSize((int) $this->boughtTogetherConfig->getBoughtTogetherProductsQty());
        }

        return $collection;
    }

    private function getMostBoughtTogether(int $id, OrderCollection $orders): array
    {
        $orderItems = [];
        foreach ($orders as $order) {
            if ($this->hasItemInOrder($id, $order)) {
                foreach ($order->getAllItems() as $item) {
                    if ($id === (int) $item->getProductId()) {
                        continue;
                    }

                    // Check array has item. If has item, sum with value with current value order. Else insert the current value in array
                    $orderItems[$item->getProductId()] = isset($orderItems[$item->getProductId()]) ?
                        (int) $orderItems[$item->getProductId()] + (int) $item->getQtyOrdered() :
                        (int) $item->getQtyOrdered();
                }
            }
        }

        arsort($orderItems);
        return array_keys($orderItems);
    }

    private function hasItemInOrder(int $id, Order $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            if ($id === (int) $item->getProductId()) {
                return true;
            }
        }

        return false;
    }

    public function isShowBlock(): bool
    {
        if (!$this->boughtTogetherConfig->isEnabled()) {
            return false;
        }

        if ($this->boughtTogetherConfig->isBoughtTogetherLoggedIn()) {
            $customerSession = $this->customerSession->create();
            return $customerSession->isLoggedIn();
        }

        if ($this->getLoadedProductCollection()->getSize() === 0) {
            return false;
        }

        return true;
    }

    public function getBoughtTogetherConfig(): Options
    {
        return $this->boughtTogetherConfig;
    }

    public function getBoughtTogetherTitle(): string
    {
        return $this->boughtTogetherConfig->hasBoughtTogetherTitle() ?
            $this->boughtTogetherConfig->getBoughtTogetherTitle() :
            __('Frequently Bought Together:');
    }

    public function getAddProductsSelectedInCartUrl(): string
    {
        return $this->getUrl('boughtTogether/cart/addProductsSelectedInCart');
    }
}
