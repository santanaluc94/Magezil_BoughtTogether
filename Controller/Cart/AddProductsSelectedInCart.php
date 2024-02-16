<?php

namespace Magezil\BoughtTogether\Controller\Cart;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;

class AddProductsSelectedInCart implements ActionInterface
{
    public function __construct(
        protected \Magento\Framework\Data\Form\FormKey $formKey,
        protected \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        protected \Magento\Framework\UrlInterface $urlInterface,
        protected \Magento\Framework\App\RequestInterface $request,
        protected ResultFactory $resultFactory,
        protected \Magento\Checkout\Model\SessionFactory $checkoutSession,
        protected \Magento\Framework\Message\ManagerInterface $messageManager,
        protected \Psr\Log\LoggerInterface $logger
    ) {
    }

    public function execute(): ResultInterface
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productIds = $this->request->getParam('productIds');

        var_dump($this->request->getParams());
        die;
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

            /** @var Quote $currentCart */
            $currentCart = $this->checkoutSession->create()->getQuote();

            $url = $this->urlInterface->getUrl('checkout/cart/');

            foreach ($selectedItems as $productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product_id' => $productId,
                    'qty' => $productQty
                ];

                $product = $this->productRepository->getById($productId);

                $currentCart->addProduct($product, $productQty);
                $message = __(
                    'You added ' . $product->getName() . ' to your <a href="' . $url . '">shopping cart.</a>'
                );
                $this->messageManager->addSuccessMessage($message);
            }

            $this->cartRepository->save($currentCart);
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
