<?php

class Harapartners_Import_Model_Mysql4_Import_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('import/import');
    }
}