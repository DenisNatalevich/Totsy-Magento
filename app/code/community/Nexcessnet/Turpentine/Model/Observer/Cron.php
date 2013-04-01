<?php

/**
 * Nexcess.net Turpentine Extension for Magento
 * Copyright (C) 2012  Nexcess.net L.L.C.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class Nexcessnet_Turpentine_Model_Observer_Cron extends Varien_Event_Observer {

    /**
     * Max time to crawl URLs if max_execution_time is 0 (unlimited) in seconds
     *
     * @var int
     */
    const MAX_CRAWL_TIME    = 300;

    /**
     * Amount of time of execution time to leave for other cron processes
     *
     * @var int
     */
    const EXEC_TIME_BUFFER  = 15;

    /**
     * Crawl available URLs in the queue until we get close to max_execution_time
     * (up to MAX_CRAWL_TIME)
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function crawlUrls( $eventObject ) {
        $helper = Mage::helper( 'turpentine/cron' );
        if( $helper->getCrawlerEnabled() ) {
            $maxRunTime = $helper->getAllowedRunTime();
            if( $maxRunTime === 0 ) {
                $maxRunTime = self::MAX_CRAWL_TIME;
            }
            while( ( $helper->getRunTime() < ( $maxRunTime - self::EXEC_TIME_BUFFER ) ) &&
                    $url = $helper->getNextUrl() ) {
                if( !$this->_crawlUrl( $url ) ) {
                    Mage::log(
                        sprintf( 'TURPENTINE: Failed to crawl URL: %s', $url ),
                        Zend_Log::WARN );
                }
            }
        }
    }

    /**
     * Queue all URLs
     *
     * @param  Varien_Object $eventObject
     * @return null
     */
    public function queueAllUrls( $eventObject ) {
        $helper = Mage::helper( 'turpentine/cron' );
        if( $helper->getCrawlerEnabled() ) {
            $helper->addUrlsToCrawlerQueue( $helper->getAllUrls() );
        }
    }

    /**
     * Request a single URL, returns whether the request was successful or not
     *
     * @param  string $url
     * @return bool
     */
    protected function _crawlUrl( $url ) {
        $client = Mage::helper( 'turpentine/cron' )->getCrawlerClient();
        $client->setUri( $url );
        if( Mage::helper( 'turpentine/cron' )->getCrawlerDebugEnabled() ) {
            Mage::log( 'TURPENTINE: Crawling URL: ' . $url );
        }
        try {
            $response = $client->request();
        } catch( Exception $e ) {
            Mage::log( sprintf( 'TURPENTINE: Error crawling URL (%s): %s',
                    $url, $e->getMessage() ),
                Zend_Log::WARN );
            return false;
        }
        return $response->isSuccessful();
    }
}
