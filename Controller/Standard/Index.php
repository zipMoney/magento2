<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;
       
use Magento\Checkout\Model\Type\Onepage;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Index extends AbstractStandard
{   
  /**
   * Start the checkout by requesting the redirect url and checkout id
   *
   * @return json
   * @throws \Magento\Framework\Exception\LocalizedException
   */
  public function execute()
  {
    try {    
      $this->_logger->info("Starting Checkout");
      // Do the checkout
      $this->_initCheckout()->start();

      // Get the redirect url
      if($redirectUrl = $this->_checkout->getRedirectUrl()) {
      
        $this->_logger->info(sprintf(__('Successful to get redirect url [ %s ] '), $redirectUrl));

        $data = array(
            'redirect_uri'      => $redirectUrl,
            'message'    => __('Redirecting to zipMoney.')
        );                    
        return $this->_sendResponse($data,\Magento\Framework\Webapi\Response::HTTP_OK);
      } else {        
        throw new \Magento\Framework\Exception\LocalizedException(__('Could not get the redirect url'));
      }
    } catch (\Exception $e) {                
      $this->_logger->debug($e->getMessage());
    }

    if(empty($result['error'])){
      $result['error'] = __('Can not get the redirect url from zipMoney.');
    }

    return $this->_sendResponse($result, \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
  }
}