Totsy-Magento Release Notes
===========================

20120718
--------

* removing infamous 'test' text in mini-cart huzzahhhhhh
* fixed issue with redirect issue after create account
* MGN-676 fixing calculation of case pack total, included check for amended records
* added affiliate pixel to mobile
* fixed layout issue in pinterest module that caused pixels module layout to not be shown. moved pixels module code to default theme so that pixels will fire for mobile as well as regular site
* MNG-682. Remove SLAV"s dev mode ;)
* Adding estimated ship date below tangible prods in rush checkout
* MGN-682. Hide not upcoming events on age/categoty page(s)
* MGN-826 : any event that has only one product will now be redirected straight to the item instead of the event page and the event page is viewable while it is still an upcoming sale

20120717
--------

* Setup registry in one place at the top of the script.
* Removed ship date for virtual orders on My Orders page
* MGN-604: added support for affiliate tracking codes to use registration params for dynamic template replacement.
* MGN-823 added asterik to image
* MGN-676 - forgot to add the new mysql4 setup script
* MGN-454 correctly percent off calculation
* display total quantity sold/casepack for an event in the PO grid
* MGN-454 correctly percent off calculation
* MGN-454 correctly percent off calculation
* MGN-454 correctly percent off calculation
* Restored Google_Checkout module because of warnings.
* Adding product title and description to the coupon code emails
* MGN-454 correctly percent off calculation
* MGN-205 fix for css cart message
* MGN-533 #resolve #comment Adding correct favicon.ico for IE compatibility, implementing via code rather than admin, updating .gitignore
* Removed the call to load() to eliminate PHP warnings.
* MGN-823 legal copy to login/register
* Added test fixture for a test case for Totsy_Customer_Model_Observer class.
* Removed unnecessary order status/states from config file, because custom states should be administered in admin (and are added by data sql scripts).
* Restored the Mage_AdminNotification module, which should be disabled via admin instead, since the Enterprise_Enterprise module depends on it.
* Finished up last test cases for Totsy_Customer_Model_Observer class.
* Removed the locking down of store models by Harapartners.
* Tightened up spacing and changed some copy as per ticket MGN-818 (steps 1-4)
* Moving estimated ship date on orders that have virtual items to below the item's row in the cart as opposed to the top of the cart
* Started making edits. Still to do are the main cart and review pages
* Added toggleable div for vouchers
* Started new sales order layout changes for virtual items

20120716
--------

* Test cases for Totsy_Customer_Model_Observer class.
* Reverted timing for the dotcom fulfill orders cronjob.
* MGN-790 marketing copy changes
* MGN-530 reduce thumbnail size on mobile checkout
* MGN-821 mms css fix
* MGN-821 mms 404 missing images
* MGN-813 button css for mms
* MGN-756 order id on dashboard column width
* hide percent on event page css mms
* hide percent on event page css
* MGN-454 percent off changes
* MGN-516 new adminhtml block to fix recent orders grid on admin > view customer
* MGN-812 MGN-815 find and replace #ED1C25 for #DE6076 in mms styles
* MGN-822 separate breadcrumbs file from base template, make blank
* MGN-740 add strip gmail to sales > orders grid
* MGN-740 add strip gmail to sales > orders grid
* MGN-790 revised image/messaging on pinterest splash
* MGN-205 css fix
* MGN-476 #resolve #comment Fixing display of logged-in view of reset password page

20120713-1
----------

* change text MGN 738
* add url to webstire restriction whitelist
* new module pinterest xml
* new template phtml for pinterest form
* module config xml
* new module for pinterest form page
* MGN-820 #resolve #comment Fixing font-size and adding bg image for content blocks on My Account page
* fix form submit js MGN790
* MGN-805 #resolve #comment Moar fixins on mamasource linkage
* fixing Google order success tracking

20120713
--------

* pinterest form page building
* Sailthru feed - add mamaource support
* pink nav icons
* remove height from image to fix event page
* pinterest form page building
* pinterest form page building
* pinterest form page building
* removing header
* pinterest form page building
* building form page
* adding config.xml to local core for new cms page type
* redirect to 10 seconds
* revision to badgecity
* new badge for login and reg
* Added unit test cases for Totsy_Customer_Model_Observer class.
* remove tmp cron file fix
* fixed message wording and timezone issue - no longer removes add to cart button 4 hours ahead of time.
* removing copy from blank popup cart
* button padding
* remove debug output from sailthru queue
* Sailthru Api events feed - increase content cache time
* adding some images
* padding on add to cart btn
* branched orders from core to local and added order status to orders grid widget in customer view in admin

20120712
--------

* Reverted page cache changes from cdavidowski.
* Sailthru Api events feed - increase content cache time
* remove tmp cron file fix
* Sailthru Api events feed - increase content cache time
* Fixes for automatic login/registration using a different store. Should auto-detect store though.
* Changed Full Page Cache to not cache pages by customer and only by customer group. Also, removed strange logic from Category and Product cache processors that randomly showed a non-cached page
* Fixing some committed merge issues for mama links
* add copy to button
* Another forced cronjob execution for dotcom orders.
* MGN-805 #resolve #comment Styling mamasource linkage to a nice rgb(222,96,118) pink
* Emergency fix for dotcom cron to execute.
* MGN-794 #comment Fixing slight display issue on login and register screens on mamasource #resolve
* MGN-788: tweaking positioning of public viewable login/regsiter module on mamasource
* MGN-799 Added size and color columns to the manage products grid
* fix var names
* remove rss link from mms order view
* checkout button css
