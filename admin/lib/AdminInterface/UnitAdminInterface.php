<?php
    inc_lib('components/Unit.php');
    class UnitAdminInterface {
        public $unit;
        public $title;
		public $icon;
        public $description;
        public $users;
        function __construct(&$unit, $title, $users) {
		global $PRJ_DIR, $THEME_REF;
            $this->unit = $unit;
            $this->title = $title;
            $this->users = $users;
        }
        function isAvailable() {
            return $GLOBALS['auth']->isSuperuser() || $this->users[CUtils::_sessionVar('user')] == 1;
        }
        function getBaseRef() {
            return '?state='.CUtils::_getVar('state', false, 'content').'&unit='.$this->unit->ocomponent['name'];
        }
        function createBaseTableRef($table) {
            return $this->getBaseRef().'&table='.$table;
        }
        function getBaseTableKey() {
            if (!CUtils::_getVar('table')) {
                if (sizeof($this->unit->tables) > 0) {
                    foreach ($this->unit->tables as $k => $v) {
                        $_GET['table'] = $k;
                        break;
                    }
                } else {
                    throw new Exception('Tables not exists');
                }
            }
            return CUtils::_getVar('table');
        }
        function getBaseTableRef() {
            return $this->createBaseTableRef($this->getBaseTableKey());
        }
        function getBaseTable() {
            return isset($this->unit->tables[$this->getBaseTableKey()]) ? $this->unit->tables[$this->getBaseTableKey()] : null;
        }
        function messageAction($msg, $path = '') {
            if (empty($path)) {
                $path = $this->getBaseTableRef();
            }
            header('location: '.$path.'&message='.urlencode($msg));
        }
        function getContent() {
            $action = CUtils::_getVar('action', false, 'index');
            if (inc_lib('AdminInterface/actions/proc.'.$action.'.inc.php')) {
	            $name = $action.'UnitAdminBody';
			} else {
				inc_lib('AdminInterface/actions/UnitAdminBody.php');
				$name = 'UnitAdminBody';
			}
			$body = new $name($this);
            return $body->getText();
        }
        
        function getMenuItems() {
		global $db;
            $ret = array();
            foreach ($this->unit->tables as $k => $v) {
                if (empty($v->props['is_hidden'])) {
                    $ret[] = array (
                        'ref' => $this->createBaseTableRef($k),
                        'name' => $v->title
                    );
                }
            }
			if ($GLOBALS['auth']->isSuperuser()) {
				if (sizeof($this->unit->dbparams) > 0) {
					$ret[] = array (
		            	'ref' => $this->getBaseRef().'&action=s_setting',
	                	'name' => 'Настройки'
	            	);
				}
			}
			if ($this->unit->ocomponent['name'] == 'config' && $GLOBALS['auth']->isSuperuser()) {
				$ret[] = array (
		           	'ref' => $this->getBaseRef().'&action=s_backup',
	               	'name' => 'Резервное копирование'
	            );
			}
			if ($this->unit->ocomponent['name'] == 'articles' && $GLOBALS['auth']->isSuperuser()) {
				$ret[] = array (
		           	'ref' => $this->getBaseRef().'&action=counttags',
	               	'name' => 'Расчет тегов'
	            );
			}
			if ($this->unit->ocomponent['name'] == 'maillist' && $GLOBALS['auth']->isSuperuser()) {
				$ret[] = array (
		           	'ref' => $this->getBaseRef().'&action=send',
	               	'name' => 'Отправка писем'
	            );
			}
			if (__PROCESSOR_VISIBLE) {
				if ($GLOBALS['auth']->isSuperuser() && empty($this->unit->ocomponent['is_admin'])) {
					$ret[] = array (
		            	'ref' => $this->getBaseRef().'&amp;action=s_methods',
	                	'name' => 'Методы'
            		);
				}
			}
            return $ret;
        }
		
		function getTableMenu() {
		global $smarty;
			$smarty->assign('tables', $this->getMenuItems());
			return $smarty->fetch('admin/submenu.tpl');
		}
		
        function getIndex() {
		global $smarty;
            $title = str_replace(' ', '&nbsp;', $this->title);
			$message = CUtils::_getVar('message');
            if (sizeof($menu = $this->getMenuItems()) > 0) {
				foreach ($menu as $m) {
					if ($this->getBaseTableRef() == $m['ref']) {
						$title .= ':&nbsp;'.$m['name'];
					}
				}
				if (CUtils::_getVar('action')) {
					switch (CUtils::_getVar('action')) {
    	              case 's_table':
        	            $title .= ':&nbsp;Настройка таблицы';break;
                	  case 's_insert':
            	       	$title .= ':&nbsp;Добавление';break;
                  	  case 's_update':
                        $title .= ':&nbsp;Редактирование';break;
                	}
				}
            }
           	$title .= !empty($message) ? ':&nbsp;'.$message : '';
			$smarty->assign('title', $title);
			$smarty->assign('body', $this->getContent());
			return $smarty->fetch('admin/mainbody.tpl');
        }
    }
?>