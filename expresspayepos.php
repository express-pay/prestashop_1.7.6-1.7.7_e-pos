<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
    exit;

class ExpressPayEpos extends PaymentModule
{

    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'expresspayepos';
        $this->tab = 'payments_gateways';
        $this->author = 'ООО "ТриИнком"';
        $this->version = '1.0';
        $this->controllers = array('redirect');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->currencies      = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;

        parent::__construct();

        $this->page = basename(__FILE__, '.php');

        $this->displayName      = $this->l('ExpressPay E-POS');
        $this->description      = $this->l('This module allows you to accepts E-POS payments');
        $this->confirmUninstall = $this->l('Are you sure you want to remove module ?');
    }

    // Установка модуля
    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        return parent::install() &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('paymentReturn') &&
            Configuration::updateValue('EXPRESSPAY_MODULE_NAME_EPOS', 'EXPRESSPAY_EPOS') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_TOKEN', '') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_SERVICE_ID', '') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_CODE', '') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_NOTIFICATION_URL', $this->context->link->getModuleLink($this->name, 'notification', [])) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND', false) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_SEND_SECRET_WORD', '') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE', false) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD', '') &&
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME', false) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS', false) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT', false) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_TESTING_MODE', true) &&
            Configuration::updateValue('EXPRESSPAY_EPOS_API_URL', "https://api.express-pay.by/v1/") &&
            Configuration::updateValue('EXPRESSPAY_EPOS_TEST_API_URL', "https://sandbox-api.express-pay.by/v1/");
    }

    // Удаление модуля
    public function uninstall()
    {
        return parent::uninstall() &&
            Configuration::deleteByName('EXPRESSPAY_MODULE_NAME_EPOS') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_TOKEN') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_SERVICE_ID') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_CODE') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_NOTIFICATION_URL') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_SEND_SECRET_WORD') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_TESTING_MODE') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_API_URL') &&
            Configuration::deleteByName('EXPRESSPAY_EPOS_TEST_API_URL');
    }

    // Сохранение значений из конфигурации
    public function getContent()
    {
        $this->log_info('getContent', 'start');

        $output = null;
        $check = true;

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->log_info('getContent', '!count($this->_postErrors)');

                $output .= $this->_postProcess();
            } else {
                $this->log_error('getContent', 'Post Errors; Errors - ' . implode($this->_postErrors));

                foreach ($this->_postErrors as $err) {
                    $output .= $this->displayError($err);
                }
            }
        }
        $this->log_info('getContent', ' Output- ' . $output);
        return $output . $this->displayForm();
    }

    protected function _postValidation()
    {
        if (!Tools::getValue('EXPRESSPAY_EPOS_TOKEN')) {
            $this->_postErrors[] = $this->trans('Token is empty', array(), 'Modules.ExpressPayEpos.Admin');
        } elseif (!Tools::getValue('EXPRESSPAY_EPOS_CODE')) {
            $this->_postErrors[] = $this->trans('E-POS code text is empty.', array(), "Modules.ExpressPayEpos.Admin");
        } elseif (!Tools::getValue('EXPRESSPAY_EPOS_SERVICE_ID')) {
            $this->_postErrors[] = $this->trans('Service Id text is empty.', array(), "Modules.ExpressPayEpos.Admin");
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EXPRESSPAY_EPOS_TOKEN', Tools::getValue('EXPRESSPAY_EPOS_TOKEN'));
            Configuration::updateValue('EXPRESSPAY_EPOS_SERVICE_ID', Tools::getValue('EXPRESSPAY_EPOS_SERVICE_ID'));
            Configuration::updateValue('EXPRESSPAY_EPOS_CODE', Tools::getValue('EXPRESSPAY_EPOS_CODE'));
            Configuration::updateValue('EXPRESSPAY_EPOS_NOTIFICATION_URL', Tools::getValue('EXPRESSPAY_EPOS_NOTIFICATION_URL'));
            Configuration::updateValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND', Tools::getValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND'));
            Configuration::updateValue('EXPRESSPAY_EPOS_SEND_SECRET_WORD', Tools::getValue('EXPRESSPAY_EPOS_SEND_SECRET_WORD'));
            Configuration::updateValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE', Tools::getValue('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE'));
            Configuration::updateValue('EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD', Tools::getValue('EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD'));
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME', Tools::getValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME'));
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS', Tools::getValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS'));
            Configuration::updateValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT', Tools::getValue('EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT'));
            Configuration::updateValue('EXPRESSPAY_EPOS_TESTING_MODE', Tools::getValue('EXPRESSPAY_EPOS_TESTING_MODE'));
            Configuration::updateValue('EXPRESSPAY_EPOS_API_URL', Tools::getValue('EXPRESSPAY_EPOS_API_URL'));
            Configuration::updateValue('EXPRESSPAY_EPOS_TEST_API_URL', Tools::getValue('EXPRESSPAY_EPOS_TEST_API_URL'));
        }
        return $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
    }

    // Форма страницы конфигурации
    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form[0]['form'] = array(

            'legend' => array(
                'title' => $this->l('ExpressPay E-POS Settings'),
                'icon' => 'icon-envelope'
            ),
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Token'),
                    'name' => 'EXPRESSPAY_EPOS_TOKEN',
                    'desc' => $this->l('Your token from express-pay.by website.'),
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Service ID'),
                    'name' => 'EXPRESSPAY_EPOS_SERVICE_ID',
                    'desc' => $this->l('Your service number from express-pay.by website.'),
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('E-POS Code'),
                    'name' => 'EXPRESSPAY_EPOS_CODE',
                    'desc' => $this->l('Your E-POS code from express-pay.by website.'),
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Notification URL'),
                    'name' => 'EXPRESSPAY_EPOS_NOTIFICATION_URL',
                    'desc' => $this->l('Copy this URL to \"URL for notification\" field on express-pay.by.'),
                    'readonly' => true,
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Digital signature for API'),
                    'name' => 'EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Secret word for bills signing'),
                    'name' => 'EXPRESSPAY_EPOS_SEND_SECRET_WORD'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Digital signature for notifications'),
                    'name' => 'EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Secret word for notifications'),
                    'name' => 'EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD'
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Allow to change payer name'),
                    'name' => 'EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Allow to change payer address'),
                    'name' => 'EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Allow to change pay amount'),
                    'name' => 'EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Use test mode'),
                    'name' => 'EXPRESSPAY_EPOS_TESTING_MODE',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Yes')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('No')
                        ]
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('API URL'),
                    'name' => 'EXPRESSPAY_EPOS_API_URL'
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Test API URL'),
                    'name' => 'EXPRESSPAY_EPOS_TEST_API_URL'
                ]
            ],

            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name; //AdminController::$currentIndex . '&configure=' . $this->name;

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'btnSubmit';

        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token=' . Tools::getAdminTokenLite('AdminModules')
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $helper->fields_value['EXPRESSPAY_EPOS_TOKEN']                    = Configuration::get('EXPRESSPAY_EPOS_TOKEN');
        $helper->fields_value['EXPRESSPAY_EPOS_SERVICE_ID']               = Configuration::get('EXPRESSPAY_EPOS_SERVICE_ID');
        $helper->fields_value['EXPRESSPAY_EPOS_CODE']                     = Configuration::get('EXPRESSPAY_EPOS_CODE');
        $helper->fields_value['EXPRESSPAY_EPOS_NOTIFICATION_URL']         = Configuration::get('EXPRESSPAY_EPOS_NOTIFICATION_URL');
        $helper->fields_value['EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND']    = Configuration::get('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND');
        $helper->fields_value['EXPRESSPAY_EPOS_SEND_SECRET_WORD']         = Configuration::get('EXPRESSPAY_EPOS_SEND_SECRET_WORD');
        $helper->fields_value['EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE'] = Configuration::get('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_RECEIVE');
        $helper->fields_value['EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD']      = Configuration::get('EXPRESSPAY_EPOS_RECEIVE_SECRET_WORD');
        $helper->fields_value['EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME']        = Configuration::get('EXPRESSPAY_EPOS_ALLOW_CHANGE_NAME');
        $helper->fields_value['EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS']     = Configuration::get('EXPRESSPAY_EPOS_ALLOW_CHANGE_ADDRESS');
        $helper->fields_value['EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT']      = Configuration::get('EXPRESSPAY_EPOS_ALLOW_CHANGE_AMOUNT');
        $helper->fields_value['EXPRESSPAY_EPOS_TESTING_MODE']             = Configuration::get('EXPRESSPAY_EPOS_TESTING_MODE');
        $helper->fields_value['EXPRESSPAY_EPOS_API_URL']                  = Configuration::get('EXPRESSPAY_EPOS_API_URL');
        $helper->fields_value['EXPRESSPAY_EPOS_TEST_API_URL']             = Configuration::get('EXPRESSPAY_EPOS_TEST_API_URL');

        $html = $this->_displayInfo();
        $html .= $helper->generateForm($fields_form);
        return $html;
    }

    private function _displayInfo()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    function sendRequestGET($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // Хук оплаты
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $newOption = new PaymentOption();
        $newOption->setCallToActionText($this->l('ExpressPay E-POS'))
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        $state = $params['order']->getCurrentState();

        $eposCode = Configuration::get("EXPRESSPAY_EPOS_CODE");
        $orderId =  $eposCode . '-1-'. $params['order']->id;
        $amount = Tools::displayPrice($params['order']->total_paid);
        $qr_code = $this->getQrCode($_REQUEST['ExpressPayInvoiceNo'], Configuration::get('EXPRESSPAY_EPOS_SEND_SECRET_WORD'), Configuration::get('EXPRESSPAY_EPOS_TOKEN'));
        $qr_description = 'Отсканируйте QR-код для оплаты';
       // $this->log_info('initContent', 'Qr_Code_BODY - ' . $qr_code);

        if ($state == _PS_OS_PREPARATION_) {
            $this->smarty->assign(array(
                'status' => 'fail'
            ));
        } else {
            $this->smarty->assign(array(
                'epos_code' => $eposCode,
                'order_id' => $orderId,
                'amount' => $amount,
                'qr_code' => $qr_code,
                'qr_description' => $qr_description,
                'status' => 'ok'
            ));
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    	//Получение Qr-кода
	public function getQrCode($ExpressPayInvoiceNo, $secretWord, $token)
	{
		$request_params_for_qr = array(
			"Token" => $token,
			"InvoiceId" => $ExpressPayInvoiceNo,
			'ViewType' => 'base64'
		);

        if (Configuration::get('EXPRESSPAY_EPOS_USE_DIGITAL_SIGN_SEND')){
            $request_params_for_qr["Signature"] = $this->compute_signature($request_params_for_qr, $secretWord, $token, 'get_qr_code');
        }

		$request_params_for_qr  = http_build_query($request_params_for_qr);
		$response_qr = $this->sendRequestGET('https://api.express-pay.by/v1/qrcode/getqrcode/?' . $request_params_for_qr);
		$response_qr = json_decode($response_qr);
		$qr_code = $response_qr->QrCodeBody;
		return $qr_code;
	}

    	//Вычисление цифровой подписи
	public function compute_signature($request_params, $secret_word, $token, $method = 'add_invoice')
	{
		$secret_word = trim($secret_word);
		$normalized_params = array_change_key_case($request_params, CASE_LOWER);
		$api_method = array(
			'get_qr_code' => array(
				"invoiceid",
				"viewtype",
				"imagewidth",
				"imageheight"
			),
		);

		$result =  $token;

		foreach ($api_method[$method] as $item)
			$result .= (isset($normalized_params[$item])) ? $normalized_params[$item] : '';

		$hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

		return $hash;
	}

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

    public function log_error_exception($name, $message, $e)
    {
        $this->log($name, "ERROR", $message . '; EXCEPTION MESSAGE - ' . $e->getMessage() . '; EXCEPTION TRACE - ' . $e->getTraceAsString());
    }

    public function log_error($name, $message)
    {
        $this->log($name, "ERROR", $message);
    }

    public function log_info($name, $message)
    {
        $this->log($name, "INFO", $message);
    }

    public function log($name, $type, $message)
    {
        $log_url = dirname(__FILE__) . '/Log';

        if (!file_exists($log_url)) {
            $is_created = mkdir($log_url, 0777);

            if (!$is_created)
                return;
        }

        $log_url .= '/express-pay-' . date('Y.m.d') . '.log';

        file_put_contents($log_url, $type . " - IP - " . $_SERVER['REMOTE_ADDR'] . "; DATETIME - " . date('c') . "; USER AGENT - " . $_SERVER['HTTP_USER_AGENT'] . "; FUNCTION - " . $name . "; MESSAGE - " . $message . ';' . PHP_EOL, FILE_APPEND);
    }
}
