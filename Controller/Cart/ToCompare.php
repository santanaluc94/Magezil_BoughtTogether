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
use Magento\Framework\Controller\Result\Redirect;

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
    protected $productRepository;
    protected $result;
    protected $messageManager;
    protected $listCompare;
    protected $urlInterface;
    protected $logger;

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
     */
    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirect->create(
            ResultFactory::TYPE_REDIRECT
        );

        $productId = $this->getRequest()->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);

            // Add product to comparison list
            $this->listCompare->addProduct($productId);

            $this->messageManager->addSuccess(
                __(
                    'You added %1 to the <a href="%2">comparison list.</a>',
                    $product->getName(),
                    $this->urlInterface->getUrl('catalog/product_compare/')
                )
            );
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('%1', $e->getMessage()));
            $product = null;
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
