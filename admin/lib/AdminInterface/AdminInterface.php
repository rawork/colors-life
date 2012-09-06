<?php
	inc_lib('AdminInterface/UnitAdminInterface.php');
	inc_lib('components/Unit.php');
    class AdminInterface {
        protected $units = array(); 
		protected $unit;
		protected $state;
		protected $smarty;
        public function __construct() {
			$this->setOptions();
			$this->setUnits();
        }
        
		protected function setOptions() {
			$this->unit = CUtils::_getVar('unit');
			$this->state = CUtils::_getVar('state', false, 'content');
			$this->smarty = $GLOBALS['smarty'];
			$this->smarty->assign('user', CUtils::_sessionVar('user'));
			$this->smarty->assign('state', $this->state);
			$this->smarty->assign('lang', CUtils::_sessionVar('lang', false, 'ru'));
			$this->smarty->assign('unit', $this->unit);
		}
		
		protected function setUnits() {
		global $PRJ_DIR, $THEME_REF;
			$components = $GLOBALS['rtti']->getComponents();
			if (sizeof($components) > 0) {
				foreach ($components as $u)
					if ($u['name'] == $this->unit)
						$this->addUnit($u, array(CUtils::_sessionVar('user') => 1));
				switch ($this->state) {
					case 'content': $stateLetter = 'C'; break;
					case 'settings': $stateLetter = 'A'; break;
					case 'service': $stateLetter = 'S'; break;
					default : $stateLetter = 'N';
				}
				$units = array();
				foreach ($components as $u) {
					if ($u['ctype'] == $stateLetter) {
						$basePath = $THEME_REF.'/img/module/';
						$units[] = array(
							'name' => $u['name'],
							'title' => $u['title'],
							'ref' => $this->getBaseRef($u['name']),
							'icon' => (file_exists($PRJ_DIR.$basePath.$u['name'].'.gif') ? $basePath.$u['name'] : $basePath.'folder').'.gif',
							'tablelist' => $u['name'] == $this->unit ? $this->getUnit($u['name'])->getTableMenu() : '',
							'current' => $u['name'] == $this->unit
						);	
					}
				}
				$this->smarty->assign('units', $units);
			} else {
				unset($_SESSION['user']);
				unset($_SESSION['ukey']);
				session_destroy();
				header('/admin/?error='.urlencode('Incorrect user settings. Check user rules.'));
			}	
		}
		
        protected function addUnit($u, $users) {
            global $PRJ_DIR, $THEME_REF;
			if (($u['ctype'] == 'S' || $u['name'] == 'meta' || $u['name'] == 'tree') && !inc_u($u['name'])) {
				CUtils::raiseError('Not exists component: '.$u['name'], ERROR_DIE);
			}
			$className = ucfirst($u['name']).'Unit';
			$unit = inc_u($u['name']) ? new $className() : new Unit($u['name']);
			$this->units[$u['name']] = new UnitAdminInterface($unit, $u['title'], $users);
        }
        
		public function getUnit($uname) {
			if (isset($this->units[$uname]) && $this->units[$uname]->isAvailable()){
               	return $this->units[$uname];
            } else {
                CUtils::raiseError('Not exists component: '.$uname, ERROR_DIE);
            }
        }
		
		public function getBaseRef($unitname) {
            return '?state='.$this->state.'&unit='.$unitname;
        }
		
        public function cron($period) {
            if (!empty($period)) {
                set_time_limit(0);
                echo 'Cron ('.$period.'):';
                foreach ($this->units as $u) {
                    echo ' '.$u->unit->ocomponent['name'];
                    $name = 'every'.$period;
                    $u->unit->$name();
                }
            } else {
                CUtils::raiseError('Cron params error', ERROR_DIE);
            }
        }
		
        public function show() {
			$version = $GLOBALS['LIB_VERSION'];
			$this->smarty->assign('bottom', !empty($this->unit) ? $this->getUnit($this->unit)->getIndex() : '');
			$this->smarty->assign('langs', $GLOBALS['db']->getItems('config_languages', 'SELECT * FROM config_languages'));
			$this->smarty->display('admin/main.tpl');
			ob_end_flush();
        }
    }
?>