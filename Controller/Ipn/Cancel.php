<?php
/**
 * @copyright  PayTabs
 * @customizedBy  Ambient
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ambient\Paytabs\Controller\Ipn;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Cancel extends AppAction implements CsrfAwareActionInterface
{
/**
 * @var \Magento\Sales\Model\Order
 */
    protected $_order;

/**
 * @var \Magento\Sales\Model\OrderFactory
 */
    protected $_orderFactory;

/**
 * @var \Psr\Log\LoggerInterface
 */
    protected $_logger;

    protected $request;

    protected $cart;

    protected $checkoutSession;

/**
 * @param \Magento\Framework\App\Action\Context $context
 * @param \Magento\Sales\Model\OrderFactory $orderFactory
 * @param \Citrus\Icp\Model\PaymentMethod $paymentMethod
 * @param Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
 * @param  \Psr\Log\LoggerInterface $logger
 */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->request = $request;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

/**
 *      * @inheritDoc
 *           */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     *      * @inheritDoc
     *           */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

/**
 * Handle POST request to Citrus callback endpoint.
 */
    public function execute()
    {
        try {
            // Cryptographically verify authenticity of callback
            if ($this->getRequest()->isPost()) {
                $this->cancelAction();
            } else {
                $this->_logger->addError("Paytabs: no post back data received in callback");
                return $this->_failure();
            }
        } catch (Exception $e) {
            $this->_logger->addError("Paytabs: error processing callback");
            $this->_logger->addError($e->getMessage());
            return $this->_failure();
        }

        $this->_logger->addInfo("Paytabs Transaction END from Paytabs");
    }

    public function getOrder()
    {
        if ($this->checkoutSession->getLastRealOrderId()) {
            $order = $this->_orderFactory->create()->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
            return $order;
        }
    }

    protected function cancelAction()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $this->getOrder();
        if (!$order) {
            $this->_redirect('checkout');
            return;
        }
        $quote = $objectManager->create('Magento\Quote\Model\QuoteFactory')->create()->load($order->getQuoteId());

        $quote->setReservedOrderId(null);
        $quote->setIsActive(true);
        $quote->removePayment();
        $quote->save();

        //replace the quote to the checkout session (I guess this is the better way)
        $this->checkoutSession->replaceQuote($quote);
        //OR add quote to cart
        $this->cart->setQuote($quote);
        //if your last order is still in the session (getLastRealOrder() returns order data) you can achieve what you need with this one line without loading the order:
        $this->checkoutSession->restoreQuote();

        //Redirect to cart page
        $this->_redirect('checkout');
    }
}
