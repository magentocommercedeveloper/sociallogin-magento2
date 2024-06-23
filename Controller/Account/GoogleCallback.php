<?php
namespace Ssquare\SocialLogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\CustomerFactory;
use Ssquare\SocialLogin\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class GoogleCallback extends Action
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
        $googleClientId = $this->helper->getGoogleClientId();
        $googleClientSecret = $this->helper->getGoogleClientSecret();
        $callbackUrl = $this->_url->getUrl('sociallogin/account/googlecallback');

        if ($code) {
            $tokenUrl = 'https://oauth2.googleapis.com/token';
            $tokenData = [
                'code' => $code,
                'client_id' => $googleClientId,
                'client_secret' => $googleClientSecret,
                'redirect_uri' => $callbackUrl,
                'grant_type' => 'authorization_code',
            ];

            $tokenResponse = $this->_post($tokenUrl, $tokenData);
            $params = json_decode($tokenResponse, true);

            if (isset($params['access_token'])) {
                $graphUrl = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $params['access_token'];
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

    private function _post($url, $data)
    {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
}
