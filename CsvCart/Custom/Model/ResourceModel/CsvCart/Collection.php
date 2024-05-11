<?php
namespace CsvCart\Custom\Model\ResourceModel\CsvCart;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CsvCart\Custom\Model\CsvCart;
use CsvCart\Custom\Model\ResourceModel\CsvCart as CsvCartResourceModel;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(CsvCart::class, CsvCartResourceModel::class);
    }
}
