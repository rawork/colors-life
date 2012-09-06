<?php

	inc_lib('components/Unit.php'); 
    class MaillistUnit extends Unit {
        function __construct($props = array()) {
            parent::__construct('maillist', $props);
        }

        function getForm() {
		global $PRJ_REF, $PRJ_DIR;
			$this->smarty->assign('prj_ref', $PRJ_REF);
			$this->smarty->assign('prj_ref', $PRJ_REF);
            return $this->smarty->fetch('service/'.$this->props['lang'].'/subscribe.form.tpl');
        }
		
        function everyMin() {
		global $ADMIN_EMAIL, $PRJ_DIR;
            $this->tables['lists']->selectWhere('TO_DAYS(date) <= TO_DAYS(NOW())');
            if ($a = $this->tables['lists']->getNextArray()) {
                
				inc_lib('libmail.php');
                $m = new Mail();
                $m->From($ADMIN_EMAIL);
                $m->Subject($a['subj']);
                $m->SetCharset('windows-1251');
                $m->Html($a['body']);
                if (is_file($PRJ_DIR.$a['file'])) {
                    $m->AttachFile($PRJ_DIR.$a['file']);
                }
                $this->tables['users']->select(array('where' => "is_active='on'"));
                while ($a2 = $this->tables['users']->getNextArray()) {
                    $m->To(array($a2['email']));
                    $m->Send();
                }
				$this->tables['lists']->delete('id='.$a['id']);
                @unlink($PRJ_DIR.$a['file']);
            }
        }
    }
?>
