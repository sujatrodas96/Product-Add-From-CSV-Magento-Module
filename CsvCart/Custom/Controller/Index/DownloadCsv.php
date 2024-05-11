<?php
namespace CsvCart\Custom\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;

class DownloadCsv extends Action
{
    protected $fileFactory;
    protected $filesystem;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $csvFilePath = $this->getRequest()->getParam('csv_file_path_download');

        try {
            // Validate the CSV file path if needed

            // Set the appropriate headers for the CSV file
            $this->getResponse()->setHeader('Content-Type', 'text/csv');
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="download.csv"');

            // Get the absolute path of the CSV file
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $absolutePath = $mediaDirectory->getAbsolutePath($csvFilePath);

            // Return the CSV file as the response
            return $this->fileFactory->create(
                'download.csv',
                [
                    'type' => 'filename',
                    'value' => $absolutePath,
                    'rm' => true // Automatically remove the file after download
                ],
                DirectoryList::MEDIA,
                'application/octet-stream'
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error occurred while downloading the CSV file.'));
        }

        // If an error occurred, redirect to a fallback page
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
