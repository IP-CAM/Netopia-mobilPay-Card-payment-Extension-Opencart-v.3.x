<?php
class ControllerExtensionPaymentMobilpay extends Controller {
    private $error = array();
    protected $_domain;
    protected $outEnvKey;
    protected $outEncData;
    protected $jsonData;
    protected $encData;
    const ERROR_LOAD_X509_CERTIFICATE = 0x10000001;
    const ERROR_ENCRYPT_DATA          = 0x10000002;


    public function index() {
        // get User Token
        $data['user_token'] = $this->session->data['user_token'];

        $this->load->language('extension/payment/mobilpay');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_mobilpay', array_merge($this->request->post));
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        //die(print_r($this->request->files));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['signature'])) {
            $data['error_signature'] = $this->error['signature'];
        } else {
            $data['error_signature'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/payment/mobilpay', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/mobilpay', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);


        if (isset($this->request->post['payment_mobilpay_signature'])) {
            $data['payment_mobilpay_signature'] = $this->request->post['payment_mobilpay_signature'];
        } else {
            $data['payment_mobilpay_signature'] = $this->config->get('payment_mobilpay_signature');
        }

        if (isset($this->request->post['payment_mobilpay_total'])) {
            $data['payment_mobilpay_total'] = $this->request->post['payment_mobilpay_total'];
        } else {
            $data['payment_mobilpay_total'] = $this->config->get('payment_mobilpay_total');
        }

        if (isset($this->request->post['payment_mobilpay_test'])) {
            $data['payment_mobilpay_test'] = $this->request->post['payment_mobilpay_test'];
        } else {
            $data['payment_mobilpay_test'] = $this->config->get('payment_mobilpay_test');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_id'])) {
            $data['payment_mobilpay_order_status_id'] = $this->request->post['payment_mobilpay_order_status_id'];
        } else {
            $data['payment_mobilpay_order_status_id'] = $this->config->get('payment_mobilpay_order_status_id');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_confirmed_pending_id'])) {
            $data['payment_mobilpay_order_status_confirmed_pending_id'] = $this->request->post['payment_mobilpay_order_status_confirmed_pending_id'];
        } else {
            $data['payment_mobilpay_order_status_confirmed_pending_id'] = $this->config->get('payment_mobilpay_order_status_confirmed_pending_id');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_paid_pending_id'])) {
            $data['payment_mobilpay_order_status_paid_pending_id'] = $this->request->post['payment_mobilpay_order_status_paid_pending_id'];
        } else {
            $data['payment_mobilpay_order_status_paid_pending_id'] = $this->config->get('payment_mobilpay_order_status_paid_pending_id');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_paid_id'])) {
            $data['payment_mobilpay_order_status_paid_id'] = $this->request->post['payment_mobilpay_order_status_paid_id'];
        } else {
            $data['payment_mobilpay_order_status_paid_id'] = $this->config->get('payment_mobilpay_order_status_paid_id');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_canceled_id'])) {
            $data['payment_mobilpay_order_status_canceled_id'] = $this->request->post['payment_mobilpay_order_status_canceled_id'];
        } else {
            $data['payment_mobilpay_order_status_canceled_id'] = $this->config->get('payment_mobilpay_order_status_canceled_id');
        }

        if (isset($this->request->post['payment_mobilpay_order_status_credit_id'])) {
            $data['payment_mobilpay_order_status_credit_id'] = $this->request->post['payment_mobilpay_order_status_credit_id'];
        } else {
            $data['payment_mobilpay_order_status_credit_id'] = $this->config->get('payment_mobilpay_order_status_credit_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_mobilpay_geo_zone_id'])) {
            $data['payment_mobilpay_geo_zone_id'] = $this->request->post['payment_mobilpay_geo_zone_id'];
        } else {
            $data['payment_mobilpay_geo_zone_id'] = $this->config->get('payment_mobilpay_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_mobilpay_status'])) {
            $data['payment_mobilpay_status'] = $this->request->post['payment_mobilpay_status'];
        } else {
            $data['payment_mobilpay_status'] = $this->config->get('payment_mobilpay_status');
        }

        if (isset($this->request->post['payment_mobilpay_sort_order'])) {
            $data['payment_mobilpay_sort_order'] = $this->request->post['payment_mobilpay_sort_order'];
        } else {
            $data['payment_mobilpay_sort_order'] = $this->config->get('payment_mobilpay_sort_order');
        }

        if (isset($this->request->post['payment_mobilpay_sand_pri_key'])) {
            $data['payment_mobilpay_sand_pri_key'] = $this->request->post['payment_mobilpay_sand_pri_key'];
        } else {
            $data['payment_mobilpay_sand_pri_key'] = $this->config->get('payment_mobilpay_sand_pri_key');
        }

        if (isset($this->request->post['payment_mobilpay_sand_pub_key'])) {
            $data['payment_mobilpay_sand_pub_key'] = $this->request->post['payment_mobilpay_sand_pub_key'];
        } else {
            $data['payment_mobilpay_sand_pub_key'] = $this->config->get('payment_mobilpay_sand_pub_key');
        }

        if (isset($this->request->post['payment_mobilpay_live_pri_key'])) {
            $data['payment_mobilpay_live_pri_key'] = $this->request->post['payment_mobilpay_live_pri_key'];
        } else {
            $data['payment_mobilpay_live_pri_key'] = $this->config->get('payment_mobilpay_live_pri_key');
        }

        if (isset($this->request->post['payment_mobilpay_live_pub_key'])) {
            $data['payment_mobilpay_live_pub_key'] = $this->request->post['payment_mobilpay_live_pub_key'];
        } else {
            $data['payment_mobilpay_live_pub_key'] = $this->config->get('payment_mobilpay_live_pub_key');
        }

        if (!empty($this->request->files['payment_mobilpay_live_pub_key_file']["name"])) {
            $data['payment_mobilpay_live_pub_key_file'] = $this->request->files['payment_mobilpay_live_pub_key_file']["name"];
        } else {
            $data['payment_mobilpay_live_pub_key_file'] = $this->config->get('payment_mobilpay_live_pub_key_file');
        }
        if (isset($this->error['payment_mobilpay_live_pub_key_file'])) {
            $data['error_payment_mobilpay_live_pub_key_file'] = $this->error['payment_mobilpay_live_pub_key_file'];
        } else {
            $data['error_payment_mobilpay_live_pub_key_file'] = '';
        }


        if (!empty($this->request->files['payment_mobilpay_sand_pub_key_file']["name"])) {
            $data['payment_mobilpay_sand_pub_key_file'] = $this->request->files['payment_mobilpay_sand_pub_key_file']["name"];
        } else {
            $data['payment_mobilpay_sand_pub_key_file'] = $this->config->get('payment_mobilpay_sand_pub_key_file');
        }
        if (isset($this->error['payment_mobilpay_sand_pub_key_file'])) {
            $data['error_payment_mobilpay_sand_pub_key_file'] = $this->error['payment_mobilpay_sand_pub_key_file'];
        } else {
            $data['error_payment_mobilpay_sand_pub_key_file'] = '';
        }

        if (!empty($this->request->files['payment_mobilpay_live_pri_key_file']["name"])) {
            $data['payment_mobilpay_live_pri_key_file'] = $this->request->files['payment_mobilpay_live_pri_key_file']["name"];
        } else {
            $data['payment_mobilpay_live_pri_key_file'] = $this->config->get('payment_mobilpay_live_pri_key_file');
        }
        if (isset($this->error['payment_mobilpay_live_pri_key_file'])) {
            $data['error_payment_mobilpay_live_pri_key_file'] = $this->error['payment_mobilpay_live_pri_key_file'];
        } else {
            $data['error_payment_mobilpay_live_pri_key_file'] = '';
        }
        if (!empty($this->request->files['payment_mobilpay_sand_pri_key_file']["name"])) {
            $data['payment_mobilpay_sand_pri_key_file'] = $this->request->files['payment_mobilpay_sand_pri_key_file']["name"];
        } else {
            $data['payment_mobilpay_sand_pri_key_file'] = $this->config->get('payment_mobilpay_sand_pri_key_file');
        }
        if (isset($this->error['payment_mobilpay_sand_pri_key_file'])) {
            $data['error_payment_mobilpay_sand_pri_key_file'] = $this->error['payment_mobilpay_sand_pri_key_file'];
        } else {
            $data['error_payment_mobilpay_sand_pri_key_file'] = '';
        }

        if (isset($this->request->post['payment_mobilpay_conditions_complete_description'])) {
            $data['payment_mobilpay_conditions_complete_description'] = $this->request->post['payment_mobilpay_conditions_complete_description'];
        } else {
            $data['payment_mobilpay_conditions_complete_description'] = $this->config->get('payment_mobilpay_conditions_complete_description');
        }

        if (isset($this->request->post['payment_mobilpay_conditions_prices_currency'])) {
            $data['payment_mobilpay_conditions_prices_currency'] = $this->request->post['payment_mobilpay_conditions_prices_currency'];
        } else {
            $data['payment_mobilpay_conditions_prices_currency'] = $this->config->get('payment_mobilpay_conditions_prices_currency');
        }

        if (isset($this->request->post['payment_mobilpay_conditions_has_ssl'])) {
            $data['payment_mobilpay_conditions_has_ssl'] = $this->request->post['payment_mobilpay_conditions_has_ssl'];
        } else {
            $data['payment_mobilpay_conditions_has_ssl'] = $this->config->get('payment_mobilpay_conditions_has_ssl');
        }

        if (isset($this->request->post['payment_mobilpay_conditions_forbidden_business'])) {
            $data['payment_mobilpay_conditions_forbidden_business'] = $this->request->post['payment_mobilpay_conditions_forbidden_business'];
        } else {
            $data['payment_mobilpay_conditions_forbidden_business'] = $this->config->get('payment_mobilpay_conditions_forbidden_business');
        }

        if (isset($this->request->post['payment_mobilpay_clarity_contact'])) {
            $data['payment_mobilpay_clarity_contact'] = $this->request->post['payment_mobilpay_clarity_contact'];
        } else {
            $data['payment_mobilpay_clarity_contact'] = $this->config->get('payment_mobilpay_clarity_contact');
        }

        if (isset($this->request->post['payment_mobilpay_terms_conditions_url'])) {
            $data['payment_mobilpay_terms_conditions_url'] = $this->request->post['payment_mobilpay_terms_conditions_url'];
        } else {
            $data['payment_mobilpay_terms_conditions_url'] = $this->config->get('payment_mobilpay_terms_conditions_url');
        }

        if (isset($this->request->post['payment_mobilpay_privacy_policy_url'])) {
            $data['payment_mobilpay_privacy_policy_url'] = $this->request->post['payment_mobilpay_privacy_policy_url'];
        } else {
            $data['payment_mobilpay_privacy_policy_url'] = $this->config->get('payment_mobilpay_privacy_policy_url');
        }

        if (isset($this->request->post['payment_mobilpay_delivery_policy_url'])) {
            $data['payment_mobilpay_delivery_policy_url'] = $this->request->post['payment_mobilpay_delivery_policy_url'];
        } else {
            $data['payment_mobilpay_delivery_policy_url'] = $this->config->get('payment_mobilpay_delivery_policy_url');
        }

        if (isset($this->request->post['payment_mobilpay_return_cancel_policy_url'])) {
            $data['payment_mobilpay_return_cancel_policy_url'] = $this->request->post['payment_mobilpay_return_cancel_policy_url'];
        } else {
            $data['payment_mobilpay_return_cancel_policy_url'] = $this->config->get('payment_mobilpay_return_cancel_policy_url');
        }

        if (isset($this->request->post['payment_mobilpay_gdpr_policy_url'])) {
            $data['payment_mobilpay_gdpr_policy_url'] = $this->request->post['payment_mobilpay_gdpr_policy_url'];
        } else {
            $data['payment_mobilpay_gdpr_policy_url'] = $this->config->get('payment_mobilpay_gdpr_policy_url');
        }

        if (isset($this->request->post['payment_mobilpay_image_visa_logo_link'])) {
            $data['payment_mobilpay_image_visa_logo_link'] = $this->request->post['payment_mobilpay_image_visa_logo_link'];
        } else {
            $data['payment_mobilpay_image_visa_logo_link'] = $this->config->get('payment_mobilpay_image_visa_logo_link');
        }

        if (isset($this->request->post['payment_mobilpay_image_master_logo_link'])) {
            $data['payment_mobilpay_image_master_logo_link'] = $this->request->post['payment_mobilpay_image_master_logo_link'];
        } else {
            $data['payment_mobilpay_image_master_logo_link'] = $this->config->get('payment_mobilpay_image_master_logo_link');
        }

        if (isset($this->request->post['payment_mobilpay_image_netopia_logo_link'])) {
            $data['payment_mobilpay_image_netopia_logo_link'] = $this->request->post['payment_mobilpay_image_netopia_logo_link'];
        } else {
            $data['payment_mobilpay_image_netopia_logo_link'] = $this->config->get('payment_mobilpay_image_netopia_logo_link');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/mobilpay', $data));
    }

    protected function uploadFile($file,$allowTypes,$fieldName)
    {
        // File path config
        $targetFilePath = $this->_getUploadDir() . basename($file["name"]);
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        // Allow certain file formats
        if(!in_array($fileType, $allowTypes)) {
            $this->error[$fieldName] = $this->language->get('error_type');
        } else if($file['size'] <= 0 ) {
            $this->error[$fieldName] = $this->language->get('error_size');
        } else {
            if (!move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                $this->error[$fieldName] = $this->language->get('error_file_upload');
            }
        }
    }


    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/mobilpay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_mobilpay_signature']) {
            $this->error['signature'] = $this->language->get('error_signature');
        }
        if(!empty($this->request->files['payment_mobilpay_live_pub_key_file']['name'])) {
            $this->uploadFile($this->request->files['payment_mobilpay_live_pub_key_file'],['cer'],'payment_mobilpay_live_pub_key_file');
        }

        if(!empty($this->request->files['payment_mobilpay_live_pri_key_file']['name'])) {
            $this->uploadFile($this->request->files['payment_mobilpay_live_pri_key_file'],['key'],'payment_mobilpay_live_pri_key_file');
        }
        if(!empty($this->request->files['payment_mobilpay_sand_pub_key_file']['name'])) {
            $this->uploadFile($this->request->files['payment_mobilpay_sand_pub_key_file'],['cer'],'payment_mobilpay_sand_pub_key_file');
        }

        if(!empty($this->request->files['payment_mobilpay_sand_pri_key_file']['name'])) {
            $this->uploadFile($this->request->files['payment_mobilpay_sand_pri_key_file'],['key'],'payment_mobilpay_sand_pri_key_file');
        }

        return !$this->error;
    }

    protected function getDomain() {
//	    $this->_domain = 'https://'.$_SERVER['HTTP_HOST'].DIRECTORY_SEPARATOR; // Temporary Use HTTP
        $this->_domain = 'http://'.$_SERVER['HTTP_HOST'].DIRECTORY_SEPARATOR;
    }

    protected function hasDeclarations() {
        $isValid = 1;
        $declarations = array (
            'completeDescription' => $this->request->post['payment_mobilpay_conditions_complete_description'],
            'priceCurrency'       => $this->request->post['payment_mobilpay_conditions_prices_currency'],
            'contactInfo'         => $this->request->post['payment_mobilpay_clarity_contact'],
            'forbiddenBusiness'   => $this->request->post['payment_mobilpay_conditions_forbidden_business']
        );
        foreach ($declarations as $key => $value) {
            if($value !== 'yes'){
                $isValid = 0;
                $error[$key] = $key . " , is not valid!";
            }
        }
        return array(
            'status' => $isValid,
            'error'  => isset($error) ? $error: '',
        );
    }

    protected function hasMandatoryPages() {
        $isValid = 1;
        $mandatoryPages = array(
            'termsAndConditions'    => $this->request->post['payment_mobilpay_terms_conditions_url'],
            'privacyPolicy'         => $this->request->post['payment_mobilpay_privacy_policy_url'],
            'deliveryPolicy'        => $this->request->post['payment_mobilpay_delivery_policy_url'],
            'returnAndCancelPolicy' => $this->request->post['payment_mobilpay_return_cancel_policy_url'],
            'gdprPolicy'            => $this->request->post['payment_mobilpay_gdpr_policy_url']
        );
        $pages = [];
        foreach ($mandatoryPages as $key => $value) {
            $checkUrl = $this->checkUrlValidation($value);
            if(!$checkUrl['status']){
                $isValid = 0;
                $error[$key]           = $value . " , is not valid URL!";
            }
            $pages[$key]['status'] = $checkUrl['status'];
            $pages[$key]['code']   = $checkUrl['code'];
        }

        return array(
            'status' => $isValid,
            'result' => $pages,
            'error'  => isset($error) ? $error: '',
        );
    }

    public function checkImageValidation ($image) {
        $exist = 1;
        $imagePath = $_SERVER['SERVER_NAME'].'/'.$image;
        //die(var_dump($imagePath));
        if(!file_exists($imagePath)) {
            $exist = 0;
        }
        return $exist;
    }

    protected function hasMandatoryImages() {
        $isValid = 1;
        $MandatoryImages = array(
            'visaLogoLink'    => $this->request->post['payment_mobilpay_image_visa_logo_link'],
            'masterLogoLink'  => $this->request->post['payment_mobilpay_image_master_logo_link'],
            'netopiaLogoLink' => $this->request->post['payment_mobilpay_image_netopia_logo_link']
        );
        $img = [];
        foreach ($MandatoryImages as $key => $value) {
            $checkImage = $this->checkImageValidation($value);
            if(!$checkImage){
                $isValid = 0;
                $error[$key] = $value . " , is not valid image!";
            }
            $img[$key]['status'] = $checkImage;
        }

        return array(
            'status' => $isValid,
            'result' => $img,
            'error'  => isset($error) ? $error: '',
        );
    }

    protected function checkUrlValidation($url) {
        $isValid = false;
        $code = null;
        $url= $this->_domain.$url;
        if(isset($url) && is_string($url) && preg_match('/^http(s)?:\/\/[a-z0-9-]+(\.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url)){
            $ch = curl_init($url);
            if($ch !== false) {
                curl_setopt($ch, CURLOPT_HEADER         ,true);
                curl_setopt($ch, CURLOPT_NOBODY         ,true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
                curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
                curl_setopt($ch, CURLOPT_TIMEOUT        ,6);
                curl_exec($ch);
                if(!curl_errno($ch)) {
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $redirectNumber = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
                    if($code == 200 && $redirectNumber==0) {$isValid = true;}
                }
            }
        }

        return array(
            'status' =>  $isValid,
            'code'   => $code
        );
    }

    protected function hasSsl() {
        $isValid = 1;
//        TEMPORRY DOMAIN NAME
        $domain = 'https://netopia-payments.com'.DIRECTORY_SEPARATOR; // TEMPORARY to Test SSL
//      $domain = 'http://mtyplast.com'.DIRECTORY_SEPARATOR; // TEMPORARY to Test SSL
//      $domain = 'http://mtypdfgddfgdfgdfgdfglast.com'.DIRECTORY_SEPARATOR; // TEMPORARY to Test SSL
//      $domain = $this->_domain; // Main Domain

        $stream = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
        if($read = @fopen($domain, "rb", false, $stream)) {
            $cont = stream_context_get_params($read);
            if(array_key_exists('peer_certificate', $cont["options"]["ssl"])){
                $var = $cont["options"]["ssl"]["peer_certificate"];
                $result = (!is_null($var)) ? 1 : 0;
            } else{
                $isValid = 0;
                $result = 0;
                $error['hasSSL'] = $this->_domain . ", doesn't have valid SSL Certification";
            }
        } else {
            $isValid = 0;
            $result = 0;
            $error['hasSSL']= $this->_domain . ", is not a valid domain";
        }

        return array(
            'status' => $isValid,
            'result' => $result,
            'error'  => isset($error) ? $error: '',
        );
    }

    public function selfValidation() {
        $this->getDomain();
        $declareValidation  = $this->hasDeclarations();
        $pagesVerification  = $this->hasMandatoryPages();
        $imgVerification    = $this->hasMandatoryImages();
        $sslVerification    = $this->hasSsl();

        $validateResult = array(
            'declare' => $declareValidation,
            'urls'    => $pagesVerification,
            'images'  => $imgVerification,
            'ssl'     => $sslVerification
        );

        $isValid = true;
        foreach ($validateResult as $key => $value) {
            if($value['status'] != 1) {
                $isValid = false;
            }
        }


        if($isValid) {
            $jsonResponse = array(
                'status'  =>  true,
                'msg'     => 'All fields are valid! Please click on button "Send request to NETOPIA Payments to verify" to continue',
                'details' => $validateResult);
        } else {
            $jsonResponse = array(
                'result'  =>  false,
                'msg'     => "There is still problem on Validation\n. ",
                'result' => $validateResult );
        }
        /*
        * Send response to JS
        */
        echo (json_encode($jsonResponse));
    }

    public function sendValidation() {
        $ntpDeclare = array (
            'completeDescription' => (bool) $this->config->get('payment_mobilpay_conditions_complete_description') == "yes" ? true : false,
            'priceCurrency'       => (bool) $this->config->get('payment_mobilpay_conditions_prices_currency') == "yes" ? true : false,
            'contactInfo'         =>  (bool) $this->config->get('payment_mobilpay_clarity_contact') == "yes" ? true : false,
            'forbiddenBusiness'   =>  (bool) $this->config->get('payment_mobilpay_conditions_forbidden_business') == "yes" ? true : false
        );

        $ntpUrl = array(
            'termsAndConditions'    => $this->config->get('payment_mobilpay_terms_conditions_url'),
            'privacyPolicy'         => $this->config->get('payment_mobilpay_privacy_policy_url'),
            'deliveryPolicy'        => $this->config->get('payment_mobilpay_delivery_policy_url'),
            'returnAndCancelPolicy' => $this->config->get('payment_mobilpay_return_cancel_policy_url'),
            'gdprPolicy'            => $this->config->get('payment_mobilpay_gdpr_policy_url')
        );

        $ntpImg = array(
            //'visaLogoLink' => $this->config->get('payment_mobilpay_image_visa_logo_link'),
            //'masterLogoLink' => $this->config->get('payment_mobilpay_image_master_logo_link'),
            //'netopiaLogoLink' => $this->config->get('payment_mobilpay_image_netopia_logo_link')
            'netopiaLogo' => false
        );

        $this->jsonData = $this->makeActivateJson($ntpDeclare, $ntpUrl, $ntpImg);

        die(var_dump($this->jsonData));

        if(is_null($encResult = $this->encrypt())) {
            $this->encData = array(
                'env_key' => $this->getEnvKey(),
                'data'    => $this->getEncData()
            );

            //die(var_dump($this->encData));

            $result = json_decode($this->sendJsonCurl());
            // die(var_dump($result));

            if($result) {
                $response = array(
                    'status' =>  true,
                    'msg' => 'succesfully sent your request' );
            } else {
                $response = array(
                    'status' =>  false,
                    'msg' => 'Error, after send data!! ');
            }
        } else {
            $response = array(
                'status' =>  false,
                'msg' => $encResult );
        }
        echo (json_encode($response));


    }

    public function getEnvKey()
    {
        return $this->outEnvKey;
    }

    public function getEncData()
    {
        return $this->outEncData;
    }

    public function encrypt()
    {
        $errorMessage = null;
        if($this->config->get('payment_mobilpay_test') === 'sandbox') {
            $pubFile =  $this->config->get('payment_mobilpay_sand_pub_key');
        } else {
            $pubFile =  $this->config->get('payment_mobilpay_live_pub_key');
        }
        // die(print_r($pubFile));
        // $x509FilePath = $this->_getUploadDir().$pubFile['name'];
        $x509FilePath = $this->_getUploadDir().$pubFile;
        // die(print_r($x509FilePath));
        $publicKey = openssl_pkey_get_public("file://{$x509FilePath}");
        if($publicKey === false)
        {
            $this->outEncData = null;
            $this->outEnvKey  = null;
            $errorMessage = "Error while loading X509 public key certificate! Reason:";
            while(($errorString = openssl_error_string()))
            {
                $errorMessage .= $errorString . "\n";
            }
        }
        $srcData = $this->jsonData;
        $publicKeys = array($publicKey);
        $encData  = null;
        $envKeys  = null;
        // die(print_r($srcData));
        $result   = openssl_seal($srcData, $encData, $envKeys, $publicKeys);
        if($result === false)
        {
            $this->outEncData = null;
            $this->outEnvKey  = null;
            $errorMessage = "Error while encrypting data! Reason:";
            while(($errorString = openssl_error_string()))
            {
                $errorMessage .= $errorString . "\n";
            }
            //throw new Exception($errorMessage, self::ERROR_ENCRYPT_DATA);
        }
        $this->outEncData = base64_encode($encData);
        $this->outEnvKey  = base64_encode($envKeys[0]);
        return $errorMessage;
    }

    protected function _getUploadDir()
    {
        return DIR_CATALOG.'Mobilpay/cert/';
    }

    function makeActivateJson($declareatins, $urls, $images) {
        $jsonData = array(
            "sac_key" => $this->config->get('payment_mobilpay_signature'),
            "agreements" => array(
                "declare" => $declareatins,
                "urls"    => $urls,
                "images"  => $images,
                "ssl"     => false
            ),
            "lastUpdate" => date("c", strtotime(date("Y-m-d H:i:s"))), // To have Date & Time format on RFC3339
            "platform" => 'OpenCart 3X'
        );

        $post_data = json_encode($jsonData, JSON_FORCE_OBJECT);
        return $post_data;
    }

    // public function sendJsonCurl(){
    //     // $url = 'https://netopia-payments-user-service-api-fqvtst6pfa-ez.a.run.app/user/verify';
    //     $url = 'https://netopia-payments-user-service-api-fqvtst6pfa-ew.a.run.app/financial/agreement/add2';
    //     $ch = curl_init($url);
    //     $payload = json_encode($this->encData);
    //     // Attach encoded JSON string to the POST fields
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    //     // Set the content type to application/json
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    //     // Return response instead of outputting
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     // Execute the POST request
    //     $result = curl_exec($ch);
    //     // Close cURL resource
    //     curl_close($ch);
    //     return $result;
    // }

    public function sendJsonCurl() {
        $url = 'https://netopia-payments-user-service-api-fqvtst6pfa-ew.a.run.app/financial/agreement/add2';
        $ch = curl_init($url);

        $payload = json_encode($this->encData);

        // Attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the POST request
        $result = curl_exec($ch);

        if (!curl_errno($ch) && 0) {
              switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                  case 200:  # OK
                      $arr = array(
                          'code'    => $http_code,
                          'message' => "You send your request, successfully",
                          'data'    => json_decode($result)
                      );
                      break;
                  case 404:  # Not Found
                      $arr = array(
                          'code'    => $http_code,
                          'message' => "You send request to wrong URL"
                      );
                      break;
                  case 400:  # Bad Request
                      $arr = array(
                          'code'    => $http_code,
                          'message' => "You send Bad Request"
                      );
                      break;
                  case 405:  # Method Not Allowed
                      $arr = array(
                          'code'    => $http_code,
                          'message' => "Your method of sending data are Not Allowed"
                      );
                      break;
                  default:
                      $arr = array(
                          'code'    => $http_code,
                          'message' => "Opps! Something happened, verify how you send data & try again!!!"
                      );
              }
        } else {
              $arr = array(
                  'code'    => 0,
                  'message' => "Opps! There is some problem, you are not able to send data!!!"
              );
        }


        // Close cURL resource
        curl_close($ch);

        $finalResult = json_encode($arr, JSON_FORCE_OBJECT);
        return $finalResult;
    }

}
