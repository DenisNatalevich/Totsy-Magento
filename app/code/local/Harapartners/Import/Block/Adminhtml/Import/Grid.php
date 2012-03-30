<?php

class Harapartners_Import_Block_Adminhtml_Import_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('importGrid');
      $this->setDefaultSort('import_import_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('import/import')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('import_import_id', array(
          'header'    => Mage::helper('import')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'import_import_id',
      ));

      $this->addColumn('import_title', array(
          'header'    => Mage::helper('import')->__('Title'),
          'align'     =>'left',
          'index'     => 'import_title',
      ));
      
      $this->addColumn('import_filename', array(
          'header'    => Mage::helper('import')->__('Filename'),
          'align'     =>'left',
          'index'     => 'import_filename',
      ));
      
      $this->addColumn('import_status', array(
          'header'    => Mage::helper('import')->__('Status'),
          'align'     =>'left',
          'index'     => 'import_status',
      ));
      
      $this->addColumn('created_time', array(
          'header'    => Mage::helper('import')->__('Created'),
          'align'     =>'left',
          'index'     => 'created_time',
      ));
      
      $this->addColumn('update_time', array(
          'header'    => Mage::helper('import')->__('Updated'),
          'align'     =>'left',
          'index'     => 'update_time',
      ));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('import_id');
        $this->getMassactionBlock()->setFormFieldName('import');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('import')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('import')->__('Are you sure?')
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}