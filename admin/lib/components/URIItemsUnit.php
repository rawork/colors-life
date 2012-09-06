<?php
    inc_lib('components/Unit.php');
    class URIItemsUnit extends Unit {
        function __construct($name, $props = array()) {
            parent::__construct($name, $props);
        }
        function getItem($field, $uri = false) {
            if (!$uri) {
                $uri = $_SERVER['REQUEST_URI'];
            }
            $this->tables['items']->select();
            while ($a = $this->tables['items']->getNextArray()) {
                $words = split(",", $a['words']);
                foreach ($words as $w) {
                    $w = trim($w);
                    if (!empty($w) && $uri == $w) {
                        return $a[$field];
                    }
                }
                $keywords = split(',', $a['keywords']);
                foreach ($keywords as $w) {
                    $w = trim($w);
                    if (!empty($w) && stristr($uri, $w)) {
                        return $a[$field];
                    }
                }
            }
        }
        function getTitle(){
			return '';
		}
        function getBody(){
			return '';
		}
    }

?>
