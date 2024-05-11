<?php
namespace CsvCart\Custom\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use CsvCart\Custom\Model\CsvCartFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class AddToCart extends Action
{
    protected $cart;
    protected $customerSession;
    protected $csvCartFactory;
    protected $filesystem;

    public function __construct(
        Context $context,
        Cart $cart,
        CustomerSession $customerSession,
        CsvCartFactory $csvCartFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->csvCartFactory = $csvCartFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to add products to cart.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        try {
            $csvFilePath = $this->getRequest()->getParam('csv_file_path');
            $csvFilePath = $this->processCsvFilePath($csvFilePath);

            if ($csvFilePath) {
                // Read the CSV file
                $csvData = $this->readCsvFile($csvFilePath);

                // Add products to cart based on CSV data
                $this->addProductsToCart($csvData);

                $this->messageManager->addSuccessMessage(__('Products added to cart successfully.'));
            } else {
                $this->messageManager->addErrorMessage(__('Invalid CSV file path.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error occurred while adding products to cart.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }

    protected function processCsvFilePath($relativePath)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $absolutePath = $mediaDirectory->getAbsolutePath($relativePath);
        if ($mediaDirectory->isFile($absolutePath)) {
            return $absolutePath;
        }
        return null;
    }

    protected function readCsvFile($csvFilePath)
    {
        $csvData = [];
        if (($handle = fopen($csvFilePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $csvData[] = $data;
            }
            fclose($handle);
        } else {
            throw new LocalizedException(__('Unable to open CSV file: %1', $csvFilePath));
        }
        return $csvData;
    }

    protected function addProductsToCart($csvData)
    {
        foreach ($csvData as $rowData) {
            // Assuming the CSV format is like: product_sku,quantity
            $productSku = $rowData[0];
            $quantity = isset($rowData[1]) ? (int)$rowData[1] : 1;

            // Load product by SKU
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $productSku);
            if ($product && $product->getId()) {
                $params = array(
                    'product' => $product->getId(),
                    'qty' => $quantity
                );
                $this->cart->addProduct($product, $params);
            } else {
                throw new LocalizedException(__('Product with SKU %1 not found.', $productSku));
            }
        }
        $this->cart->save();
    }
}
