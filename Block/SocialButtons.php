<?php
namespace Ssquare\SocialLogin\Block;

use Magento\Framework\View\Element\Template;
use Ssquare\SocialLogin\Helper\Data;

class SocialButtons extends Template
{
    protected $helper;

    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getFacebookLoginUrl()
    {
        return $this->getUrl('sociallogin/account/facebooklogin');
    }

    public function getGoogleLoginUrl()
    {
        return $this->getUrl('sociallogin/account/googlelogin');
    }
}
