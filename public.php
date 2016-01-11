<?php
require_once 'mapi/mapi_package.php';

/**
 * Class plugins_hipay_public
 */
class plugins_hipay_public extends DBHipay{
    public $urlprocess = array(
        'seturlok'=>'/payment/success/',
        'seturlnook'=>'/payment/success/',
        'seturlcancel'=>'/payment/cancel/',
        'seturlexception'=>'/payment/exception/',
        'seturlack'=>'/payment/process/'
    );
    /**
     * constructeur
     */
    public function __construct()
    {
        $this->template = new frontend_controller_plugins();
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
        return array(
            'mailhack'              =>  $data['mailhack'],
            'pwaccount'             =>  $data['pwaccount'],
            'setaccount'            =>  $data['setaccount'],
            'setmarchantsiteid'     =>  $data['setmarchantsiteid'],
            'mailcart'              =>  $data['mailcart'],
            'setcategory'           =>  $data['setcategory'],
            'signkey'               =>  $data['signkey'],
            'formaction'            =>  $data['formaction'],
            'iso'                   =>  frontend_model_template::current_Language()
        );
    }

    /**
     * @param $setParams
     * @return string
     * @throws Exception
     * @example :
     *  $hipay = new plugins_hipay_public();
        $payprocess = $hipay->getData(
            array(
                'plugin'    =>  'cartpay',
                'key'       =>  $session_key,
                'order'     =>  $data_cart['id_cart'],
                'amount'    =>  $amount_to_pay
            )
        );
     *
     */
    public function getData($setParams)
    {
        $this->template->configLoad();
        // Chargement des données Hipay en base de données
        $data = $this->setData();
        //Production : https://payment.hipay.com/order/
        //Dev : https://test-payment.hipay.com/order/
        if ($data['formaction'] === 'test') {
            $urlhipay = 'https://test-payment.hipay.com/order/';
        } elseif ($data['formaction'] === 'production') {
            $urlhipay = 'https://payment.hipay.com/order/';
        }
        $urlwebsite = magixcjquery_html_helpersHtml::getUrl() . '/' . $data['iso'] . '/';
        // Paramètres Hipay
        $params = new HIPAY_MAPI_PaymentParams();
        // Paramètres de connexion à la plateforme Hipay. Attention, il ne s'agit pas du login et mot de passe utilisé pour se connecter
        // au site Hipay, mais du login et mot de passe propre à la connexion à la passerelle. Le login est l'id du compte associé au site,
        // le mot de passe est le « mot de passe marchand » associé au site.
        $params->setLogin($data['setaccount'], $data['pwaccount']);
        //89043 = Compte N° 89043 (voir synthèse des comptes)
        // Les sommes seront créditées sur le compte 59118, sauf les taxes qui seront créditées sur le compte 59119
        $params->setAccounts($data['setaccount']);
        // L'interface de paiement sera, par défaut, en français international
        $setlocal = $data['iso'] . '_' . strtoupper($data['iso']);
        $params->setLocale($setlocal);
        //fr_FR, fr_BE, de_DE, en_GB, en_US, es_ES, nl_NL, nl_BE, pt_PT
        // L'interface sera l'interface Web
        $params->setMedia('WEB');
        // Le contenu de la commande s'adresse à un public au moins âgé de 16 ans.
        $params->setRating('ALL');
        // Il s'agit d'un paiement simple
        $params->setPaymentMethod(HIPAY_MAPI_METHOD_SIMPLE);
        // La capture sera immédiate
        $params->setCaptureDay(HIPAY_MAPI_CAPTURE_IMMEDIATE);
        // Les montants sont donnés en euros, la devise associée au compte du site.
        $params->setCurrency('EUR');
        // L'identifiant au choix du commerçant pour cette commande est REF6522
        // session_key_cart
        $params->setIdForMerchant('FB' . $setParams['key']);
        // Deux données du type clé=valeur sont déclarées et seront retournées au commerçant après le paiement dans les flux de notification.
        // idcart
        $params->setMerchantDatas('order', $setParams['order']);
        if (array_key_exists('idprofil', $setParams)) {
            $params->setMerchantDatas('idprofil', $setParams['idprofil']);
        }
        // Cette commande se rapporte au site web qu'a déclaré le marchand dans la plateforme Hipay et qui a l'identifiant 9
        $params->setMerchantSiteId($data['setmarchantsiteid']);
        // seturl pour les notifications et process
        $seturl = $this->setUrl();
        // Si le paiement est accepté, le client est redirigé vers la page success.html
        $params->setUrlOk($urlwebsite . $setParams['plugin'] . $seturl['seturlok']);
        // Si le paiement est refusé, le client est redirigé vers la page refused.html
        $params->setUrlNok($urlwebsite . $setParams['plugin'] . $seturl['seturlnook']);
        // Si le paiement est annulé par le client, il est redirigé vers la page cancel.html
        $params->setUrlCancel($urlwebsite . $setParams['plugin'] . $seturl['seturlcancel']);
        // L'email de notification du marchand posté en parallèle des notifications http sur l'url de ack
        // cf chap 19 : Réception de notification d'un paiement par le marchand
        $params->setEmailAck($data['mailhack']);
        // Le site du marchand est notifié automatiquement du résultat du paiement par un appel au script "listen_hipay_notification.php"
        // cf chap 19 : Réception de notification d'un paiement par le marchand
        $params->setUrlAck($urlwebsite. $setParams['plugin'] . $seturl['seturlack']);
        $t = $params->check();
        // secondly, we define taxes which will be added on HiPAY page to order amount
        // in our case, we wan't to add taxes; it's why we put 0 as a tax amount
        $taxParam = new HIPAY_MAPI_Tax();
        $taxParam->setTaxName($this->template->getConfigVars('without_tax'));
        $taxParam->setTaxVal(0, false);
        if (!$taxParam->check()) {
            throw new Exception("Error when creating Tax object");
        }
        // Premier produit : 2 exemplaires d'un livre à 12.50 euros l'unité sur (les taxes $tax3 et $tax2)
        $item1 = new HIPAY_MAPI_Product();
        $item1->setName($this->template->getConfigVars('total_products'));
        $item1->setInfo($this->template->getConfigVars('total_products_on') . ' ' . $this->template->getConfigVars('website'));
        $item1->setquantity(1);
        //Assigne la référence du produit (aux choix du marchand)
        $item1->setRef('FB' . $setParams['key']);
        //https://test-payment.hipay.com/order/list-categories/id/[id_site_marchand]
        //[id_site_marchand] = 3684
        $item1->setCategory($data['setcategory']);
        //Le prix a envoyer à Hipay
        $item1->setPrice($setParams['amount']);
        //$item1->setTax(array($tax3,$tax2));
        $item1->setTax(array($taxParam));
        $t = $item1->check();
        if (!$t) {
            echo "Erreur de création de l'objet product";
            exit;
        }
        $order = new HIPAY_MAPI_Order();
        // Titre et informations sur la commande
        $order->setOrderTitle($this->template->getConfigVars('order_on') . ' ' . $this->template->getConfigVars('website'));
        $order->setOrderInfo('Reference : ' . $setParams['key']);
        // La catégorie de la commande est 3
        // cf annexe 7 pour savoir comment obtenir la liste des catégories disponibles pour votre site
        $order->setOrderCategory($data['setcategory']);
        // Les frais d'envoi sont de 1.50 euros HT, sur lesquels est appliquée la taxe $tax1
        $order->setShipping($setParams['shipping'], array($taxParam));
        // Les frais d'assurance sont de 2 euros HT, sur lesquels sont appliquées les taxes $tax1 et $tax3
        //$order->setInsurance(2,array($tax3,$tax1));
        // Les coûts fixes sont de 2.25 euros HT, sur lesquels est appliquée la taxe $tax3
        //$order->setFixedCost($amount_tax,array($taxParam));
        // Cette commande a deux affiliés, $aff1 et $aff2
        //$order->setAffiliate(array($aff1,$aff2));
        $t = $order->check();
        if (!$t) {
            echo "Erreur de création de l'objet order";
            exit;
        }
        try {
            $commande = new HIPAY_MAPI_SimplePayment($params, $order, array($item1));
        } catch (Exception $e) {
            echo " Error " . $e->getMessage();
        }

        $xmlTx = $commande->getXML();
        $output = HIPAY_MAPI_SEND_XML::sendXML($xmlTx, $urlhipay);
        //$url = HIPAY_GATEWAY_URL;
        $r = HIPAY_MAPI_COMM_XML::analyzeResponseXML($output, $url, $err_msg);
        /*if ($r===true) {
            echo 'hipay ok';
        }else{
            echo $err_msg;
        }*/
        /*if ($r === true) {
            // On renvoie l'internaute vers l'url indiquée par la plateforme Hipay
            //header('Location: '.$url) ;
            echo $url;
        } else {
            // Une erreur est intervenue
            echo $err_msg;
            //$url_error = '/error.html';
            //header('Location: '.$url_error) ;}
        }*/
        /*
            * Var $data contain the XML string describing your order
           */
        $getdata = trim($output);

        // your website Hipay key
        $signKey = $data['signkey'];
        $encodedData = base64_encode($getdata);
        $md5Sign = md5($encodedData.$signKey);
        $forms_hipay = '<form target="_blank" action="'.$url.'" method="post" >
            <input type="hidden" name="mode" value="MODE_B" />
            <input type="hidden" name="website_id" value="'.$data['setmarchantsiteid'].'" />
            <input type="hidden" name="sign" value="'.$md5Sign.'" />
            <input type="hidden" name="data" value="'.$encodedData.'" />
            <input type="image" name="send" src="https://www.hipay.com/images/i18n/'.$data['iso'].'/bt_payment_1.png" />
        </form>';
        return $forms_hipay;
    }

    /**
     * @return array
     */
    public function getProcess(){
        $analyse = HIPAY_MAPI_COMM_XML::analyzeNotificationXML(
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
                'amount'        =>  $amount,
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