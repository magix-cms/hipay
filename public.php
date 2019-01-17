<?php
include_once ('db.php');
/**
 * Class plugins_hipay_public
 */
class plugins_hipay_public extends plugins_hipay_db{
    /**
     * @var object
     */
    protected $template,
        $mail,
        $header,
        $data,
        $getlang,
        $modelDomain,
        $config,
        $settings,
        $about;
    public $purchase,$custom,$urlStatus;

    /**
     * plugins_hipay_public constructor.
     * @param null $t
     */
    public function __construct($t = null)
    {
        $this->template = $t ? $t : new frontend_model_template();
        $this->header = new http_header();
        $this->data = new frontend_model_data($this,$this->template);
        $this->getlang = $this->template->lang;
        $this->mail = new frontend_model_mail('hipay');
        $this->modelDomain = new frontend_model_domain($this->template);
        $this->about = new frontend_model_about($this->template);
        $formClean = new form_inputEscape();
        if (http_request::isPost('purchase')) {
            $this->purchase = $formClean->arrayClean($_POST['purchase']);
        }
        if (http_request::isPost('custom')) {
            $this->custom = $formClean->arrayClean($_POST['custom']);
        }

        if (http_request::isGet('urlStatus')) {
            $this->urlStatus = $formClean->simpleClean($_GET['urlStatus']);
        }

    }

    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string $context
     * @param boolean $assign
     * @return mixed
     */
    private function getItems($type, $id = null, $context = null, $assign = true) {
        return $this->data->getItems($type, $id, $context, $assign);
    }

