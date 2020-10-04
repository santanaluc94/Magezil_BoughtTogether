<?php

namespace Magezil\BoughtTogether\Controller\Cart;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class AddProductsSelectedInCart
 *
 * @category Magento
 * @package  Magezil_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class AddProductsSelectedInCart extends Action
{
    protected $formKey;
    protected $cart;
    protected $product;
    protected $messageManager;
    protected $urlInterface;
    protected $logger;

    public function __construct(
        Context $context,
        FormKey $formKey,
        Cart $cart,
        ProductFactory $productFactory,
        ManagerInterface $managerInterface,
        UrlInterface $urlInterface,
        LoggerInterface $logger
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->messageManager = $managerInterface;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Add product to cart
     */
    public function execute(): Redirect
    {
        // Redirect to latest url
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        $productIds = $this->getRequest()->getParam('productIds');

        if ($this->getRequest()->getParam('qty') !== null) {
            $productQty = $this->getRequest()->getParam('qty');

            if ($productQty <= 0) {
                throw new LocalizedException(__('Invalid product qty.'));
                return $resultRedirect;
            }
        } else {
            $productQty = 1;
        }

        try {
            $selectedItems = explode(",", $productIds);

            $url = $this->urlInterface->getUrl('checkout/cart/');

            foreach ($selectedItems as $productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product_id' => $productId,
                    'qty'   => $productQty
                ];

                $productFactory = $this->productFactory->create();
                $product = $productFactory->load($productId);

                $this->cart->addProduct($product, $params);
                $message = __('You added ' . $product->getName() . ' to your <a href="' . $url . '">shopping cart.</a>');
                $this->messageManager->addSuccess($message);
            }

            $this->cart->save();
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
            $this->messageManager->addExceptionMessage($exception, __('%1', $exception->getMessage()));
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->messageManager->addExceptionMessage($exception, __('error.'));
        }

        return $resultRedirect;
    }
}
