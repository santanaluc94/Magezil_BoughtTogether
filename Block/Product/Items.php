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

class Items extends ListProduct
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CustomHelper
     */
    protected $helper;

    /**
     * @var BlockFactory
     */

    private $blockFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * constructor items
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
     * @param BlockFactory $blockFactory
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
     * get frequently items bought together
     *
     * @param integer $id
     * @return ProductFactory
     */
    public function getBoughtTogetherCollection($id)
    {
        // get order collection
        $ordersCollection = $this->orderCollectionFactory->create();
        $orders = $ordersCollection->addAttributeToSelect('*');

        // $id = (int) $this->getCurrentProduct()->getId();

        // get array with most items bought together
        $mostBoughtId = $this->getMostBoughtTogether($id, $orders);

        $collection = $this->productFactory->create();
        $collection->addMinimalPrice()
            ->addIdFilter($mostBoughtId)
            ->addFinalPrice()
            ->addTaxPercents()
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
     * get frequently items bought together
     *
     * @param integer $id
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

                    // check array has item. If has item, sum with value with current value order. Else insert the current value in array
                    $orderItems[$item->getProductId()] = isset($orderItems[$item->getProductId()]) ? (int) $orderItems[$item->getProductId()] + (int) $item->getQtyOrdered() : (int) $item->getQtyOrdered();
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
     * has item in these orders
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
     * get Current Product
     */
    public function getCurrentProduct()
    {
        return (int) $this->registry->registry('current_product')->getId();
    }

    public function getListingBlock($id)
    {
        return $this->blockFactory
            ->createBlock(ListProduct::class)
            ->setCollection($this->getBoughtTogetherCollection($id))
            ->setTemplate('CustomModules_BoughtTogether::product/list.phtml');
    }
}
