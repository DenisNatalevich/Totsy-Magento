<?php
/**
 * @category    Totsy
 * @package     Totsy_Catalog_Model_Resource_Eav_Mysql4
 * @author      Jimmy Dinkers <dinkers@dinkers.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
class Totsy_Catalog_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Retrieve new customer entity attributes.
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'catalog_product'                => array(
                'entity_model'                   => 'catalog/product',
                'attribute_model'                => 'catalog/resource_eav_attribute',
                'table'                          => 'catalog/product',
                'additional_attribute_table'     => 'catalog/eav_attribute',
                'entity_attribute_collection'    => 'catalog/product_attribute_collection',
                'attributes'                     => array(
                    'internal_dept' => array(
                        'type'        => 'varchar',
                        'label'       => 'Internal Department',
                        'input'       => 'text',
                        'sort_order'  => 25,
                        'apply_to'    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                        'required'    => false,
                    ),
                    'fulfillment_inventory' => array(
                        'type'        => 'int',
                        'label'       => 'Fulfillment Inventory',
                        'input'       => 'text',
                        'sort_order'  => 5,
                        'apply_to'    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                        'required'    => false,
                    ),
                    'tax_class' => array(
                        'type'        => 'int',
                        'label'       => 'Speed Tax Class',
                        'input'       => 'select',
                        'source'      => 'catalog/product_attribute_source_tax',
                        'sort_order'  => 6,
                        'apply_to'    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                        'required'    => false,
                    ),
                    'upc' => array(
                        'type'        => 'varchar',
                        'label'       => 'UPC',
                        'input'       => 'text',
                        'sort_order'  => 10,
                        'apply_to'    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                        'required'    => false,
                    ),
                    'purchase_max_sale_qty' => array(
                        'type'        => 'int',
                        'label'       => 'Purchase Max Sale Quantity',
                        'input'       => 'text',
                        'sort_order'  => 26,
                        'apply_to'    => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                        'required'    => false,
                    )
                )
            )
        );

        return $entities;
    }
}
