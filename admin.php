<?php
class plugins_hipay_admin extends DBHipay
{
    protected $header, $template, $message;
    public static $notify = array('plugin' => 'true');
    public $getlang, $plugin, $edit, $id, $getpage, $wsLogin,$wsPassword,$websiteId,$customerIpAddress,$signkey,$formaction,$categoryId;

    /**
     * constructeur
     */
    public function __construct()
    {
        if (class_exists('backend_model_message')) {
            $this->message = new backend_model_message();
        }
        // Global
        if (magixcjquery_filter_request::isGet('getlang')) {
            $this->getlang = magixcjquery_filter_isVar::isPostNumeric($_GET['getlang']);
        }
        if (magixcjquery_filter_request::isGet('edit')) {
            $this->edit = magixcjquery_filter_isVar::isPostNumeric($_GET['edit']);
        }
        if (magixcjquery_filter_request::isGet('action')) {
            $this->action = magixcjquery_form_helpersforms::inputClean($_GET['action']);
        }
        if (magixcjquery_filter_request::isGet('tab')) {
            $this->tab = magixcjquery_form_helpersforms::inputClean($_GET['tab']);
        }
        // Dédié
        if (magixcjquery_filter_request::isGet('plugin')) {
            $this->plugin = magixcjquery_form_helpersforms::inputClean($_GET['plugin']);
        }
        if (magixcjquery_filter_request::isGet('id')) {
            $this->id = (integer)magixcjquery_filter_isVar::isPostNumeric($_GET['id']);
        }
        // POST
        if (magixcjquery_filter_request::isPost('wsLogin')) {
            $this->wsLogin = magixcjquery_form_helpersforms::inputClean($_POST['wsLogin']);
        }
        if (magixcjquery_filter_request::isPost('wsPassword')) {
            $this->wsPassword = magixcjquery_form_helpersforms::inputClean($_POST['wsPassword']);
        }
        if (magixcjquery_filter_request::isPost('websiteId')) {
            $this->websiteId = magixcjquery_form_helpersforms::inputClean($_POST['websiteId']);
        }
        if (magixcjquery_filter_request::isPost('customerIpAddress')) {
            $this->customerIpAddress = magixcjquery_form_helpersforms::inputClean($_POST['customerIpAddress']);
        }
        if (magixcjquery_filter_request::isPost('signkey')) {
            $this->signkey = magixcjquery_form_helpersforms::inputClean($_POST['signkey']);
        }
        if (magixcjquery_filter_request::isPost('formaction')) {
            $this->formaction = magixcjquery_form_helpersforms::inputClean($_POST['formaction']);
        }
        if (magixcjquery_filter_request::isPost('categoryId')) {
            $this->categoryId = magixcjquery_form_helpersforms::inputClean($_POST['categoryId']);
        }

        $this->header = new magixglobal_model_header();
        $this->template = new backend_controller_plugins();
    }

    /**
     * @access private
     * Installation des tables mysql du plugin
     */
    private function install_table($create)
    {
        if (parent::c_show_table() == 0) {
            $create->db_install_table('db.sql', 'request/install.tpl');
        } else {
            //$magixfire = new magixcjquery_debug_magixfire();
            //$magixfire->magixFireInfo('Les tables mysql sont installés', 'Statut des tables mysql du plugin');
            return true;
        }
    }

