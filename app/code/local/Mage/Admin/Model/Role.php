<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Admin Role Model
 *
 * @method Mage_Admin_Model_Resource_Role _getResource()
 * @method Mage_Admin_Model_Resource_Role getResource()
 * @method int getParentId()
 * @method Mage_Admin_Model_Role setParentId(int $value)
 * @method int getTreeLevel()
 * @method Mage_Admin_Model_Role setTreeLevel(int $value)
 * @method int getSortOrder()
 * @method Mage_Admin_Model_Role setSortOrder(int $value)
 * @method string getRoleType()
 * @method Mage_Admin_Model_Role setRoleType(string $value)
 * @method int getUserId()
 * @method Mage_Admin_Model_Role setUserId(int $value)
 * @method string getRoleName()
 * @method Mage_Admin_Model_Role setRoleName(string $value)
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Role extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('admin/role');
    }
}
