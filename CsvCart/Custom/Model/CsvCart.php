<?php
namespace CsvCart\Custom\Model;

use Magento\Framework\Model\AbstractModel;

class CsvCart extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\CsvCart\Custom\Model\ResourceModel\CsvCart::class);
    }

    /**
     * Retrieve CSV path for the CSV file
     *
     * @return string|null
     */
    public function getCsvPath()
    {
        return $this->getData('csv_file_path');
    }

    /**
     * Load CSV data from the file
     *
     * @param string $csvFilePath
     * @return $this
     */
    public function loadByCsvFilePath($csvFilePath)
    {
        $this->setData('csv_file_path', $csvFilePath);
        $this->loadCsvData();
        return $this;
    }

    /**
     * Load CSV data from the file
     *
     * @return $this
     */
    public function loadCsvData()
    {
        $csvFilePath = $this->getCsvPath();
        if ($csvFilePath && file_exists($csvFilePath)) {
            $file = fopen($csvFilePath, 'r');
            $csvData = [];
            while (($line = fgetcsv($file)) !== false) {
                $csvData[] = $line;
            }
            fclose($file);
            $this->setData('csv_data', $csvData);
        }
        return $this;
    }

    /**
     * Retrieve the product data from the CSV file
     *
     * @return array|null
     */
    public function getProductData()
    {
        $csvData = $this->getData('csv_data');
        $productData = [];

        if (is_array($csvData) && !empty($csvData)) {
            foreach ($csvData as $row) {
                // Assuming the product SKU is in the first column and quantity is in the second column of the CSV data
                $productData[] = [
                    'sku' => $row[0] ?? null,
                    'qty' => isset($row[1]) ? (int)$row[1] : 0 // Assuming quantity is an integer
                ];
            }
        }

        return $productData;
    }
}
