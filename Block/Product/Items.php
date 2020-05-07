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
use Magento\Catalog\Model\ProductFactory;
use CustomModules\BoughtTogether\Helper\Data as CustomHelper;

class Items extends \Magento\Catalog\Block\Product\ListProduct
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
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $urlHelper
     * @param CollectionFactory $orderCollectionFactory
     * @param Registry $registry
     * @param CustomerSession $customerSession
     * @param ProductFactory $productFactory
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
    }

    /**
     * get Current Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * get frequently items bought together
     *
     * @param integer $id
     */
    public function getFrequentlyBoughtTogether(int $id)
    {
        // get order collection
        $ordersCollection = $this->orderCollectionFactory->create();
        $orders = $ordersCollection->addAttributeToSelect('*');

        // get array with most items bought together
        $mostBought = $this->getMostBoughtTogether($id, $orders);

        $orderItems = [];
        foreach ($mostBought as $itemId => $qty) {
            // insert product in array
            $orderItems[] = $this->getItemBoughtTogether($itemId);
        }

        // return array with products sorted
        return $orderItems;
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

                    $orderItems[] = $item->getProductId();
                }
            }
        }
        // count which are the most bought items grouped by id
        $mostBought = array_count_values($orderItems);

        // sord the most bought items
        arsort($mostBought);

        // set admin qty to show in front
        if ($this->helper->hasBoughtTogetherProductsQty()) {
            $qty = $this->helper->getBoughtTogetherProductsQty();
            $mostBought = array_slice($mostBought, 0, $qty, true);
        }

        return $mostBought;
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
     * get product repository by product id
     *
     * @param int $productId
     * @return ProductFactory
     */
    private function getItemBoughtTogether(int $productId)
    {
        $productFactory = $this->productFactory->create();
        $product = $productFactory->load($productId);

        return $product;
    }

}
