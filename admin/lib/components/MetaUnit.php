<?php
    inc_lib('components/URIItemsUnit.php');
    class MetaUnit extends URIItemsUnit {
        function __construct($props = array()) {
            parent::__construct('meta', $props);
        }
        function getMeta($uri = false) {
            return $this->getItem('meta', $uri);
        }
        function getTitle($uri = false) {
            return $this->getItem('title', $uri);
        }
    }
?>
