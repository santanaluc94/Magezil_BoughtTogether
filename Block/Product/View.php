<?php

namespace CustomModules\BoughtTogether\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductFactory;
use CustomModules\BoughtTogether\Helper\Data as CustomHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;

/**
 * Class View
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class View extends ListProduct
{
    protected $orderCollectionFactory;
    protected $registry;
    protected $customerSession;
    protected $productFactory;
    protected $storeManager;
    protected $productRepository;

    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        OrderCollectionFactory $orderCollectionFactory,
        Registry $registry,
        CustomerSession $customerSession,
        ProductFactory $productFactory,
        CustomHelper $helper,
        StoreManagerInterface $storeManager,
        $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * Get Current Product
     */
    public function getCurrentProduct(): Product
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get frequently items bought together
     */
    public function getFrequentlyBoughtTogether(): ProductCollection
    {
        // get order collection
        $ordersCollection = $this->orderCollectionFactory->create();
        $orders = $ordersCollection->addAttributeToSelect('*');

        // get current product id
        $productId = (int) $this->getCurrentProduct()->getId();

        // get array with most items bought together
        $mostBought = $this->getMostBoughtTogether($productId, $orders);

        $collection = $this->productFactory->create();
        $collection->addMinimalPrice()
            ->addIdFilter($mostBought)
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToFilter('status', '1')
            ->addAttributeToSelect('*')
            ->addStoreFilter($this->storeManager->getStore()->getId());

        // set admin qty to show in front
        if ($this->helper->hasBoughtTogetherProductsQty()) {
            $collection->setPageSize((int) $this->helper->getBoughtTogetherProductsQty());
        }

        // return array with products sorted
        return $collection;
    }

    /**
     * Get frequently items bought together
     */
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

        // sord the most bought items
        arsort($orderItems);

        // get only id in array index
        $orderItems = array_keys($orderItems);

        return $orderItems;
    }

    /**
     * Has item in these orders.
     */
    private function hasItemInOrder(int $id, Order $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            if ($id === (int) $item->getProductId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is show block in product page
     */
    public function isShowBlock(): bool
    {
        // Check module is enable in admin
        if (!$this->helper->isEnabled()) {
            return false;
        }

        // Check config is enable and if is true, show block only user is logged in
        if ($this->helper->isBoughtTogetherLoggedIn()) {
            return $this->customerSession->isLoggedIn();
        }

        // Show block if product were bought together
        if (count($this->getFrequentlyBoughtTogether($this->getCurrentProduct()->getId())) === 0) {
            return false;
        }

        return true;
    }
}
