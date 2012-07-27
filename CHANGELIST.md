Totsy-Magento Release Notes
===========================

20120727
--------
* MGN-604: Correctly calculate commission as a percentage of profit.
* MGN-754 Auto select the vendor code when importing to an existing event
* MGN-745: added custom pixel helper for returning customized text in an affiliate pixel, with TrialPay's HMAC-MD5 signed querystring as the first usage of this functionality.
* Added 'Remove' button for affiliate tracking codes.
* MGN-604: Added commission calculation to orders, for usage in affiliate pixels.
* Added 'auto' parameter to the redirect URL after autoregistration/autologin.
* Fixing Status not Updated when Payment Edit Order with same Credit Card
* MGN-278 Better error handling when special characters break product import
* adding reference to home_banner on homepage
* MGN-847 admin user's email for default prompt value
* fix event preview for virtual events, MGN-826 fix instant direct for events with one product event is not live
* MGN-847 : You can now send yourself a test email for virtual products by providing email(s) in the prompt
* MGN-783 add discover image to add credit card on mms
* MGN-747: Accepting Keyade-requested date formats in affiliate data feeds.
* MGN-835: Defensive check for shipping address on an order, which fails for virtual orders.
* MGN-278 Improved error handling for product imports

20120725
--------

* MGN-718 #comment Fixing ESD check for virtual to be item-based
* MGN-815 mms color changes ;
* MGN-813 buton padding on mms
* MGN-840 change grand total to total for legal/confusion
* MGN-840 new sales view with cart disclaimer
* MGN-583 adding sales order view to iphone template to remove track link
* MGN-783 adding discover to add new cc page (will require akamai flush on production push)
* MGN850 - Always print status in order comment section
* Fix incompatible with  Mage_Adminhtml_Block_Widget_Form::_setFieldset()
* Dont show voucher if payment failed on order page
* Disable email Order Confirmation/ Voucher Email when payment failed
* MGN-821 adding link color to 404 cells for mms
* hide expired events at category/age landing page

20120724
--------

* changed the item queue batch cancel to allow the cancelling of suspended items
* change error message 203
* remove caracter from payment failed message
* MGN-821 adding missing css for mms 404 view
* MGN-841 add switch to category preview controller to allow disabled events to be previewed in admin
* changed order view in admin to show total canceled. fixed total due line to take into account canceled amounts. when items are batch canceled and items remain in order, if order contains discounts, points, or store credit the order will be marked as 'Batch Cancel - CSR Review' so CSRs can manually adjust order accordingly.
* MGN693 - Cancel an Order if the payment failed status is older than 7 days
* Add script to cancel Orders with 7 days older payment failed update
* Adding configuration section for e-mail template to use for auto-generated customer welcome e-mails.
* MGN-704: Sending an e-mail containing the auto-generated password when creating customer accounts automagically.
* Dotcom test fulfillment script can accept the order ID as a single parameter on the command line, or will execute the full fulfillment process when no arguments are supplied.
* tweaked page cache to not cache pages on a customer by customer basis, but only for customer groups
* MGN846: Print more informations when a payment failed
* Fix for 500 problen on category/age langing page
* fix steelhouse pixel issue
* modified dotcom fulfillment to invoice the order and then capture the invoice rather than use order payments for capturing payment. also added in calculation of canceled amounts for order in batch cancel process
* Batch Cancel Process now only cancels the items off the orders. Required modifying the sales order model to allow orders to be canceled when items have already been canceled individually. Also modified invoicing process to not invoice canceled items.

20120723
--------
* MGN-718 #resolve #comment Adding Estimated Shipping Date to order confirmation email for physical products.
* Updated a few feed names for the keyade feeds.
* Populating additional event information (departments/ages/max discount pct) by inspecting event products, and storing this in category sort entries.
* MGN-728: Added the 'Not you?' link, and updated the title on the Revalidation page.
* MGN-835: added adwords conversion tags.
* Fixing payment update module when a credit card entered is identical as one of the saved credit card.


20120720-1
--------
* Tweaking order history layout and status message in order history.

20120720
--------
* Put back estimated shipdate / shipping edit functionality
* Fix PO quantity total : Removing order by vendor style

20120719
--------

* fix Event name in Order Page
* Last Fix Order Payment module
* hide saved credit card
* Add correct Email when Profile is created
* Payment Module : Billing Required Fields
* the variable totalQty was not initialized, which prevented logistics from printing the PO.
* fixed a couple 404 errors on the mobile site related to missing js and css files. finished up header include of user's name and cart info via ajax
* tweaked the after login and after registration redirects to force the user to go the events page if no redirect is already set in their session.
* MGN-695 - Add Payment Informations Module to Order Admin Page
* MGN-676 Almost made a mistake of never setting the qty variable, which would have caused the result for prebuys to always be zero
* MGN-669 logistics can now change item case pack status from the PO Report view
* print credit card profile in order page
* MGN-747: Rebuilt the Affiliate Feeds controller, and allowed public access to the action.
* further changes for header ajax piece to mobile
* Finished tweaks realetd to MGN-802 as per Rob's comment on order stati on virtual orders
* tweaks as per Rob's 07/17/12 comments
* Add All statuses for Order Edit Extension
* manage saved credit card
* Change Text Button, Way how to get Billing Address

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
