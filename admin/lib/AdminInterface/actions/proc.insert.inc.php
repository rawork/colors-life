<?php
    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class insertUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
            if (CUtils::_postVar('utype')) {
				if ($this->t->insertGlobals()) {
                    $path = $this->fullRef.'&action=s_update&id='.$GLOBALS['db']->getInsertID();
                    $path .= '&message='.urlencode('���������');
                } else {
                    $path = $this->fullRef.'&action=s_insert';
                    $path .= '&message='.urlencode('������ ����������');
                }
				header('location: '.$path);	
			} else {
				$this->messageAction($this->t->insertGlobals() ? '���������' : '������ ����������');
			}
        }
    }
?>