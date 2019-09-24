<?php
//support install without composer like FTP or saved code in git
if (!class_exists("\zipMoney\Api\ChargesApi") && file_exists(BP . "/lib/internal/merchantapi-php/autoload.php")) {
    require_once(BP . "/lib/internal/merchantapi-php/autoload.php");
}
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ZipMoney_ZipMoneyPayment',
    __DIR__
);
