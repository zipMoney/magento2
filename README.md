# Magento2 zipMoneyPayment

## Installation

### Install using Composer

To install the extension via [Composer](http://getcomposer.org/), run

- `cd <your Magento install dir>`
- `composer require zipmoney/magento2`
- `php bin/magento setup:upgrade`
- `php bin/magento setup:di:compile`
- `php bin/magento setup:static-content:deploy en_AU en_US`

To upgrade Existing Modules

- `composer update zipmoney/magento2`
- `php bin/magento setup:di:compile`
- `php bin/magento c:c`
- `php bin/magento setup:static-content:deploy en_AU en_US`

### Install using Magento Component Manager

Note:- This extension is not yet available in the Magento Marketplace. This section will be updated once it is released in the marketplace.

http://docs.magento.com/marketplace/user_guide/quick-tour/install-extension.html

## Configuration

### Payment Section
1. Open the Magento Admin
2. Click    the Stores  icon    in  the left    hand menu   and from    there   choose  Settings    >   Configuration
3. The  configuration   page    will    open.   From  the little  menu    on  the left    hand    side    of  this    screen  you must    click   Sales  and then
choose  Payment Methods when    it  expands.
4. On   the Payment Methods page,   click   Other   Payment Methods so  it  expands.

![Alt text](https://static.zipmoney.com.au/github-images/payment-section.png "Payment Section")

1. Set Enable  to  Yes and a   title   for the payment method  “zipMoney   – Buy   Now Pay Later”  or  “zipPay – Buy   Now Pay Later”
2. Enter the   Private Key and Public  Key.
3. Select   your    product type    (zipPay or  zipMoney)
4. Set  payment action  to  Capture, or  Authorise   if  you want    to  authorise   on  checkout    completion  and capture later
5. Set  log settings    to  Info or Debug if you want to log all the debug information as well.
6. Set  environment to  either  Sandbox (for    your    test    or  development site)   or  Production  (for    your    live    website)
7. Set  In-Context  Checkout    to  Yes to enable iframe checkout
8. Set  Sort    Order   to  0 to place the payment method on top.

### Marketing Banners and Widgets Section
![Alt text](https://static.zipmoney.com.au/github-images/marketing-section.png "Markting Banners and Widgets Section")

1. Scroll down  and expand  Marketing   Banners and Widgets section
2. Expand   everything  and set all options to Yes/No as per your requirement.
3. Click    Save    Config  up  the top