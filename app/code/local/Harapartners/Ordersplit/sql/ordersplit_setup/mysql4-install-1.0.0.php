<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

$installer = $this;

$installer->startSetup();

$installer->run("
INSERT INTO sales_order_status
VALUES ('splitted','Splitted');
INSERT INTO sales_order_status_state
VALUES ('splitted','splitted','0');
");

$installer->endSetup();