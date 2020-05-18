<?php

namespace CustomModules\BoughtTogether\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductFactory;
use Magento\Store\Model\StoreManagerInterface;
use CustomModules\BoughtTogether\Helper\Data as CustomHelper;
use Magento\Framework\App\ObjectManager;

/**
 * Class View
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class View extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Order Collection Factory
     *
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Customer Session
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Product Factory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param CollectionFactory $orderCollectionFactory
     * @param Registry $registry
     * @param CustomerSession $customerSession
     * @param ProductFactory $productFactory
     * @param CustomHelper $helper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        CollectionFactory $orderCollectionFactory,
        Registry $registry,
        CustomerSession $customerSession,
        ProductFactory $productFactory,
        CustomHelper $helper,
        StoreManagerInterface $storeManager,
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
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Current Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get frequently items bought together
     *
     * @return ProductFactory
     */
    public function getFrequentlyBoughtTogether()
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
     *
     * @param integer $id
     * @param $orders
     * @return array
     */
    private function getMostBoughtTogether(int $id, $orders): array
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
     *
     * @param integer $id
     * @param $order
     * @return boolean
     */
    private function hasItemInOrder(int $id, $order): bool
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
     *
     * @return boolean
     */
    public function isShowBlock(): bool
    {
        // Check module is enable in admin
        if (!$this->helper->isEnabled()) {
            return false;
        }

        // Check config is enable and if is true, show block only user is logged in
        if ($this->helper->isBoughtTogetherLoggedIn()) {
            // Customer session is not working by dependency injection
            $this->customerSession = ObjectManager::getInstance()
                ->create(CustomerSession::class);

            return $this->customerSession->isLoggedIn();
        }

        // Show block if product were bought together
        if (count($this->getFrequentlyBoughtTogether($this->getCurrentProduct()->getId())) === 0) {
            return false;
        }

        return true;
    }

    public function getProductByItem($productId)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->getById($productId);
    }
}
