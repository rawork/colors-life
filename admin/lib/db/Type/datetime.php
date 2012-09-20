<?php
    inc_lib('db/Type/FieldType.php');
    class datetimeFieldType extends FieldType {
        protected $arr;
		protected $year, $month, $day, $time; 
        public function __construct(&$props, $dbArray = null) {
            parent::__construct($props, $dbArray);
            // немного уменьшаем геморой...
            $this->arr = array(
                'year' => 4,
                'month' => 2,
                'day' => 2,
                'time' => 8
            );
        }
		/*** implementation */
        public function getSQL() {
            return $this->getName().' datetime NOT NULL default \'0000-00-00 00:00:00\'';
        }
        
        public function value2YMD($value = '') {
            if (!empty($value)) {
                $this->year = substr($value, 0, $this->arr['year']);
                $this->month = substr($value, 5, $this->arr['month']);
                $this->day = substr($value, 8, $this->arr['day']);
                $this->time = substr($value, 11, $this->arr['time']);
            } else {
                $ts = time();
                $this->year = date('Y', $ts);
                $this->month = date('m', $ts);
                $this->day = date('d', $ts);
                $this->time = date('H:i:s', $ts);
            }
        }
        
        public function getSQLValue($name = '') {
			if (trim($this->getValue($name))) {
				return "STR_TO_DATE('".$this->getValue($name)."','%d.%m.%Y %H:%i:%s')";
			} else {
				return "'0000-00-00 00:00:00'";
			}
        }
		
        public function getStatic() {
            $this->value2YMD($this->dbValue);
            return $this->day.'.'.$this->month.'.'.$this->year.' '.$this->time;
        }

        public function getInput($value = '', $name = '') {
            return $this->dateFieldType_getInput(($name ? $name : $this->getName()), $this->dbValue);
        }
        
		public function getSearchInput() {
			if ($date = $this->getSearchValue('beg')) {
				$date_beg = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2).' '.substr($date,11,8);
			} else {
				$date_beg = '';//date('Y-m-d').' 00:00:00';
			}
			if ($date = $this->getSearchValue('end')) {
				$date_end = substr($date,6,4).'-'.substr($date,3,2).'-'.substr($date,0,2).' '.substr($date,11,8);
			} else {
				$date_end = '';//date('Y-m-d').' 23:59:59';
			}
			return 'c '.$this->dateFieldType_getInput(parent::getSearchName('beg'), $date_beg, false).' по '.$this->dateFieldType_getInput(parent::getSearchName('end'), $date_end, false).' <a href="#" onClick="emptyDateSearch(\''.parent::getSearchName().'\')">Обнулить</a>';
        }

        public function getSearchSQL() {
			$ret = '';
			if ($date = $this->getSearchValue('beg')) {
				$ret .= ($ret ? ' AND ' : '').$this->getName().">=STR_TO_DATE('$date','%d.%m.%Y %H:%i:%s')";
			}
			if ($date = $this->getSearchValue('end')) {
				$ret .= ($ret ? ' AND ' : '').$this->getName()."<=STR_TO_DATE('$date','%d.%m.%Y %H:%i:%s')";
			}
			return $ret;	
        }
		
        public function getSearchURL() {
			$ret = '';
			if (parent::getSearchURL('beg')) {
				$ret = parent::getSearchURL('beg');
			}
			if (parent::getSearchURL('end')) {
				$ret = ($ret ? '&' : '').parent::getSearchURL('end');
			}
            return $ret;
        }
		
        public function dateFieldType_getInput($name, $value = '', $insertValue = true) {
		global $THEME_REF;
            if ($value || $insertValue) {
                $this->value2YMD($value);
				$date = $this->day.'.'.$this->month.'.'.$this->year.' '.$this->time;
			} else {
				$date = '';	
			}	
			return '<input readonly value="'.$date.'" name="'.$name.'" id="'.$name.'">&nbsp;<img id="trigger_'.$name.'" style="cursor: pointer; border: 0px;" src="'.$THEME_REF.'/img/calendar.gif"><script type="text/javascript">setupCalendar(\''.$name.'\', \'%H:%M:00\')</script>';
        }
    }
?>