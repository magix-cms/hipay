<?php
/**
 * Class plugins_hipay_public
 */
class plugins_hipay_public extends DBHipay{
    protected $template,$modelSystem;
    /**
     * @var array
     */
    public $urlprocess = array(
        'seturlok'          =>'/payment/success/',
        'seturlnook'        =>'/payment/success/',
        'seturlcancel'      =>'/payment/cancel/',
        'seturlexception'   =>'/payment/exception/',
        'seturlack'         =>'/payment/process/'
    );

    /**
     * constructeur
     */
    public function __construct()
    {
        $this->template = new frontend_controller_plugins();
        $this->modelSystem = new magixglobal_model_system();
    }

    /**
     * @return array
     */
    public function setUrl(){
        return $this->urlprocess;
    }
    /**
     * Retourne les données enregistrées dans la base de données pour le compte hipay
     * @return array
     */
    private function setData(){
        $data = parent::selectOne();
        if($data != 'NULL'){
            return array(
                'wsLogin'               =>  $data['wsLogin'],
                'wsPassword'            =>  $data['wsPassword'],
                'websiteId'             =>  $data['websiteId'],
                'signkey'               =>  $data['signkey'],
                'formaction'            =>  $data['formaction'],
                'iso'                   =>  frontend_model_template::current_Language()
            );
        }
    }

    /**
     * @param $data
     * @return mixed|void
     */
    private function setCategory($data){
        //https://test-payment.hipay.com/order/list-categories/id/
        $options = array(
            CURLOPT_RETURNTRANSFER  => true,
            CURLINFO_HEADER_OUT     => true,
            CURLOPT_URL             => $data['url'],
            CURLOPT_HTTPHEADER      => array('Content-type: text/xml','Accept: text/xml'),
            CURLOPT_TIMEOUT         => 300,
            CURLOPT_CONNECTTIMEOUT  => 300,
            CURLOPT_SSL_VERIFYPEER   => false,
            CURLOPT_CUSTOMREQUEST   => "GET"
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        if (array_key_exists('debug', $data) && $data['debug']) {
            var_dump($curlInfo);
            var_dump($response);
        }
        if ($curlInfo['http_code'] == '200') {
            if ($response) {
                return $response;
            }
        }elseif($curlInfo['http_code'] == '0'){
            print 'Error HTTP: code 0';
            return;
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    private function getCategory($data){
        $object = simplexml_load_string($this->setCategory($data), null, LIBXML_NOCDATA);
        return $object->{'categoriesList'}->{'category'}->attributes();
    }

    /**
     * @param $setParams
     * @return string
     *
    $hipay = new plugins_hipay_public();
    $hipayProcess = $hipay->getData(
        array(
            'plugin'        =>  'cartpay',
            'key'           =>  $session_key,
            'order'         =>  $id_cart,
            'amount'        =>  $amount_pay_with_tax,
            'shipping'      =>  $shipping,
            'locale'        =>  'BE',
            'customerEmail' => $data_cart['email_cart']
        )
    );
     */
    public function getData($setParams){
        try {
            frontend_model_smarty::getInstance()->configLoad(
                $this->modelSystem->base_path().'plugins/hipay/i18n/public_local_'.frontend_model_template::current_Language().'.conf'
            );
            // Chargement des données Hipay en base de données
            $data = $this->setData();
            if ($data != null) {
                if ($data['formaction'] === 'test') {
                    $urlOrder = 'https://test-ws.hipay.com/soap/payment-v2?wsdl';
                    $urlCategory = 'https://test-payment.hipay.com/order/list-categories/id/';
                } elseif ($data['formaction'] === 'production') {
                    $urlOrder = 'https://ws.hipay.com/soap/payment-v2?wsdl';
                    $urlCategory = 'https://payment.hipay.com/order/list-categories/id/';
                }
                $getCategory = $this->getCategory(
                    array(
                        'url' => $urlCategory . $data['websiteId'],
                        'debug' => false
                    )
                );
                $urlwebsite = magixcjquery_html_helpersHtml::getUrl() . '/' . $data['iso'] . '/';
                // seturl pour les notifications et process
                $seturl = $this->setUrl();
                if ($getCategory) {
                    // STEP 1 : soap flow options
                    $options = array(
                        'compression'   => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                        'cache_wsdl'    => WSDL_CACHE_NONE,
                        'soap_version'  => SOAP_1_1,
                        'encoding'      => 'UTF-8',
                        'exceptions'    => true
                    );
                    // STEP 2 : Soap client initialization
                    $client = new SoapClient($urlOrder, $options);
                    //https://test-ws.hipay.com/soap/payment-v2?wsdl
                    // L'interface de paiement sera, par défaut, en français international
                    $setlocal = $data['iso'] . '_' . strtoupper($setParams['locale']);
                    $executionDate = date('c');
                    // STEP 3 : Soap call on confirm method of manual-capture webservice
                    $result = $client->generate(array('parameters' => array(
                        'wsLogin'       => $data['wsLogin'],
                        'wsPassword'    => $data['wsPassword'],
                        'websiteId'     => $data['websiteId'],
                        'categoryId'    => $getCategory,
                        'description'   => $this->template->getConfigVars('order_on') . ' ' . $this->template->getConfigVars('website'),
                        //'method' => 'iframe',
                        'freeData' => array(
                            array(
                                'key'   => 'order',
                                'value' => $setParams['order'],
                            ),
                            array(
                                'key'   => 'shipping',
                                'value' => $setParams['shipping'],
                            ),
                        ),
                        'currency' => 'EUR',
                        'amount' => $setParams['amount'],
                        'rating' => 'ALL',
                        'locale' => $setlocal,
                        //'customerIpAddress' => '46.182.41.35',
                        'manualCapture' => '0',
                        'executionDate' => $executionDate,
                        'customerEmail' => $setParams['customerEmail'],
                        'urlCallback'   => $urlwebsite . $setParams['plugin'] . $seturl['seturlack'],
                        'urlAccept'     => $urlwebsite . $setParams['plugin'] . $seturl['seturlok']
                    )));
                    //print_r($result);
                    $forms_hipay = '<a href="' . $result->{'generateResult'}->{'redirectUrl'} . '"><img src="https://www.hipay.com/images/i18n/' . $data['iso'] . '/bt_payment_1.png" /></a>';
                    return $forms_hipay;
                }
            }
        }catch (SoapFault $e) {
            magixglobal_model_system::magixlog("SOAP Fault: (faultcode: {$e->faultcode}, faultstring: {$e->faultstring})",$e);
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
                'amount'        =>  $amount-$merchantdatas['shipping'],
                'currency'      =>  $currency,
                'idformerchant' =>  $idformerchant,
                'merchantdatas' =>  $merchantdatas,
                'order'         =>  $merchantdatas['order'],
                'shipping'      =>  $merchantdatas['shipping'],
                'email'         =>  $emailClient,
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
}
class DBHipay
{
    /**
     * @return array
     */
    protected function selectOne()
    {
        $query = 'SELECT hp.*
            FROM mc_plugins_hipay AS hp';
        return magixglobal_model_db::layerDB()->selectOne($query);
    }
}