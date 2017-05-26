<?php
namespace ZipMoney\ZipMoneyPayment\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $urlRewrite;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        UrlRewrite $urlRewrite
    ) {
        $this->salesSetupFactory = $salesSetupFactory;        
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->urlRewrite = $urlRewrite;

    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

        /**
         * Add 'NEW_ATTRIBUTE' attributes for order
         */
        $salesSetup->addAttribute('order_payment', 'zipmoney_charge_id', 
            ['label' => 'zipMoney Checkout Id', 'type' => 'varchar', 'visible' => false, 'required' => false]);   
        $quoteSetup->addAttribute('quote', 'zipmoney_checkout_id', 
            ['type' => 'varchar', 'visible' => false, 'required' => false]);   
        
        $rewriteCollection = $this->urlRewrite->getCollection()
                                              ->addFieldToFilter('target_path', "zipmoney");

        if (count($rewriteCollection) > 0) 
            return false;

        $this->urlRewrite
                ->setIsSystem(0)
                ->setStoreId(1)
                ->setIdPath('zipmoney-landingpage')
                ->setTargetPath("zipmoney")
                ->setRequestPath('zipmoney')
                ->save();

        /**  
          * Install  Status and States
          * 
          */

        $statuses = [
            'zip_authorised' => __('zipMoney Authorised')
        ];

        /**
         * Check if status exists already
         */
        $new = [];
        $data = [];
        foreach ($statuses as $key => $label ) {
            $select = $setup->getConnection()->select()
              ->from(array('e' => $setup->getTable('sales_order_status')))
              ->where("e.status=?", $key);
            $result = $setup->getConnection()->fetchAll($select);             
            if (!$result) {           
              $new[$key] = $label;
            }
        }

        foreach ($new as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }

        if(count($data)>0){
          $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);
        }

        // Order States
        $states = array(
          array('zip_authorised', 'pending_payment', 0)
        );


        /**
         * Check if state exists already
         */
        $new = [];        
        $data = [];

        foreach ($states as $status) {
          $select = $setup->getConnection()->select()
                          ->from(array('e' => $setup->getTable('sales_order_status_state')))
                          ->where("e.status=?", $status[0]);
          $result = $setup->getConnection()->fetchAll($select);
          if (!$result) {          
            $new[] = $status;
          }
      }
      

      if(count($new)>0) {
        $setup->getConnection()->insertArray(
            $setup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default'],
            $new
        );
      }

      $setup->endSetup();
    }

}