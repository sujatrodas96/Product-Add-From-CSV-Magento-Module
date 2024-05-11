<?php
namespace CsvCart\Custom\Block\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;
use CsvCart\Custom\Model\CsvCartFactory;

class CsvLinks extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;
    protected $csvCartFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CsvCartFactory $csvCartFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->csvCartFactory = $csvCartFactory;
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
     * Get CSV links by customer ID
     *
     * @return array
     */
    public function getCsvLinksByCustomerId()
    {
        $customerId = $this->customerSession->getCustomerId();
        $csvLinks = [];
        $csvFiles = $this->csvCartFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        
        foreach ($csvFiles as $csvFile) {
            $csvLinks[] = [
                'absolute' => $csvFile->getCsvPathAbsolute(), // Assuming you have a method to get absolute path
                'relative' => $csvFile->getCsvFilePath() // Assuming you have a method to get relative path
            ];
        }

        return $csvLinks;
    }

}
