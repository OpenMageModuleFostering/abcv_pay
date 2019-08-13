<?php
 
/**
* Our test CC module adapter
*/
/* PAYMENT */
define('CHILD_KEY_LOCATION', __DIR__.'/../keys/childKeyQA1');
define('PARENT_KEY_LOCATION',__DIR__.'/../keys/parentKeyQA1');


class Abcv_AbcPay_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    // const CGI_URL = 'https://staging.linkpt.net:1129/LSGSXML';

    const REQUEST_METHOD_CC     = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';

    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT       = 'CREDIT';
    const REQUEST_TYPE_VOID         = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    const ECHECK_ACCT_TYPE_CHECKING = 'CHECKING';
    const ECHECK_ACCT_TYPE_BUSINESS = 'BUSINESSCHECKING';
    const ECHECK_ACCT_TYPE_SAVINGS  = 'SAVINGS';

    const ECHECK_TRANS_TYPE_CCD = 'CCD';
    const ECHECK_TRANS_TYPE_PPD = 'PPD';
    const ECHECK_TRANS_TYPE_TEL = 'TEL';
    const ECHECK_TRANS_TYPE_WEB = 'WEB';

    const RESPONSE_DELIM_CHAR = ',';

    const RESPONSE_CODE_APPROVED = 1;
    const RESPONSE_CODE_DECLINED = 2;
    const RESPONSE_CODE_ERROR    = 3;
    const RESPONSE_CODE_HELD     = 4;

  

    protected $_authorize   = '';

    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'AbcPay';
 
    /**
     * Here are examples of flags that will determine functionality availability
     * of this module to be used by frontend and backend.
     *
     * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
     *
     * It is possible to have a custom dynamic logic by overloading
     * public function can* for each flag respectively
     */
     
    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    protected $_isGateway               = true;
 
    /**
     * Can authorize online?
     */
    protected $_canAuthorize            = true;
 
    /**
     * Can capture funds online?
     */
    protected $_canCapture              = true;
 
    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial       = false;
 
    /**
     * Can refund online?
     */
    protected $_canRefund               = true;
 
    /**
     * Can void transactions online?
     */
    protected $_canVoid                 = true;
 
    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal          = true;
 
    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout          = true;
 
    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping  = true;
 
    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = true;
 
    /**
     * Here you will need to implement authorize, capture and void public methods
     *
     * @see examples of transaction specific public methods such as
     * authorize, capture and void in Mage_Paygate_Model_Authorizenet
     */

    
    public function authorize(Varien_Object $payment, $amount){
       
     
   
    }

    public function capture(Varien_Object $payment, $amount){

        

        $order = $payment->getOrder();

     
        $billing = $order->getBillingAddress();

      
         $cardNum =    $this->encrypt($payment->getCcNumber());


         $creditCardLast4Digits = substr($payment->getCcNumber(),strlen($payment->getCcNumber())-4,4);

         $creditCardInfo = array(
                           
                            'cardNum' => $cardNum,
                            'creditCardLast4Digits' => $creditCardLast4Digits,
                           
                            'expYear'               => $payment->getCcExpYear(),
                            'expMonth'              => $payment->getCcExpMonth(),
                            'cardCodePresent'       => 'ValuePresent',
                            'cardCode'              => $payment->getCcCid(), 
                            'cardholderFirstName'   => $billing->getFirstname(),
                            'cardholderLastName'    => $billing->getLastname(),
                            'cardHolderCompanyName' => null,
                            'cardHolderFullName'    => $billing->getFirstname() . ' ' . $billing->getLastname(),                                                               
                            'track1'                => null,
                            'track2'                => null,
                            'streetAddressLine1'    => $billing->getStreet(1),
                            'zip'                   => $billing->getPostcode(),
                            'ext'                   => '',                                  
                            'cardType'              => 'Visa', 
                            'entryMode'             => 'Manual',
                            'signature'             => null,
                            'cardPresent'           => 'NO',                                
                        );  


        $response =  $this->makePayment(
                        array(
                        "creditCardInfo" => $creditCardInfo,  
                        'paymentAccountId' => '0',
                        'amount' => $amount,
                        'action' => 'payment',
                       
                        'useTestMode' => $this->getConfigData('test') ? 'YES' : 'NO'

                        ), $payment->getOrder()->getEntityId());


        if ($response->success){

            $payment_engine_id = $response->merchantReferenceNumber;

             

            // create transaction 
            if ($payment->getParentTransactionId()) {
                $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
            } else {
                $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
            }   

            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setCcTransId($payment_engine_id);
            $payment->setLastTransId($payment_engine_id);
            if ($payment_engine_id != $payment->getParentTransactionId()) {
                $payment->setTransactionId($payment_engine_id);
            }           
            $payment
                ->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('real_transaction_id', $payment_engine_id);
            // end create

            // update last transaction id
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write->query("UPDATE sales_flat_order_payment SET last_trans_id ='".$payment_engine_id."' WHERE entity_id =".$payment->getOrder()->getEntityId());

            // end update 

            $order->addStatusToHistory(
                $order->getStatus(),
                'Captured amount of  $'.$amount.' online at ABC Pay. Trans ID: ' . $payment_engine_id,
                'Captured amount of  $'.$amount.' online from ABC Pay. Trans ID: ' . $payment_engine_id
            );

        }
        else{

            $order->addStatusToHistory(
                $order->getStatus(),
                $response->commonResponse->errorDescription.' at ABC Pay',
                $response->commonResponse->errorDescription.' from ABC Pay'
            );

            Mage::throwException($response->commonResponse->errorDescription);


        }

        return $this;

    }
    function makePayment($options, $clientRequestNumber){    
      
        $response = new stdClass();
        $response->success = false;
        $response->commonResponse = new stdClass();
       
       
        if ($this->url_exists($this->getConfigData('payment_gateway_wsdl_path'))){

            $payment = array('clientRequestNumber' => $clientRequestNumber,
                            'credentials' => array(
                                            'username' => $this->getConfigData('payment_username'),
                                            'password' => $this->getConfigData('payment_pass'),
                                            'merchantID' => $this->getConfigData('payment_merchant_ide'),
                                            'channelID1' => $this->getConfigData('payment_merchant_cha'), 
                                           
                                            'useTestMode' => $this->getConfigData('test') ? 'YES' : 'NO'

                                            ),
                            'creditCard' => $options['creditCardInfo'],
                            'consecutiveCount' => 'ONE',                            
                            'merchantRequest1' => array(
                                                'description' => null,
                                                'amount' => round($options['amount'],2)                     
                                                )
                );
          
            //submit request
            $payment_options = array(
                    'location' => $this->getConfigData('payment_gateway_path')
            );

             

            $soapClient = new SoapClient($this->getConfigData('payment_gateway_wsdl_path'), $payment_options);
            
            try {
                
                $response = $soapClient->submitCreditCardPayment($payment);
                
            } catch (SoapFault $exception) {
                $response->commonResponse->errorDescription = "Couldn't process the request - Error code: $exception->faultcode";
            }   


        }else{
            $response->commonResponse->errorDescription = "Couldn't connect to MerchantService.";
        }

        if (isset($clientRequestNumber) && isset($response)){
            if($response->success == false && isset($response->commonResponse)
                    && isset($response->commonResponse)){
                if(isset($response->commonResponse->errorDescription)){
                    $response->commonResponse->errorDescription = $response->commonResponse->errorDescription;
                }else if(isset($response->commonResponse->displayMessages)){
                    $response->commonResponse->errorDescription = $response->commonResponse->displayMessages;   
                }
                        
            }
        }
       

          
        return $response;    
    }   
    

    public function void(){

        $file=fopen("text.txt", "a");
         $write=fwrite($file, ' void ');
         fclose($file);


        // $payment = array(
        //                 'authenticationInfo' => array('clientID' => "Client34349821758574"), 
        //                 'authorizationInfo' =>
        //                     array(
        //                         'adminUser' => $adminUserForVoidRefund,
        //                         'adminPwd'  => $adminPwdForVoidRefund),
        //                 'transactionDetails' => 
        //                     array(
        //                         'creditCardInfo' => 
        //                             array(
        //                                 'cardCodePresent' => 'ValuePresent',
        //                                 'entryMode'       => 'Manual',
        //                                 'cardPresent'     => 'NO'),
        //                         'paymentMethod' => 'CC',
        //                         'amount' => "9.99",
        //                         'requestType' => 'VOID' 
        //                         ),
        //                 'requestID' => "ORDER_ID",
        //                 'previousTransactionId' => 'paymentEngineId',
        //                 );

        // $options = array(
        //                 'location' => $this->getConfigData('payment_gateway_path')
        //             );              
        // $soapClient = new SoapClient($this->getConfigData('payment_gateway_wsdl_path'), $options);

        // try {
        //     $response = $soapClient->submitPayment($payment);
        //     $response->clientRequestNumber = $clientRequestNumber;
        // } catch (SoapFault $exception) {
        //     //handle exception
        // }               

    }
    public function refund(Varien_Object $payment, $amount){

         
         $order_id = $payment->getOrder()->getEntityId();

      
        if ($payment->getRefundTransactionId() && $amount>0) {

            $payment_engine_id = '';
         


            if ($order_id){

                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $order_payment = $read->fetchAll("SELECT * from sales_flat_order_payment WHERE entity_id =".$order_id);

                $payment_engine_id = isset($order_payment[0]['last_trans_id']) ? $order_payment[0]['last_trans_id'] : ''; 
                
             }
             $description = isset($_REQUEST['creditmemo']['comment_text']) ? $_REQUEST['creditmemo']['comment_text'] : '';

            

            $refundRequest = array(
                                'clientRequestNumber' => $payment->getOrder()->getEntityId(),
                                    'credentials' =>
                                    array(
                                        'username' => $this->getConfigData('payment_username'),
                                        'password'  => $this->getConfigData('payment_pass'),
                                        'merchantID' => $this->getConfigData('payment_merchant_ide'),
                                        'channelID1' => $this->getConfigData('payment_merchant_cha'),
                                     
                                        'useTestMode' => $this->getConfigData('test') ? 'YES' : 'NO'
                                    ),
                                            'merchantReferenceNumber' => $payment_engine_id,
                                            'consecutiveCount' => 'ONE',                                                        
                                            'merchantRequest1' =>
                                            array(
                                                'description' => empty($description) ? null : $description,
                                                'amount'  => round($amount,2)                            
                                            )
                                );
                        
            try {
                $options = array(
                    'location' => $this->getConfigData('payment_gateway_path')
                );
                $soapClient = new SoapClient($this->getConfigData('payment_gateway_wsdl_path'), $options);
                $responseRefund = $soapClient->submitCreditCardRefund($refundRequest);
            } catch (SoapFault $exception) {
                //handle exception

                $responseRefund->success = false;
              

                $order->addStatusToHistory(
                    $order->getStatus(),
                    $exception->__toString().' at ABC Pay',
                    $exception->__toString().' from ABC Pay'
                ); 

                Mage::throwException($exception->__toString());
              
            }
           

            if ($responseRefund->success){

                $payment->setStatus(self::STATUS_SUCCESS);
                if ($payment_engine_id != $payment->getParentTransactionId()) {
                    $payment->setTransactionId($payment_engine_id);
                }
                $shouldCloseCaptureTransaction = $payment->getOrder()->canCreditmemo() ? 0 : 1;
                $payment
                     ->setIsTransactionClosed(1)
                     ->setShouldCloseParentTransaction($shouldCloseCaptureTransaction)
                     ->setTransactionAdditionalInfo('real_transaction_id', $payment_engine_id);    

                $order->addStatusToHistory(
                    $order->getStatus(),
                    'Refunded amount of  $'.$amount.' online at ABC Pay. Trans ID: ' . $payment_engine_id,
                    'Refunded amount of  $'.$amount.' online from ABC Pay. Trans ID: ' . $payment_engine_id
                );    
                
            } 
            else {

                $order->addStatusToHistory(
                    $order->getStatus(),
                    $responseRefund->commonResponse->errorOccurred.' at ABC Pay',
                    $responseRefund->commonResponse->errorOccurred.' from ABC Pay'
                );   

               
                Mage::throwException($responseRefund->commonResponse->errorOccurred);
            }


         } 
         else {

            $error = 'Error in refunding the payment';

            $order->addStatusToHistory(
                $order->getStatus(),
                $error.' at ABC Pay',
                $error.' from ABC Pay'
            );   

            Mage::throwException($error);
        }

       
        return $this;
    }
    public function encrypt($data) {
        $childKeyLocation = CHILD_KEY_LOCATION;
        $parentKeyLocation = PARENT_KEY_LOCATION;

        if(!file_exists($childKeyLocation)){

            modules::run('adminlog/saveLog',"Encrypt error","Error in Store::encrypt()-->childKey doesn't exist'<--");
        }       
        if(!file_exists($parentKeyLocation)){
            modules::run('adminlog/saveLog',"Encrypt error","Error in Store::encrypt()-->parentKey doesn't exist'<--");
        }

        $algorithm = MCRYPT_RIJNDAEL_128;
        $blockMode = MCRYPT_MODE_ECB;
        // Load parent key
        $handle = fopen($parentKeyLocation, "rb");
        $contents = fread($handle, filesize($parentKeyLocation));
        fclose($handle);        
        $parentKey = $contents;
        
        // Load child key                       
        $handle = fopen($childKeyLocation, "rb");
        $contents = fread($handle, filesize($childKeyLocation));
        fclose($handle);        
        $childKey = base64_decode($contents);
        
        // Decrypt child key using parent key, this yields the key to perform the encryption with
        $encryptionKey = $this->pkcs5_unpad(mcrypt_decrypt($algorithm, $parentKey, $childKey, $blockMode));

        // padd the data to encrypt
        $data = $this->pkcs5_pad($data, mcrypt_get_block_size($algorithm, $blockMode));
        // encrypt the data
        $encryptedData = mcrypt_encrypt($algorithm, $encryptionKey, $data, $blockMode);
        // return the encrypted data as base64
        return base64_encode($encryptedData);
    }
    
    public function pkcs5_pad ($text, $blocksize) 
    { 
        $pad = $blocksize - (strlen($text) % $blocksize); 
        return $text . str_repeat(chr($pad), $pad); 
    } 
    public function pkcs5_unpad($text) 
    { 
        $pad = ord($text{strlen($text)-1}); 
        if ($pad > strlen($text)) return false; 
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false; 
        return substr($text, 0, -1 * $pad); 
    }

    public function url_exists($url) {
        if(@file_get_contents($url,0,NULL,0,1))
        {
            return true;
        }
        else
        { 
            return false;
        } 
    }
}
?>