<?php
class plugins_hipay_db
{
    /**
     * @param $config
     * @param bool $params
     * @return mixed|string|null
     * @throws Exception
     */
    public function fetchData($config, $params = false)
    {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';

        if (is_array($config)) {
            if ($config['context'] === 'all') {
                switch ($config['type']) {
                    case 'data':
                        $sql = 'SELECT hp.* FROM mc_hipay AS hp';
                        break;
                    case 'contacts':
                    $sql = 'SELECT p.id_contact, p.mail_contact
								FROM mc_contact AS p
								JOIN mc_contact_content AS c USING(id_contact)
								JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
								WHERE lang.iso_lang = :lang
								GROUP BY p.id_contact';
                    break;
                }

                return $sql ? component_routing_db::layer()->fetchAll($sql, $params) : null;
            }
            elseif ($config['context'] === 'one') {
                switch ($config['type']) {
                    case 'root':
                        $sql = 'SELECT * FROM mc_hipay ORDER BY id_hipay DESC LIMIT 0,1';
                        break;
                }

                return $sql ? component_routing_db::layer()->fetch($sql, $params) : null;
            }
        }
    }
    /**
     * @param $config
     * @param array $params
     * @throws Exception
     */
    public function insert($config, $params = array())
    {
        if (is_array($config)) {
            $sql = '';

            switch ($config['type']) {
                case 'newConfig':

                    $sql = 'INSERT INTO mc_hipay (wsLogin,wsPassword,websiteId,signkey,formaction,categoryId,direct)
		            VALUE(:wsLogin,:wsPassword,:websiteId,:signkey,:formaction,:categoryId,:direct)';

                    break;
            }

            if($sql !== '') component_routing_db::layer()->insert($sql,$params);
        }
    }

    /**
     * @param $config
     * @param array $params
     * @throws Exception
     */
    public function update($config, $params = array())
    {
        if (is_array($config)) {
            $sql = '';

            switch ($config['type']) {
                case 'config':
                    $sql = 'UPDATE mc_hipay
                    SET wsLogin=:wsLogin,
                        wsPassword=:wsPassword,
                        websiteId=:websiteId,
                        signkey=:signkey,
                        formaction=:formaction,
                        categoryId=:categoryId,
                        direct=:direct
                    WHERE id_hipay=:id';
                    break;
            }

            if($sql !== '') component_routing_db::layer()->update($sql,$params);
        }
    }
}
?>