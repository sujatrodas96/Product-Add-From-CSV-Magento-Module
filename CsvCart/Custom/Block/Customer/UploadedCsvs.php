<?php
namespace CsvCart\Custom\Block\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;

class UploadedCsvs extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get the form action URL
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('custom/index/upload');

    }
}