    /**
     * return xml object
     * @param $data
     * @return mixed|void
     */
    private function setSoapCategory($data){
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
     * @return category array from xml object
     * @param $data
     * @return mixed
     */
    private function setCategory($data){
        $parseData = array();
        $newData = array();
        $object = simplexml_load_string($this->setSoapCategory($data), null, LIBXML_NOCDATA);

        foreach ($object->children() as $key) {

            foreach ($key->children() as $category => $value) {

                $parseData[strval($value['id'])]= strval($value);
            }
        }

        foreach($parseData as $key => $value){
            $newData[] = array(
                'id'    =>  $key,
                'name'  =>  $value
            );
        }

        return $newData;
    }

    /**
     * @return mixed
     */
    private function getCategory(){
        $config = parent::selectOne();
        if($config['websiteId'] != null && $config['formaction'] != null){
            if ($config['formaction'] === 'test') {
                $urlOrder = 'https://test-ws.hipay.com/soap/payment-v2?wsdl';
                $urlCategory = 'https://test-payment.hipay.com/order/list-categories/id/';
            } elseif ($config['formaction'] === 'production') {
                $urlOrder = 'https://ws.hipay.com/soap/payment-v2?wsdl';
                $urlCategory = 'https://payment.hipay.com/order/list-categories/id/';
            }
            return $this->setCategory(
                array(
                    'url' => $urlCategory . $config['websiteId'],
                    'debug' => false
                )
            );
        }
    }

    /**
     * Prépare les données utilisateur
     * @return array
     */
    private function setData(){
        $data = parent::selectOne();
        return array(
            'wsLogin'              =>  $data['wsLogin'],
            'wsPassword'           =>  $data['wsPassword'],
            'websiteId'            =>  $data['websiteId'],
            'customerIpAddress'    =>  $data['customerIpAddress'],
            'signkey'              =>  $data['signkey'],
            'formaction'           =>  $data['formaction'],
            'categoryId'           =>  $data['categoryId']
        );
    }

    /**
     * Assign table data
     */
    private function getData(){
        $data = $this->setData();
        $this->template->assign('getCategory',$this->getCategory());
        $this->template->assign('dataHipay', $data, true);
    }

    /**
     * @param $data
     */
    private function add($data){
        parent::insert($data);
    }

    /**
     * @param $data
     */
    private function update($data){
        parent::uData($data);
    }

    /**
     * @param $data
     */
    private function save($data){
        if($data['edit'] != null){
            $this->update($data);
            $this->message->getNotify('update',self::$notify);
        }else{
            $this->add($data);
            $this->message->getNotify('add',self::$notify);
        }
    }
    /**
     * Execute plugin
     */
    public function run()
    {
        if (self::install_table($this->template) == true) {
            if (isset($this->wsLogin)) {
                if(empty($this->categoryId)){
                    $this->categoryId = null;
                }
                $control = parent::selectOne();
                $this->save(
                    array(
                        'edit'                  =>  $control['idhipay'],
                        'wsLogin'               =>  $this->wsLogin,
                        'wsPassword'            =>  $this->wsPassword,
                        'websiteId'             =>  $this->websiteId,
                        'customerIpAddress'     =>  $this->customerIpAddress,
                        'signkey'               =>  $this->signkey,
                        'formaction'            =>  $this->formaction,
                        'categoryId'            =>  $this->categoryId
                    )
                );
            }else{
                $this->getData();
                $this->template->display('list.tpl');
            }
        }
    }
    public function setConfig(){
        return array(
            'url'=> array(
                'lang'  => 'none',
                'action'=>''
            ),
            'icon'=> array(
                'type'=>'font',
                'name'=>'fa fa-credit-card-alt'
            )
        );
    }
}

class DBHipay
{
    /**
     * Vérifie si les tables du plugin sont installé
     * @access protected
     * return integer
     */
    protected function c_show_table()
    {
        $table = 'mc_plugins_hipay';
        return magixglobal_model_db::layerDB()->showTable($table);
    }
    /**
     * @return array
     */
    protected function selectOne()
    {
        $query = 'SELECT hp.*
            FROM mc_plugins_hipay AS hp';
        return magixglobal_model_db::layerDB()->selectOne($query);
    }
    /**
     * @param $idcatalog
     * @param $data
     */
    protected function insert($data){
        if(is_array($data)){
            $sql = 'INSERT INTO mc_plugins_hipay (wsLogin,wsPassword,websiteId,customerIpAddress,signkey,formaction)
		    VALUE(:wsLogin,:wsPassword,:websiteId,:customerIpAddress,:signkey,:formaction,:categoryId)';
            magixglobal_model_db::layerDB()->insert($sql,
                array(
                    ':wsLogin'              =>  $data['wsLogin'],
                    ':wsPassword'           =>  $data['wsPassword'],
                    ':websiteId'            =>  $data['websiteId'],
                    ':customerIpAddress'    =>  $data['customerIpAddress'],
                    ':signkey'              =>  $data['signkey'],
                    ':formaction'           =>  $data['formaction'],
                    ':categoryId'           =>  $data['categoryId']
                ));
        }

    }

    /**
     * @param $idcatalog
     * @param $data
     */
    protected function uData($data){
        if(is_array($data)){
            $sql = 'UPDATE mc_plugins_hipay
            SET wsLogin=:wsLogin,wsPassword=:wsPassword,websiteId=:websiteId,customerIpAddress=:customerIpAddress,signkey=:signkey,formaction=:formaction,categoryId=:categoryId
            WHERE idhipay=:edit';
            magixglobal_model_db::layerDB()->update($sql,
                array(
                    ':edit'	                =>  $data['edit'],
                    ':wsLogin'              =>  $data['wsLogin'],
                    ':wsPassword'           =>  $data['wsPassword'],
                    ':websiteId'            =>  $data['websiteId'],
                    ':customerIpAddress'    =>  $data['customerIpAddress'],
                    ':signkey'              =>  $data['signkey'],
                    ':formaction'           =>  $data['formaction'],
                    ':categoryId'           =>  $data['categoryId']
                ));
        }
    }
}
