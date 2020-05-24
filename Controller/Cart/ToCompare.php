<?php

namespace CustomModules\BoughtTogether\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class ToCompare
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class ToCompare extends Action
{
    /**
     * Product Repository Interface
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Result Factory
     *
     * @var ResultFactory
     */
    protected $result;

    /**
     * Manager Interface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * List Compare
     *
     * @var ListCompare
     */
    protected $listCompare;

    /**
     * Url Interface
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * Logger Interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Add To Compare constructor.
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ResultFactory $result
     * @param ManagerInterface $messageManager
     * @param ListCompare $listCompare
     * @param UrlInterface $urlInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ResultFactory $result,
        ManagerInterface $messageManager,
        ListCompare $listCompare,
        UrlInterface $urlInterface,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->resultRedirect = $result;
        $this->messageManager = $messageManager;
        $this->listCompare = $listCompare;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
    }

    /**
     * Add product to add to compare
     *
     * @return ResultFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirect->create(
            ResultFactory::TYPE_REDIRECT
        );

        $productId = $this->getRequest()->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addException($e, __('%1', $e->getMessage()));
            $product = null;
        }

        // Add product to comparison list
        $this->listCompare->addProduct($productId);

        $this->messageManager->addSuccess(
            __(
                'You added %1 to the <a href="%2">comparison list.</a>',
                $product->getName(),
                $this->urlInterface->getUrl('catalog/product_compare/')
            )
        );

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
