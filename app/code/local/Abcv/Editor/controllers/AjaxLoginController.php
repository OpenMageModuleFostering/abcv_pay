<?php
class Abcv_Editor_AjaxLoginController extends Mage_Core_Controller_Front_Action
{ 
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('loginPost', 'createpost');

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    /**
     * Login post action
     */
    public function loginPostAction()
    {
        //$result = array();
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                    echo 'success';
                    //$result['message'] = '';
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    //$session->addError($message);
                    //$session->setUsername($login['username']);
                    //$result['result'] = 'error';
                    //$result['message'] = $message;
                    if ($message == "Invalid login or password.")
                        $message = 'Invalid Email Address or Password.';
                    echo $message;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                    //$result['result'] = 'error';
                    //$result['message'] = $e->getMessages();
                    echo $e->getMessages();
                }
            } else {
                //$session->addError($this->__('Login and password are required.'));
                //echo 'Login and password are required';
                //$result['result'] = 'error';
                //$result['message'] = 'Login and password are required';
                echo  'Email Address and Password are required';
            }
        }
        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function getCustomerIdAction()
    {
        $this->loadLayout();
        $result = array();
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $result['id'] =  $customerData->getId();
            $result['name'] =  $customerData->getName();
            
            $result['header'] = $this->getLayout()->getBlock('header')->toHtml();            
                 
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        
    }
        /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                            ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }
