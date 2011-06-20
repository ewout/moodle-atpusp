<?php

class Output {

    public function pix_url($url) {
        global $CFG;
        return $CFG->wwwroot.'/pix/smartpix.php/standard/'.$url.'.gif';
    }

    public function header() {
        global $CFG, $PAGE2;
        $navigation = build_navigation(array(
                        array('name' => $PAGE2->title)));
        $meta = '';
        foreach ($PAGE2->requires->get_js() as $js){
            $meta .= '<script language="javascript" type="text/javascript" src="'.$CFG->wwwroot.$js.'"></script>';
        }
        print_header($PAGE2->title, $PAGE2->title, $navigation, '', $meta);
        return '';
    }

    public function heading($text, $level=2, $classes='main', $id = null) {
        print_heading($text, $level, $classes);
        return '';
    }

    public function footer() {
        print_footer();
        return '';
    }

    public function confirm ($message, $continue, $cancel) {
        $pageyes = substr($continue, 0, strpos($continue, '?'));
        $pageno = substr($cancel, 0, strpos($cancel, '?'));
        
        $explodeoptionsyes = explode('&', substr($continue, strpos($continue, '?') + 1));
        $explodeoptionsno = explode('&', substr($cancel, strpos($cancel, '?') + 1));

        $optionsyes = array();
        foreach ($explodeoptionsyes as $option){
            $optionsyes[substr($option, 0, strpos($option, '='))] = substr($option, strpos($option, '=') + 1);
        }
        $optionsno = array();
        foreach ($explodeoptionsno as $option){
            $optionsno[substr($option, 0, strpos($option, '='))] = substr($option, strpos($option, '=') + 1);
        }

        notice_yesno($message, $pageyes, $pageno, $optionsyes, $optionsno, 'get', 'get');
        return '';
    }

}
$GLOBALS['OUTPUT'] = new Output();

class Navbar {
    public function add($item) {
    } 
}

class Requires {
    private $js = array();

    public function js($url) {
        $this->js[] = $url;
    }
    public function get_js() {
        return $this->js;
    }
}

class Page {
    private $context;
    private $url;

    public $title = "";
    public $navbar;
    public $requires;

    public function Page() {
        $this->navbar = new Navbar();
        $this->requires = new Requires();
    }

    public function set_context($context) {
        $this->context = $context;
    }

    public function set_url($url) {
        $this->url = $url;
    }

    public function set_title($title) {
        $this->title = $title;
    }

    public function url_get_full() {
        return '';
    }

}
$GLOBALS['PAGE2'] = new Page();

class DB {

    private function convert_sql($sql, array $params=null) { 
        global $CFG;
        if (!empty($params)) {
            foreach ($params as $param) {
                $sql = preg_replace('/\?/', $param, $sql, 1);
            }
        }
        $sql = preg_replace('/{(\w*)}/',$CFG->prefix.'${1}', $sql);
        return $sql;
    }

    public function get_records($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        if (count($conditions)==1) {
            $value = current($conditions);
            $field = key($conditions);
            return get_records($table, $field, $value, $sort, $fields, $limitfrom, $limitnum);
        }
        //return null;
    }

    public function get_records_list($table, $field, array $pvalues, $sort='', $fields='*', $limitfrom=0, $limitnum=0) {
        $values = implode(',', $pvalues);
        return get_records_list($table, $field='', $values='', $sort='', $fields='*', $limitfrom, $limitnum);
    }

    public function get_fieldset_sql($sql, array $params=null) {
        return get_fieldset_sql($this->convert_sql($sql, $params));
    }
    
    public function get_fieldset_select($table, $return, $select, array $params=null) {
        return get_fieldset_select($table, $return, $this->convert_sql($select, $params));
    }

    public function get_records_sql($sql, array $params = null, $limitfrom = 0, $limitnum = 0) {
        return get_records_sql($this->convert_sql($sql, $params), $limitfrom, $limitnum);
    }

    public function delete_records($table, array $conditions=null) {
        $field1 = ''; $value1 = ''; $field2 = ''; $value2 = ''; $field3 = ''; $value3 = '';
        foreach ($conditions as $key=>$value) {
            if (empty($field1)) { $field1 = $key; $value1 = $value; }
            if (empty($field2)) { $field2 = $key; $value2 = $value; }
            if (empty($field3)) { $field3 = $key; $value3 = $value; }
        }
        return delete_records($table, $field1, $value1, $field2, $value2, $field3, $value3);
    }

}
$GLOBALS['DB'] = new DB();

?>
