<?php

namespace CustomModules\BoughtTogether\Controller\Cart;

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

/**
 * Class Addto
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class Addto extends Action
{
    /**
     * Form key
     *
     * @var FormKey
     */
    protected $formKey;

    /**
     * Cart
     *
     * @var Cart
     */
    protected $cart;

    /**
     * Product Factory
     *
     * @var ProductFactory
     */
    protected $product;

    /**
     * Message Manager
     *
     * @var ManagerInterface
     */
    protected $messageManager;

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
     * Addto constructor.
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param Cart $cart
     * @param ProductFactory $product
     * @param ManagerInterface $managerInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        Cart $cart,
        ProductFactory $product,
        ManagerInterface $managerInterface,
        UrlInterface $urlInterface,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->messageManager = $managerInterface;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
    }

    /**
     * Add product to cart
     *
     * @return ResultFactory
     */
    public function execute()
    {
        // Redirect to back
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

            foreach ($selectedItems as $key => $productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product_id' => $productId,
                    'qty'   => $productQty
                ];

                $_product = $this->product->create()->load($productId);

                $this->cart->addProduct($_product, $params);
                $this->messageManager->addSuccess(
                    'You added %1 to your <a href="%2">shopping cart.</a>',
                    $_product->getName(),
                    $this->urlInterface->getUrl('checkout/cart/')
                );
            }

            $this->cart->save();
            $status = 1;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addException($e, __('%1', $e->getMessage()));
            $status = 0;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addException($e, __('error.'));
            $status = 0;
        }


        return $resultRedirect;
    }
}