/************************************* REGISTER *********************************/
    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();
            $result = array();
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }

            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $customer->setPassword($this->getRequest()->getPost('password'));
                    $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $customer->save();

                    Mage::dispatchEvent('customer_register_success',
                        array('account_controller' => $this, 'customer' => $customer)
                    );

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        //$session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                        //$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                        //echo 'success';
                        $result['result'] = 'success';
                        $result['message'] = $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()));
                        //return;
                    } else {
                        $session->setCustomerAsLoggedIn($customer);
                        //$url = $this->_welcomeCustomer($customer);
                        //$this->_redirectSuccess($url);
                        $result['result'] = 'success';
                        $result['message'] = 'Successfully registered';
                    }
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        $err = '';
                        foreach ($errors as $errorMessage) {
                            //$session->addError($errorMessage);
                            $err .= $errorMessage . '<br/>';
                        }
                        //echo $err;
                        $result['result'] = 'error';
                        $result['message'] = $err;
                    } else {
                        //$session->addError($this->__('Invalid customer data'));
                        $result['result'] = 'error';
                        $result['message'] = 'Invalid customer data';
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, click "Forgot Password" to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                //$session->addError($message);
                 $result['result'] = 'error';
                $result['message'] = $message;
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
                
                $result['result'] = 'error';
                $result['message'] = 'Cannot save the customer';
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }

        //$this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }
    
    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $result = array();
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                //$this->_getSession()->addError($this->__('Invalid email address.'));
                $result['result'] = 'error';
                $result['message'] = 'Invalid Email Address';
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
                //$this->_redirect('*/*/forgotpassword');
                //return;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    //$this->_getSession()->addError($exception->getMessage());
                    $result['result'] = 'error';
                    $result['message'] = $exception->getMessage();
                    //$this->_redirect('*/*/forgotpassword');
                    //return;
                }
            }
            //$this->_getSession()->addSuccess(Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email)));
            $result['result'] = 'success';
            $result['message'] = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
            //$this->_redirect('*/*/');
            //return;
        } else {
            //$this->_getSession()->addError($this->__('Please enter your email.'));
            $result['result'] = 'error';
            $result['message'] = 'Please enter your Email Address';
            //$this->_redirect('*/*/forgotpassword');
            //return;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	public function sendEmailPostAction()
    {
        $result = array();
   	 	//print_r($this->getRequest()->getPost());
        $from_name = (string) $this->getRequest()->getPost('from_name');
        $mail_from = (string) $this->getRequest()->getPost('mail_from');
        $mail_to = (string) $this->getRequest()->getPost('mail_to');
        $mail_to=preg_replace("/ /", "",$mail_to);
        $comments = (string) $this->getRequest()->getPost('comments');
        if ($mail_from && $mail_to) {
            if (!Zend_Validate::is($mail_from, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                //$this->_getSession()->addError($this->__('Invalid email address.'));
                $result['result'] = 'error';
                $result['message'] = 'Your email is Invalid Email Address';
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
            $mail_tos = explode(",", $mail_to);
            //print_r($mail_tos);
            for($i=0;$i<count($mail_tos);$i++)
            {
	        	if (!Zend_Validate::is($mail_tos[$i], 'EmailAddress')) {
	                $this->_getSession()->setForgottenEmail($email);
	                $result['result'] = 'error';
	                $result['message'] = 'To is Invalid Email Address';
	                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	                return;
	            }
            }
		  	
			//$vars['src'] ='http://magento.test.co/skin/frontend/argento/argento/images/victorystore.gif';
		 	$result= $this->sendEmail('Send mail to friend',$mail_from,$mail_to,$from_name,$comments);
        	
   	 	}
   	 	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	function sendEmail($template_code,$mail_from,$mail_to,$from_name,$comments)
	{
		$templateId = Mage::getModel('core/email_template')->loadByCode($template_code)->getId();
		$sender = Array('name' => $from_name,
		'email' => $mail_from);
		//recepient
		$mail_to=str_replace(" ", "",$mail_to);
		$mail_tos = explode(",", $mail_to);
		$email = $mail_tos[0];
		unset($mail_tos[0]);
		$emailName = $from_name;
		$linkImage=$this->getRequest()->getPost('linkImage');
        $vars = Array();
			$vars['content'] =$comments;
			$vars['name'] =$from_name;
			$this->loadLayout();
			$a=$this->getLayout()->getBlock('footer')->toHtml();//$this->getLayout()->getChildHtml('footer_links');
			$a=substr($a,strrpos($a,'<div class="footer-container">'));
			$a=substr($a,0,strrpos($a,'</div>'))."</div>";
			//$i=0;
			//$vars["aaaa_".$i++] = $a;
			$vars["logo"]=Mage::getStoreConfig('design/header/logo_src');
			$vars["head"]=$this->getLayout()->createBlock('cms/block')->setBlockId('header_callout')->toHtml();//$this->getLayout()->getBlock('header')->toHtml();
			$a=preg_replace('/class="footer-container"+/',"style='float:left;width:100%'",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/class="footer"+/',"style='float:left;width:100%'",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/<address>+/',"<address style='float:left;width:50%'>",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/<ul class="links">+/',"<div style='float:right;width:50%;text-align:right'>",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/<\/ul>+/',"</div>",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/<li+/',"<span",$a);
			//$vars["aaaa_".$i++] = $a;
			$a=preg_replace('/<\/li>+/',"</span>",$a);
			
			// $h = fopen("e:/log.txt", "a");
//fwrite($h, "\n". $a);
			//$vars["aaaa"] = $a;
   	 		$vars["foot"]=$this->getRequest()->getPost('price').$this->getLayout()->createBlock('cms/block')->setBlockId('footer_contacts')->toHtml().$a; 
   	 		$vars["img"]="<a href='".$this->getRequest()->getPost('link')."'><img src='".$linkImage["linkTemplate"][0]."' ></a><hr>";
   	 		for($i=0;$i<count($linkImage["linkTemplateFinal"]);$i++)
   	 		{
   	 			$vars["img"].="<a href='".$this->getRequest()->getPost('link')."'><img src='".$linkImage["linkTemplateFinal"][$i]."' alt='Image loading...' ></a><hr>";
   	 		}
   	 		//print_r($vars);
		$storeId = Mage::app()->getStore()->getId();
		$translate = Mage::getSingleton('core/translate');
		try{
			Mage::getModel('core/email_template')->addBcc($mail_tos)
			->sendTransactional($templateId, $sender, $email, $emailName, $vars, $storeId);
			$translate->setTranslateInline(true);
			return array('result'=>"success",'message'=>'ok');
		}	
		catch  (Exception $e) {
			return array('result'=>"error",'message'=>$e);
		}
		//$translate->setTranslateInline(true);
	}
}
?>