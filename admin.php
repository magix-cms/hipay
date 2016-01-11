<?php
class plugins_hipay_admin extends DBHipay
{
    protected $header, $template, $message;
    public static $notify = array('plugin' => 'true');
    public $getlang, $plugin, $edit, $id, $getpage, $mailhack,$pwaccount,$setaccount,$setmarchantsiteid,$mailcart,$setcategory,$signkey,$formaction;

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
        if (magixcjquery_filter_request::isPost('mailhack')) {
            $this->mailhack = magixcjquery_form_helpersforms::inputClean($_POST['mailhack']);
        }
        if (magixcjquery_filter_request::isPost('pwaccount')) {
            $this->pwaccount = magixcjquery_form_helpersforms::inputClean($_POST['pwaccount']);
        }
        if (magixcjquery_filter_request::isPost('setaccount')) {
            $this->setaccount = magixcjquery_form_helpersforms::inputClean($_POST['setaccount']);
        }
        if (magixcjquery_filter_request::isPost('setmarchantsiteid')) {
            $this->setmarchantsiteid = magixcjquery_form_helpersforms::inputClean($_POST['setmarchantsiteid']);
        }
        if (magixcjquery_filter_request::isPost('mailcart')) {
            $this->mailcart = magixcjquery_form_helpersforms::inputClean($_POST['mailcart']);
        }
        if (magixcjquery_filter_request::isPost('setcategory')) {
            $this->setcategory = magixcjquery_form_helpersforms::inputClean($_POST['setcategory']);
        }
        if (magixcjquery_filter_request::isPost('signkey')) {
            $this->signkey = magixcjquery_form_helpersforms::inputClean($_POST['signkey']);
        }
        if (magixcjquery_filter_request::isPost('formaction')) {
            $this->formaction = magixcjquery_form_helpersforms::inputClean($_POST['formaction']);
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
     * Prépare les données utilisateur
     * @param $id
     * @return array
     */
    private function setData($id){
        $data = parent::selectOne($id);
        return array(
            'mailhack'              =>  $data['mailhack'],
            'pwaccount'             =>  $data['pwaccount'],
            'setaccount'            =>  $data['setaccount'],
            'setmarchantsiteid'     =>  $data['setmarchantsiteid'],
            'mailcart'              =>  $data['mailcart'],
            'setcategory'           =>  $data['setcategory'],
            'signkey'               =>  $data['signkey'],
            'formaction'            =>  $data['formaction']
        );
    }

    /**
     * Assign table data
     */
    private function getData(){
        $data = $this->setData($this->getlang);
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
            if (isset($this->mailhack)) {
                $control = parent::selectOne();
                $this->save(
                    array(
                        'edit'                  =>  $control['idhipay'],
                        'mailhack'              =>  $this->mailhack,
                        'pwaccount'             =>  $this->pwaccount,
                        'setaccount'            =>  $this->setaccount,
                        'setmarchantsiteid'     =>  $this->setmarchantsiteid,
                        'mailcart'              =>  $this->mailcart,
                        'setcategory'           =>  $this->setcategory,
                        'signkey'               =>  $this->signkey,
                        'formaction'            =>  $this->formaction
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
                'name'=>'fa fa-credit-card'
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
            $sql = 'INSERT INTO mc_plugins_hipay (mailhack,pwaccount,setaccount,setmarchantsiteid,mailcart,setcategory,signkey,formaction)
		    VALUE(:mailhack,:pwaccount,:setaccount,:setmarchantsiteid,:mailcart,:setcategory,:signkey,:formaction)';
            magixglobal_model_db::layerDB()->insert($sql,
                array(
                    ':mailhack'              =>  $data['mailhack'],
                    ':pwaccount'             =>  $data['pwaccount'],
                    ':setaccount'            =>  $data['setaccount'],
                    ':setmarchantsiteid'     =>  $data['setmarchantsiteid'],
                    ':mailcart'              =>  $data['mailcart'],
                    ':setcategory'           =>  $data['setcategory'],
                    ':signkey'               =>  $data['signkey'],
                    ':formaction'            =>  $data['formaction']
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
            SET mailhack=:mailhack,pwaccount=:pwaccount,setaccount=:setaccount,setmarchantsiteid=:setmarchantsiteid,
            mailcart=:mailcart,setcategory=:setcategory,signkey=:signkey,formaction=:formaction
            WHERE idhipay=:edit';
            magixglobal_model_db::layerDB()->update($sql,
                array(
                    ':edit'	                =>  $data['edit'],
                    ':mailhack'             =>  $data['mailhack'],
                    ':pwaccount'            =>  $data['pwaccount'],
                    ':setaccount'           =>  $data['setaccount'],
                    ':setmarchantsiteid'    =>  $data['setmarchantsiteid'],
                    ':mailcart'             =>  $data['mailcart'],
                    ':setcategory'          =>  $data['setcategory'],
                    ':signkey'              =>  $data['signkey'],
                    ':formaction'           =>  $data['formaction']
                ));
        }
    }
}
