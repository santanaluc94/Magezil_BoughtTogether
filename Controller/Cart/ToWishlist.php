<?php

namespace CustomModules\BoughtTogether\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class ToWishlist
 *
 * @category Magento
 * @package  CustomModules_BoughtTogether
 * @author   Lucas Teixeira dos Santos Santana <santanaluc94@gmail.com>
 * @license  NO-LICENSE #
 * @link     http://github.com/santanaluc94
 */
class ToWishlist extends Action
{
    /**
     * Whishlist Factory
     *
     * @var WishlistFactory
     */
    protected $wishlistRepository;

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
     * Logger Interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Add To Wishlist constructor.
     *
     * @param Context $context
     * @param WishlistFactory $wishlistRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ResultFactory $result
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        WishlistFactory $wishlistRepository,
        ProductRepositoryInterface $productRepository,
        ResultFactory $result,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
        $this->resultRedirect = $result;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Add product to wishlist
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

        $session = ObjectManager::getInstance()
            ->create(Session::class);

        if ($session->isLoggedIn()) {
            $customerId = $session->getCustomer()->getId();
            $wishlist = $this->wishlistRepository->create()
                ->loadByCustomerId($customerId, true);

            $wishlist->addNewItem($product);
            $wishlist->save();

            $resultRedirect->setPath('wishlist');
            return $resultRedirect;
        }

        $this->messageManager->addError(
            __('You must login or register to add items to your wishlist.')
        );

        $resultRedirect->setPath('customer/account/login');
        return $resultRedirect;
    }
}
