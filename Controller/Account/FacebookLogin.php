<?php
namespace Ssquare\SocialLogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ssquare\SocialLogin\Helper\Data;

class FacebookLogin extends Action
{
    protected $helper;

    public function __construct(Context $context, Data $helper)
    {
        parent::__construct($context);
        $this->helper = $helper;
    }

    public function execute()
    {
        $facebookAppId = $this->helper->getFacebookAppId();
        $callbackUrl = $this->_url->getUrl('sociallogin/account/facebookcallback');
        
        $url = 'https://www.facebook.com/v2.10/dialog/oauth?client_id=' . $facebookAppId . '&redirect_uri=' . urlencode($callbackUrl) . '&scope=email';

        $this->_redirect($url);
    }
}
