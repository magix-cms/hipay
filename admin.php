<?php
include_once ('db.php');
class plugins_hipay_admin extends plugins_hipay_db
{
    public $edit, $action, $tabs;
    protected $controller,$data,$template, $message, $plugins, $xml, $sitemap,$modelLanguage,$collectionLanguage,$header;
    
    public $getlang, $plugin, $id, $getpage, $wsLogin,$wsPassword,$websiteId,$customerIpAddress,$signkey,$formaction,$categoryId, $getWebsiteId, $category,$direct;

    /**
     * constructeur
     */
    public function __construct()
    {
        $this->template = new backend_model_template();
        $this->plugins = new backend_controller_plugins();
        $formClean = new form_inputEscape();
        $this->message = new component_core_message($this->template);
        $this->data = new backend_model_data($this);
        $this->header = new http_header();
        
        // Global

        if (http_request::isGet('edit')) {
            $this->edit = $formClean->numeric($_GET['edit']);
        }
        if (http_request::isGet('action')) {
            $this->action = $formClean->simpleClean($_GET['action']);
        } elseif (http_request::isPost('action')) {
            $this->action = $formClean->simpleClean($_POST['action']);
        }

        if (http_request::isGet('tab')) {
            $this->tab = $formClean->simpleClean($_GET['tab']);
        }

        if (http_request::isGet('id')) {
            $this->id = (integer)$formClean->numeric($_GET['id']);
        }
        // POST
        if (http_request::isPost('wsLogin')) {
            $this->wsLogin = $formClean->simpleClean($_POST['wsLogin']);
        }
        if (http_request::isPost('wsPassword')) {
            $this->wsPassword = $formClean->simpleClean($_POST['wsPassword']);
        }
        if (http_request::isPost('websiteId')) {
            $this->websiteId = $formClean->simpleClean($_POST['websiteId']);
        }
        if (http_request::isPost('customerIpAddress')) {
            $this->customerIpAddress = $formClean->simpleClean($_POST['customerIpAddress']);
        }
        if (http_request::isPost('signkey')) {
            $this->signkey = $formClean->simpleClean($_POST['signkey']);
        }
        if (http_request::isPost('formaction')) {
            $this->formaction = $formClean->simpleClean($_POST['formaction']);
        }
        if (http_request::isPost('categoryId')) {
            $this->categoryId = $formClean->simpleClean($_POST['categoryId']);
        }
        if (http_request::isPost('direct')) {
            $this->direct = $formClean->simpleClean($_POST['direct']);
        }

        /*if(http_request::isGet('websiteId')){
            $this->getWebsiteId = $formClean->simpleClean($_GET['websiteId']);
        }*/
        if(http_request::isGet('category')){
            $this->category = $formClean->simpleClean($_GET['category']);
        }
    }

	/**
	 * Method to override the name of the plugin in the admin menu
	 * @return string
	 */
	public function getExtensionName()
	{
		return $this->template->getConfigVars('hipay_plugin');
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

        $object = simplexml_load_string($this->setSoapCategory($data), null, LIBXML_NOCDATA);

        /*if($data['getWebsiteId']){
            if(isset($object->categoriesList)){
                foreach ($object->children() as $key) {

                    foreach ($key->children() as $category => $value) {

                        $parseData[strval($value['id'])]= strval($value);
                    }
                }

                foreach($parseData as $key => $value){
                    $setData[] = array(
                        'id'    =>  $key,
                        'name'  =>  $value
                    );
                }
                $newData = "true";//json_encode(array("true",$setData));
            }else{
                $newData = "false";
            }
        }else{*/
            $parseData = array();
            $newData = array();
            if(isset($object->categoriesList)){
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
            }else{
                $newData = NULL;
            }
        //}
        return $newData;
    }

    /**
     * @return mixed
     */
    private function getCategory($getWebsiteId = false, $category = false){

        $config = $this->getItems('root',NULL,'one',false);
        /*if($getWebsiteId){
            $websiteId = $getWebsiteId;
        }else{
            if($category){
                $websiteId = $category;
            }else{
                $websiteId = $config['websiteId'];
            }
        }*/

        //if($config['websiteId'] != null && $config['formaction'] != null){

            if ($config['formaction'] === 'test') {
                $urlOrder = 'https://test-ws.hipay.com/soap/payment-v2?wsdl';
                $urlCategory = 'https://test-payment.hipay.com/order/list-categories/id/';
            } elseif ($config['formaction'] === 'production') {
                $urlOrder = 'https://ws.hipay.com/soap/payment-v2?wsdl';
                $urlCategory = 'https://payment.hipay.com/order/list-categories/id/';
            }
            return $this->setCategory(
                array(
                    'url' => $urlCategory . $config['websiteid'],
                    'debug' => false,
                    'getWebsiteId' => $config['websiteid']
                )
            );
       // }
    }

    /**
     * @param $data
     * @throws Exception
     */
    private function upd($data)
    {
        switch ($data['type']) {
            case 'config':
                parent::update(
                    array(
                        //'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }

    /**
     * Update data
     * @param $data
     * @throws Exception
     */
    private function add($data)
    {
        switch ($data['type']) {
            case 'newConfig':
                parent::insert(
                    array(
                        //'context' => $data['context'],
                        'type' => $data['type']
                    ),
                    $data['data']
                );
                break;
        }
    }


    private function save(){
        $setData = $this->getItems('root',NULL,'one',false);
        if($setData['id_hipay']){

            $this->upd(
                array(
                    'type' => 'config',
                    'data' => array(
                        'wsLogin'              =>  $this->wsLogin,
                        'wsPassword'           =>  $this->wsPassword,
                        'websiteId'            =>  $this->websiteId,
                        //'customerIpAddress'    =>  $this->customerIpAddress,
                        'signkey'              =>  $this->signkey,
                        'formaction'           =>  $this->formaction,
                        'categoryId'           =>  isset($this->categoryId) ? $this->categoryId : NULL,
                        'id'                   =>  $setData['id_hipay'],
                        'direct'               =>  isset($this->direct) ? 1 : 0
                    )
                )
            );
        }else{
            $this->add(
                array(
                    'type' => 'newConfig',
                    'data' => array(
                        'wsLogin'              =>  $this->wsLogin,
                        'wsPassword'           =>  $this->wsPassword,
                        'websiteId'            =>  $this->websiteId,
                        //'customerIpAddress'    =>  $this->customerIpAddress,
                        'signkey'              =>  $this->signkey,
                        'formaction'           =>  $this->formaction,
                        'categoryId'           =>  NULL,
                        'direct'               =>  isset($this->direct) ? 1 : 0
                    )
                )
            );
        }
        $this->message->json_post_response(true, 'update');
    }
    /**
     * Execute plugin
     */
    public function run()
    {
        if(isset($this->action)) {
            switch ($this->action) {
                case 'edit':
                    $this->save();
                    break;
            }
        }else{
            $data = $this->getItems('root',NULL,'one',false);
            $this->template->assign('hipay', $data);
            $this->template->assign('hipayCategory',$this->getCategory());
            $this->template->display('index.tpl');
        }
    }
}