<?php
namespace CsvCart\Custom\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use CsvCart\Custom\Model\CsvCartFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action
{
    protected $cart;
    protected $customerSession;
    protected $filesystem;
    protected $csvCartFactory;
    protected $uploaderFactory;
    protected $storeManager;
    protected $mediaDirectory;

    public function __construct(
        Context $context,
        Cart $cart,
        CustomerSession $customerSession,
        Filesystem $filesystem,
        CsvCartFactory $csvCartFactory,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->filesystem = $filesystem;
        $this->csvCartFactory = $csvCartFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('You must be logged in to upload CSV.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $customerId = $this->customerSession->getCustomer()->getId();

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'csv_file']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            
            $destinationPath = 'csv_cart/' . $customerId;
            
            // Save file with original name
            $result = $uploader->save($this->mediaDirectory->getAbsolutePath($destinationPath));
    
            // Retrieve the file name
            $fileName = $result['file'];
    
            // Build the relative file path
            $relativeCsvFilePath = $destinationPath . $fileName;
    
            // Build the absolute file path
            $absoluteCsvFilePath = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $relativeCsvFilePath;
    
            // Save paths and customer ID in the database
            $csvCart = $this->csvCartFactory->create();
            $csvCart->setData([
                'customer_id' => $customerId,
                'csv_file_path' => $relativeCsvFilePath, // Store relative path
                'csv_path_absolute' => $absoluteCsvFilePath, // Store absolute path
            ]);
            $csvCart->save();
    
            $this->messageManager->addSuccessMessage(__('CSV uploaded successfully.'));
    
            // Read the uploaded CSV file
            $csvData = [];
            if (($handle = fopen($this->mediaDirectory->getAbsolutePath($relativeCsvFilePath), "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $csvData[] = $data;
                }
                fclose($handle);
            }
    
            // Add products to cart
            foreach ($csvData as $rowData) {
                // Assuming the CSV structure is: SKU, Quantity
                $sku = $rowData[0]; // SKU of the product
                $qty = $rowData[1]; // Quantity
    
                // Load the product by SKU
                $product = $this->_objectManager->create(\Magento\Catalog\Model\Product::class)->loadByAttribute('sku', $sku);
    
                if ($product) {
                    // Add the product to the cart
                    $this->cart->addProduct($product, ['qty' => $qty]);
                } else {
                    throw new LocalizedException(__('Product with SKU %1 not found.', $sku));
                }
            }
    
            // Save the cart
            $this->cart->save();
    
            $this->messageManager->addSuccessMessage(__('Products added to cart successfully.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error occurred while uploading CSV file.'));
        }
    
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }    
}
