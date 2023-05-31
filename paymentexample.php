<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class PaymentExample extends PaymentModule
{
    // >>>> Main settings <<<<
    public function isUsingNewTranslationSystem() {
		return true;
	}

    protected $_html = '';
    protected $_postErrors = [];

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'paymentexample';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => '8.99.99'];
        $this->author = 'PrestaShop';
        $this->controllers = ['validation'];
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Payment Example', [], 'Modules.Paymentexample.Admin');
        $this->description = $this->trans('Description of Payment Example', [], 'Modules.Paymentexample.Admin');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->trans('No currency has been set for this module.', [], 'Modules.Paymentexample.Admin');
        }
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
			$this->_errors[] = $this->trans('You have to enable the cURL extension on your server to install this module.', [], 'Modules.Paymentexample.Admin');
			return false;
		}

		return parent::install()
			&& $this->registerHook('paymentOptions')
			&& $this->registerHook('paymentReturn')
		;
    }

    public function uninstall()
	{
		return parent::uninstall();
	}

    // public function getContent()
    // {
    //     $p = new PrestaShop\Module\PaymentExample\PaymentOptions();
    //     $p::test();
    // }

    // >>>> END Main settings <<<<

	// >>>> Hooks <<<<
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getOfflinePaymentOption(),
            $this->getExternalPaymentOption(),
            $this->getEmbeddedPaymentOption(),
            $this->getIframePaymentOption(),
        ];

        return $payment_options;
    }

    public function hookPaymentReturn()
    {
        //
    }
    // >>>> END Hooks <<<<

    // >>>> Internal functionallity <<<<
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }
    // >>>> END Internal functionallity <<<<
}
