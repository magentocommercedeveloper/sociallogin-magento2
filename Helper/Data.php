<?php
namespace Ssquare\SocialLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SOCIALLOGIN = 'sociallogin/settings/';

    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SOCIALLOGIN . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFacebookAppId()
    {
        return $this->getConfigValue('facebook_app_id');
    }

    public function getFacebookAppSecret()
    {
        return $this->getConfigValue('facebook_app_secret');
    }

    public function getGoogleClientId()
    {
        return $this->getConfigValue('google_client_id');
    }

    public function getGoogleClientSecret()
    {
        return $this->getConfigValue('google_client_secret');
    }
}
