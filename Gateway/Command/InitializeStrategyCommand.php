<?php
namespace ZipMoney\ZipMoneyPayment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Framework\Phrase;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

/**
 * Class CaptureStrategyCommand
 */
class InitializeStrategyCommand   implements CommandInterface
{
   
  /**
   * @var LoggerInterface
   */
  private $logger;

  public function __construct(
      LoggerInterface $logger
  ) {
      $this->logger = $logger;
  }


  /**
   * Executes command basing on business object
   *
   * @param array $commandSubject
   * @return void
   */
  public function execute(array $commandSubject){}
}
