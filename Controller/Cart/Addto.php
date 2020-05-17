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
     * Addto constructor.
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param Cart $cart
     * @param ProductFactory $product
     * @param ManagerInterface $managerInterface
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        Cart $cart,
        ProductFactory $product,
        ManagerInterface $managerInterface,
        UrlInterface $urlInterface
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->messageManager = $managerInterface;
        $this->urlInterface = $urlInterface;
    }

    public function execute()
    {
        $productIds = $this->getRequest()->getParam('productIds');
        $selectedItems = explode(",", $productIds);

        try {
            foreach ($selectedItems as $key => $productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product_id' => $productId, //product Id
                    'qty'   => 1 //quantity of product
                ];
                $_product = $this->product->create()->load($productId);

                $this->cart->addProduct($_product, $params);
                $this->messageManager->addSuccess('You added ' . $_product->getName() . ' to your <a href="' . $this->urlInterface->getUrl('checkout/cart/') . '">shopping cart.</a>');
            }
            $this->cart->save();
            $status = 1;

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException($e, __('%1', $e->getMessage()));
            $status = 0;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('error.'));
            $status = 0;
        }

        // Redirect to back
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
