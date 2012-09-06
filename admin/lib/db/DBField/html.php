<?php
    inc_lib('db/DBField/FieldType.php');
    class htmlFieldType extends FieldType {
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
        }
        
		public function getStatic() {
            return CUtils::cut_text(parent::getStatic());
        }
		
		public function getSearchInput() {
            return $this->getInput($this->getSearchValue(), $this->getSearchName(), true);
        }
		
		public function getSQL() {
            return $this->getName().' text NULL';
        }
		
        public function getInput($value = '', $name = '', $search = false) {
        global $PRJ_REF;
			if ($search){
				return '<input type="text" id="'.($name ? $name : $this->getName()).'" name="'.($name ? $name : $this->getName()).'" style="width:100%" value="'.htmlspecialchars($value).'">';
			}
            $value = !$value ? $this->dbValue : $value;
            $name = !$name ? $this->getName() : $name;
			$r_ = rand(0, getrandmax());
            $field = '<input type="checkbox" id="'.$r_.'" checked onClick="controlEditor(this, \''.$name.'\')"><label for="'.$r_.'" style="position:relative; top:-2px;">Редактор</label>';
			$field .= '<textarea class="mceEditor" id="'.$name.'" name="'.$name.'" rows="6" cols="40" style="height:300px;width:100%">
'.htmlspecialchars($value).'</textarea>';
            return $field;
        }
        
		public function getSQLValue($name = '') {
		global $PCRE_RES_REF;
            $text = $this->getValue($name);
			if (preg_match_all('/href="'.$PCRE_RES_REF.'\/[\S]*\.(jpg|gif|png)+"/', $text, $matches)){
				foreach ($matches[0] as $m){
					$tmp = substr($m, 6, strlen($m)-7);
					$width = 50;
					$height = 50;
					if (file_exists($_SERVER['DOCUMENT_ROOT'].$tmp)){
						$size = getImagesize($_SERVER['DOCUMENT_ROOT'].$tmp);
						$width = $size[0];
						$height = $size[1];					
					}
					$text = str_replace($m, 'href="#" onClick="newWin(\''.$tmp.'\','.$width.','.$height.'); return false;"',$text);
				}
			}
			if (preg_match_all('/href="'.$PCRE_RES_REF.'\/[\S]*\.(html)"/', $text, $matches)){
				preg_replace('/onclick=".+; return false;"/', '', $text);
				foreach ($matches[0] as $m){
					$tmp = substr($m, 6, strlen($m)-7);
					$width = 640;
					$height = 480;
					$text = str_replace($m, 'href="#" onClick="newWinHtml(\''.$tmp.'\','.$width.','.$height.'); return false;"',$text);
				}
			}
            return addslashes(str_replace('&amp;','&',$text));
        }
    }
?>