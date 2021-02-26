# Magento2 zipMoneyPayment

## Updated Module

This version of the Zip payments module has now been superseded by the latest version seen [here](https://github.com/zipMoney/zip.magento2).

This version will still function as expected, though the updated version has improvements and removes some unneeded dependencies:

- More standard install location inside M2
- Support for Partial Capture
- Global calculator widgets 

## Installation

Detail installation please visit <https://zip-magento2.api-docs.io/v1/integration-steps/2-download-zip-extension> for more updated information

### Install via SFTP or file upload

- You need to download code from [merchantapi-php](https://github.com/zipMoney/merchantapi-php) and current repo
- Put the current repo files into folder `/yourMagento2root/app/code/ZipMoney/ZipMoneyPayment/`
- Put merchantapi-php files into `/yourMagento2root/lib/internal/merchantapi-php/`
- Then follow the normal plugin installation below without the composer require command

### Install using Composer

To install the extension via [Composer](http://getcomposer.org/), run

- `cd <your Magento install dir>`
- `composer require zipmoney/magento2`
- `php bin/magento module:status` (Check if ZipMoney_ZipMoneyPayment module is disabled)
- `php bin/magento module:enable ZipMoney_ZipMoneyPayment` (enabled plugin if disabled)
- `php bin/magento setup:upgrade`
- `php bin/magento setup:di:compile`
- `php bin/magento setup:static-content:deploy en_AU en_US` (specify your website regions, or leave it empty for all)

To upgrade Existing Modules

- `composer update zipmoney/magento2`
- `php bin/magento setup:di:compile`
- `php bin/magento c:c`
- `php bin/magento setup:static-content:deploy en_AU en_US`

### Install using Magento Component Manager

Note:- This extension is not yet available in the Magento Marketplace. This section will be updated once it is released in the marketplace.

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