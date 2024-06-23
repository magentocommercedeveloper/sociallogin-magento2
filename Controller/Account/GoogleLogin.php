<?php
namespace Ssquare\SocialLogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ssquare\SocialLogin\Helper\Data;

class GoogleLogin extends Action
{
    protected $helper;

    public function __construct(Context $context, Data $helper)
    {
        parent::__construct($context);
        $this->helper = $helper;
    }

    public function execute()
    {
        $googleClientId = $this->helper->getGoogleClientId();
        $callbackUrl = $this->_url->getUrl('sociallogin/account/googlecallback');
        
        $url = 'https://accounts.google.com/o/oauth2/auth?client_id=' . $googleClientId . '&redirect_uri=' . urlencode($callbackUrl) . '&response_type=code&scope=email';

        $this->_redirect($url);
    }
}
