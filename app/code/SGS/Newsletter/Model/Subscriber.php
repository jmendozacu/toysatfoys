<?php
namespace SGS\Newsletter\Model;

class Subscriber extends \Magento\Newsletter\Model\Subscriber {

    /**
     * Initialize resource model
     *
     * @return void
     */


    public function subscribe($email, $subscriber_name = '', $subscriber_date_of_birth = '', $subscriber_country_code = '') {
      $this->loadByEmail($email);

      if (!$this->getId()) {
        $this->setSubscriberConfirmCode($this->randomSequence());
      }

      $isConfirmNeed = $this->_scopeConfig->getValue(
        self::XML_PATH_CONFIRMATION_FLAG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
      ) == 1 ? true : false;
      $isOwnSubscribes = false;

      $isSubscribeOwnEmail = $this->_customerSession->isLoggedIn() && $this->_customerSession->getCustomerDataObject()->getEmail() == $email;

      if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED || $this->getStatus() == self::STATUS_NOT_ACTIVE
    ) {
        if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
          $isOwnSubscribes = $isSubscribeOwnEmail;
          if ($isOwnSubscribes == true) {
            $this->setStatus(self::STATUS_SUBSCRIBED);
          } else {
            $this->setStatus(self::STATUS_NOT_ACTIVE);
          }
        } else {
          $this->setStatus(self::STATUS_SUBSCRIBED);
        }

        $this->setSubscriberEmail($_POST['email']);

      }

      $fname='';

      if ($isSubscribeOwnEmail) {
        try {
          $customer = $this->customerRepository->getById($this->_customerSession->getCustomerId());
          $fname=$customer->getFirstname();
          $this->setStoreId($customer->getStoreId());
          $this->setCustomerId($customer->getId());
        } catch (NoSuchEntityException $e) {
          $this->setStoreId($this->_storeManager->getStore()->getId());
          $this->setCustomerId(0);
        }
      } else {
        $this->setStoreId($this->_storeManager->getStore()->getId());
        $this->setCustomerId(0);
      }

      $this->setStatusChanged(true);

      try {

        $this->save();
          //Add to mailchimp
        $this->mc_subscribe($_POST['email'],$fname);

        if ($isConfirmNeed === true && $isOwnSubscribes === false
        ) {
          $this->sendConfirmationRequestEmail();
        } else {
          $this->sendConfirmationSuccessEmail();
        }
        return $this->getStatus();
      } catch (\Exception $e) {

        throw new \Exception($e->getMessage());
      }
    }

    
      //push data to mailchimp
    private function mc_subscribe($email, $fname) {
      $listid='0b86673ce0';
        //$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        //$helper_factory = $object_manager->get('\Ebizmarts\MailChimp\Helper\Data');

        $apikey='b6fbbf0c2a804abe75b8f72d084d8aa7-us16';//$helper_factory->getApiKey($this->_storeManager->getStore());
        $storecode = $this->_storeManager->getStore()->getCode();
        $auth = base64_encode( 'user:'.$apikey );
        $data = array(
          'apikey'        => $apikey,
          'email_address' => $email,
          'status'        => 'subscribed',
          'merge_fields'  => array(
            'FNAME' => $fname,
            'STORE'=>$storecode 
          )
        );
        $json_data = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://us16.api.mailchimp.com/3.0/lists/'.$listid.'/members/');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
          'Authorization: Basic '.$auth));
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_exec($ch);
        return;
      }


    }
