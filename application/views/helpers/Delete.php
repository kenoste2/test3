<?php

class Zend_View_Helper_Delete extends Zend_View_Helper_Abstract {

    public function Delete($location, $var = false) {
        global $config;

        $functions = new Application_Model_CommonFunctions();
        $text = $functions->T("sure_c");
        if ($var) {
            $text .= " : {$var}";
        }

        $string = "<a onclick=\"event.cancelBubble = true; if(event.stopPropagation) { event.stopPropagation(); } if (confirm('{$text}')) { window.location='{$location}'; } \"><li class='fa fa-trash-o fa-fw'></li></a>";
        return $string;
    }

}

