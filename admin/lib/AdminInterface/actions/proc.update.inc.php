<?php

    inc_lib('AdminInterface/actions/UnitAdminBody.php');
    class updateUnitAdminBody extends UnitAdminBody {
        function __construct(&$unitAdminInterface) {
            parent::__construct($unitAdminInterface);
        }
        function getText() {
			if (CUtils::_postVar('utype')) {
				$path = str_replace(stristr($_SERVER['HTTP_REFERER'], '&message'), '', $_SERVER['HTTP_REFERER']);
				header('location: '.$path.'&message='.($this->t->updateGlobals() ? urlencode('Обновлено') : urlencode('Ошибка обновления')));	
			} else {
				$this->messageAction($this->t->updateGlobals() ? 'Обновлено' : 'Ошибка обновления');
			}
        }
		
    }

?>