    /**
     * @return array|false|string
     */
    private function getIp(){
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        }else{
            if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
                $realip = getenv( 'HTTP_X_FORWARDED_FOR' );
            } elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
                $realip = getenv( 'HTTP_CLIENT_IP' );
            } else {
                $realip = getenv( 'REMOTE_ADDR' );
            }
        }
        return $realip;
    }

    /**
     * @param bool $requestIp
     * @return array|false|string
     */
    public function ip($requestIp=false){
        $matcher = new http_requestMatcher();
        if($matcher->checkIp($this->getIp(),$this->getIp()) == true){
            return $this->getIp();
        }
    }

    /**
     * @param $setConfig
     * @return array
     */
    private function setUrl($setConfig){
        $baseUrl = http_url::getUrl();
        $lang = $this->getlang;
        $setConfig['plugin'] = isset($setConfig['plugin']) ? $setConfig['plugin'] : false;
        if($setConfig['plugin']) {
            $url = $baseUrl . '/'. $lang . '/' . $setConfig['plugin'] . '/';
            return array(
                'callback' => $url . '?urlStatus=urlCallback',
                'accept' => $url . '?urlStatus=urlAccept',
                'decline' => $url . '?urlStatus=urlDecline',
                'cancel' => $url . '?urlStatus=urlCancel'
            );
        }
    }

    /**
     * Retourne les données enregistrées dans la base de données pour le compte hipay
     * @return array
     */
    private function setData(){

        $data =$this->getItems('root',NULL,'one',false);

        if($data != 'NULL'){
            return array(
                'wsLogin'               =>  $data['wslogin'],
                'wsPassword'            =>  $data['wspassword'],
                'websiteId'             =>  $data['websiteid'],
                'signkey'               =>  $data['signkey'],
                'formaction'            =>  $data['formaction'],
                'categoryId'            =>  $data['categoryid'],
                'direct'                =>  $data['direct'],
                'iso'                   =>  $this->getlang
            );
        }
    }

    /**
     * @param $config
     * @throws Exception
     */
    public function createPayment($config){
        try {
            $this->template->addConfigFile(
                array(component_core_system::basePath() . '/plugins/hipay/i18n/'),
                array('public_local_'),
                false
            );
            $this->template->configLoad();

            // Chargement des données Hipay en base de données
            $setData = $this->setData();

            $collection = $this->about->getCompanyData();
            if ($setData != null) {
                if ($setData['formaction'] === 'test') {
                    $urlOrder = 'https://test-ws.hipay.com/soap/payment-v2?wsdl';
                } elseif ($setData['formaction'] === 'production') {
                    $urlOrder = 'https://ws.hipay.com/soap/payment-v2?wsdl';
                }

                $urlWebsite = http_url::getUrl() . '/' . $setData['iso'] . '/';
                // seturl pour les notifications et process
                $setUrl = $this->setUrl($config);

                if ($urlOrder) {
                    // STEP 1 : soap flow options
                    $options = array(
                        'compression'   => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                        'cache_wsdl'    => WSDL_CACHE_NONE,
                        'soap_version'  => SOAP_1_1,
                        'encoding'      => 'UTF-8',
                        'exceptions'    => true,
                        'trace'         => isset($config['debug']) ? true : false
                    );
                    // STEP 2 : Soap client initialization
                    $client = new SoapClient($urlOrder, $options);
                    //https://test-ws.hipay.com/soap/payment-v2?wsdl
                    // L'interface de paiement sera, par défaut, en français international
                    $setlocal = $setData['iso'] . '_' . strtoupper($config['locale']);
                    $executionDate = date('c');
                    // STEP 3 : Soap call on confirm method of manual-capture webservice
                    /*$freeData = array();
                    if(isset($config['freeData']) && $config['freeData'] != NULL){
                        foreach ($config['freeData'] as $key => $value) {
                            $freeData[]= array('key'=>$key,'value'=>$value);
                        }
                    }else{
                        $freeData = NULL;
                    }*/

                    $result = $client->generate(array('parameters' => array(
                        'wsLogin'       => $setData['wsLogin'],
                        'wsPassword'    => $setData['wsPassword'],
                        'websiteId'     => $setData['websiteId'],
                        'categoryId'    => $setData['categoryId'],
                        'description'   => $this->template->getConfigVars('order_on') . ' ' . $collection['name'],
                        //'method' => 'iframe',
                        'freeData'   => $config['freeData'],
                        /*'freeData' => array(
                            array(
                                'key'   => 'order',
                                'value' => $config['order'],
                            ),
                            array(
                                'key'   => 'shipping',
                                'value' => $config['shipping'],
                            ),
                        ),*/
                        'currency' => 'EUR',
                        'amount' => $config['amount'],
                        'rating' => 'ALL',
                        'locale' => $setlocal,
                        'customerIpAddress' => $this->ip(),
                        'manualCapture' => '0',
                        'executionDate' => $executionDate,
                        'customerEmail' => $config['customerEmail'],
                        'urlCallback'   => $setUrl['callback'],
                        'urlAccept'     => $setUrl['accept'],
                        'urlCancel'     => $setUrl['cancel'],
                        'urlDecline'    => $setUrl['decline']
                    )));

                    if(isset($config['debug']) && $config['debug']){
                        // Display Hipay soap data
                        header('Content-type: text/xml');
                        print $client->__getLastRequest();

                    }else{
                        // Get Hipay redirect URL and redirect user
                        $approvalUrl = $result->{'generateResult'}->{'redirectUrl'};
                        header("location:".$approvalUrl);
                    }
                }
            }


        }catch (SoapFault $e) {
            $logger = new debug_logger(MP_LOG_DIR);
            $logger->log('php', 'error', "SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})", debug_logger::LOG_MONTH);
        }
    }

    /**
     * @return array
     */
    public function getProcess(){

        $analyse = $this->analyzeNotificationXML(
            stripslashes($_POST['xml']),
            $operation,
            $status,
            $date,
            $time,
            $transid,
            $amount,
            $currency,
            $idformerchant,
            $merchantdatas,
            $emailClient,
            $subscriptionId,
            $refProduct
        );
        if ($analyse){
            return array(
                'operation'     =>  $operation,
                'status'        =>  $status,
                'date'          =>  $date,
                'time'          =>  $time,
                'transaction'   =>  $transid,
                'amount'        =>  (string) $amount/*-$merchantdatas['shipping']*/,
                'currency'      =>  (string) $currency,
                'idformerchant' =>  $idformerchant,
                'merchantdatas' =>  (array) $merchantdatas,
                /*'order'         =>  $merchantdatas['order'],
                'shipping'      =>  $merchantdatas['shipping'],*/
                'email'         =>  (string) $emailClient,
                'subscriptionId'=>  $subscriptionId,
                'ref'           =>  $refProduct
            );
        }
    }

    /**
     * @param $xml
     * @param $operation
     * @param $status
     * @param $date
     * @param $time
     * @param $transid
     * @param $origAmount
     * @param $origCurrency
     * @param $idformerchant
     * @param $merchantdatas
     * @param $emailClient
     * @param $subscriptionId
     * @param $refProduct
     * @return bool
     */
    public function analyzeNotificationXML($xml,&$operation,&$status,&$date,&$time,&$transid,&$origAmount,&$origCurrency,&$idformerchant,&$merchantdatas,&$emailClient,&$subscriptionId,&$refProduct)
    {
        $xml = preg_replace('~\s*(<([^>]*)>[^<]*</\2>|<[^>]*>)\s*~','$1',$xml);
        $operation='';
        $status='';
        $date='';
        $time='';
        $transid='';
        $origAmount='';
        $origCurrency='';
        $idformerchant='';
        $merchantdatas=array();
        $emailClient='';
        $subscriptionId='';
        $refProduct=array();
        try {
            $obj = new SimpleXMLElement(trim($xml));
        } catch (Exception $e) {
            return false;
        }
        if (isset($obj->result[0]->operation))
            $operation=$obj->result[0]->operation;
        else return false;

        if (isset($obj->result[0]->status))
            $status=$obj->result[0]->status;
        else return false;

        if (isset($obj->result[0]->date))
            $date=$obj->result[0]->date;
        else return false;

        if (isset($obj->result[0]->time))
            $time=$obj->result[0]->time;
        else return false;

        if (isset($obj->result[0]->transid))
            $transid=$obj->result[0]->transid;
        else return false;

        if (isset($obj->result[0]->origAmount))
            $origAmount=$obj->result[0]->origAmount;
        else return false;

        if (isset($obj->result[0]->origCurrency))
            $origCurrency=$obj->result[0]->origCurrency;
        else return false;

        if (isset($obj->result[0]->idForMerchant))
            $idformerchant=$obj->result[0]->idForMerchant;
        else return false;

        if (isset($obj->result[0]->merchantDatas)) {
            $d = $obj->result[0]->merchantDatas->children();
            foreach($d as $xml2) {
                if (preg_match('#^_aKey_#i',$xml2->getName())) {
                    $indice = substr($xml2->getName(),6);
                    $merchantdatas[$indice]=$obj->result[0]->merchantDatas->{$xml2->getName()};
                }
            }

        }

        if (isset($obj->result[0]->emailClient))
            $emailClient=$obj->result[0]->emailClient;
        else return false;

        if (isset($obj->result[0]->subscriptionId))
            $subscriptionId=$obj->result[0]->subscriptionId;

        foreach($obj->result[0] as $key=>$value)
        {
            if(preg_match('#^refProduct[\d]#', $key)) {
                $refProduct[] = (string)$value;
            }
        }

        return true;
    }

    // --- Mail
    /**
     * @param $type
     * @return string
     */
    private function setTitleMail(){
        $collection = $this->about->getCompanyData();

        /*switch ($type) {
            default: $title = $this->template->getConfigVars('order_on') . ' ' . $collection['name'];//$this->template->getConfigVars($type.'_title');
        }

        return sprintf($title, $collection['name']);*/
        return $this->template->getConfigVars('order_on') . ' ' . $collection['name'];
    }

    /**
     * @param $sender
     * @param $data
     */
    protected function send_email($sender,$data) {
        if($this->getlang) {
            $contacts = $this->getItems('contacts',array('lang' => $this->getlang),'all',false);
            if($contacts != null) {
                //Initialisation du contenu du message
                $send = false;
                $tpl = 'admin';//$this->type ? $this->type : 'admin';
                $error = false;
                //$sender = $this->msg['email'];
                /*if($this->msg['email'] === 'error-mail') {
                    $tpl = 'error';
                    $error = true;
                    $sender = '';
                }*/
                $data['email'] = $sender;
                foreach ($contacts as $recipient) {
                    /*$title = "title: ".$this->setTitleMail() . ' tpl : '.$tpl.' email:'.$recipient['mail_contact']. ' sender: '.$sender;
                    $logger = new debug_logger(MP_LOG_DIR);
                    $logger->log('php', 'log', $title, debug_logger::LOG_MONTH);*/

                    $isSend = $this->mail->send_email($recipient['mail_contact'],$tpl,$data,$this->setTitleMail(),$sender);
                    if(!$send) $send = $isSend;
                }
                /*if($send)
                    $this->getNotify('success');
                else
                    $this->getNotify('error');*/
            }/*
            else {
                $this->getNotify('error','configured');
            }*/
        }
    }
    // ----------------------------- -------------------------------

    /**
     * @throws Exception
     */
    public function run(){
        if(isset($this->urlStatus)){
            $setData = $this->setData();
            switch($this->urlStatus){
                case 'urlCallback':
                    $logger = new debug_logger(MP_LOG_DIR);
                    if (isset($_POST['xml'])) {
                        $data = $this->getProcess();
                        if(is_array($data)){

                            $operation = $data['operation'];
                            $merchantdatas = $data['merchantdatas'];
                            $transid = $data['transaction'];
                            $amount = $data['amount'];
                            $currency_order = $data['currency'];
                            $idformerchant = $data['idformerchant'];
                            $date = $data['date'];
                            $time = $data['time'];
                            $emailClient = $data['email'];
                            $status = $data['status'];
                            if($setData['direct'] === 1) {
                                if($operation == 'authorization'){

                                } elseif ($operation == 'capture') {

                                    $this->send_email($emailClient, $merchantdatas);

                                }elseif($operation == 'reject'){

                                }
                            }
                        }
                    }

                    break;
                case 'urlAccept':
                    break;
                case 'urlDecline':
                    break;
                case 'urlCancel':
                    break;
            }
        }else{
            if(isset($this->purchase)) {
                $freeData = array();
                //$data = array('order' => 'myorder', 'shipping' => '14');
                if(isset($this->custom) && $this->custom != NULL) {
                    foreach ($this->custom as $key => $value) {
                        $freeData[] = array(
                            'key' => $key,
                            'value' => $value
                        );
                    }
                }else{
                    $freeData = NULL;
                }

                if(isset($this->purchase['amount']) && isset($this->purchase['email'])){
                    // config data for payment
                    $config = array(
                        'plugin'        => 'hipay',
                        'locale'        => 'BE',
                        'amount'        => $this->purchase['amount'],
                        'customerEmail' => $this->purchase['email'],
                        'freeData'      => $freeData,
                        'debug'         => false//pre,none,printer
                    );

                    $this->createPayment($config);
                }
            }
        }
    }
}