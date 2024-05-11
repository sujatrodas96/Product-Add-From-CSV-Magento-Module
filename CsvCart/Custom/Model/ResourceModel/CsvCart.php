<?php
namespace CsvCart\Custom\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CsvCart extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('custom_csv_cart', 'entity_id');
    }
}
