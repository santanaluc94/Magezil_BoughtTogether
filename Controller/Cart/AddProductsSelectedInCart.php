<?php

namespace Magezil\BoughtTogether\Controller\Cart;

use Magento\Framework\Data\Form\FormKey;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class AddProductsSelectedInCart implements ActionInterface
{
    protected FormKey $formKey;
    protected Quote $cart;
    protected ProductRepositoryInterface $productRepository;
    protected UrlInterface $urlInterface;
    protected RequestInterface $request;
    protected ManagerInterface $messageManager;
    protected LoggerInterface $logger;

    public function __construct(
        FormKey $formKey,
        Quote $cart,
        ProductRepositoryInterface $productRepository,
        UrlInterface $urlInterface,
        RequestInterface $request,
        ManagerInterface $managerInterface,
        LoggerInterface $logger
    ) {
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->urlInterface = $urlInterface;
        $this->request = $request;
        $this->messageManager = $managerInterface;
        $this->logger = $logger;
    }

    public function execute(): ResultInterface
    {
        $productIds = $this->request->getParam('productIds');

        if ($this->request->getParam('qty') !== null) {
            $productQty = $this->request->getParam('qty');

            if ($productQty <= 0) {
                throw new LocalizedException(__('Invalid product qty.'));
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
                    'qty' => $productQty
                ];

                $product = $this->productRepository->getById($productId);

                $this->cart->addProduct($product, $params);
                $message = __(
                    'You added ' . $product->getName() . ' to your <a href="' . $url . '">shopping cart.</a>'
                );
                $this->messageManager->addSuccessMessage($message);
            }

            $this->cart->save();
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
            $this->messageManager->addExceptionMessage($exception, __('%1', $exception->getMessage()));
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->messageManager->addExceptionMessage($exception, __('error.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
