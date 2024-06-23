<?php
namespace Ssquare\SocialLogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Ssquare\SocialLogin\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class FacebookCallback extends Action
{
    protected $helper;
    protected $customerFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        Data $helper,
        CustomerFactory $customerFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $code = $this->getRequest()->getParam('code');
        $facebookAppId = $this->helper->getFacebookAppId();
        $facebookAppSecret = $this->helper->getFacebookAppSecret();
        $callbackUrl = $this->_url->getUrl('sociallogin/account/facebookcallback');

        if ($code) {
            $tokenUrl = 'https://graph.facebook.com/v2.10/oauth/access_token?client_id=' . $facebookAppId . '&redirect_uri=' . urlencode($callbackUrl) . '&client_secret=' . $facebookAppSecret . '&code=' . $code;
            $tokenResponse = file_get_contents($tokenUrl);
            $params = json_decode($tokenResponse, true);

            if (isset($params['access_token'])) {
                $graphUrl = 'https://graph.facebook.com/me?access_token=' . $params['access_token'] . '&fields=id,name,email';
                $user = json_decode(file_get_contents($graphUrl), true);

                if (isset($user['email'])) {
                    $this->_loginOrCreateCustomer($user);
                }
            }
        }

        $this->_redirect('/');
    }

    protected function _loginOrCreateCustomer($userData)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($this->customerSession->getWebsiteId());
        $customer->loadByEmail($userData['email']);

        if (!$customer->getId()) {
            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['name']);
            $customer->setLastname($userData['name']);
            $customer->setPassword($customer->encryptPassword($customer->generatePassword()));

            try {
                $customer->save();
                $customer->sendNewAccountEmail();
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return;
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t process your request right now.'));
                return;
            }
        }

        $this->customerSession->setCustomerAsLoggedIn($customer);
    }
}
