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
use CustomModules\BoughtTogether\Helper\Data as CustomHelper;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Block\Product\ListProduct;

/**
 * Class Items
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class Items extends ListProduct
{
    /**
     * Template
     *
     * @var string
     */
    protected $template = "CustomModules_BoughtTogether::product/list.phtml";

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
     * Product Collection Factory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Frequently Bought Together Config Helper
     *
     * @var CustomHelper
     */
    protected $helper;

    /**
     * Block Factory
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor Items
     *
     * @param Context  $context
     * @param PostHelper  $postDataHelper
     * @param Resolver  $layerResolver
     * @param CategoryRepositoryInterface  $categoryRepository
     * @param Data  $urlHelper
     * @param CollectionFactory  $orderCollectionFactory
     * @param Registry  $registry
     * @param CustomerSession  $customerSession
     * @param ProductFactory  $productFactory
     * @param CustomHelper  $helper
     * @param BlockFactory  $blockFactory
     * @param StoreManagerInterface  $storeManager
     * @param array  $data
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
        BlockFactory $blockFactory,
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
        $this->blockFactory = $blockFactory;
        $this->storeManager = $storeManager;
    }

    public function _prepareLayout()
    {
        if ($this->helper->hasBoughtTogetherTitle()) {
            $this->setData('block_title', $this->helper->getBoughtTogetherTitle());
        } else {
            $this->setData('block_title', __('Frequently Bought Together'));
        }

        return $this;
    }

    /**
     * Get frequently items bought together
     *
     * @return ProductFactory
     */
    public function _getProductCollection()
    {
        // get order collection
        $ordersCollection = $this->orderCollectionFactory->create();
        $orders = $ordersCollection->addAttributeToSelect('*');

        // get current product id
        $productId = (int) $this->getCurrentProduct()->getId();

        // get array with most items bought together
        $mostBoughtId = $this->getMostBoughtTogether($productId, $orders);

        $collection = $this->productFactory->create();
        $collection->addMinimalPrice()
            ->addIdFilter($mostBoughtId)
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

        // sort the most bought items
        arsort($orderItems);

        // get only id in array index
        $orderItems = array_keys($orderItems);

        return $orderItems;
    }

    /**
     * Has item in these orders
     *
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
     * Get Current Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getListingBlock()
    {
        return $this->blockFactory
            ->createBlock(ListProduct::class)
            ->setCollection($this->getLoadedProductCollection())
            ->setTemplate($this->template);
    }
}
