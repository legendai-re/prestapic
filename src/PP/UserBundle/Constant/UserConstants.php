<?php

namespace PP\UserBundle\Constant;
/**
 * Description of Constants
 *
 * @author Olivier
 */
class UserConstants {
    
    const DISPLAY_REQUEST = 1;
    const DISPLAY_PROPOSITION = 2;
    
    public static function getForbidddenName(){
        return array("users", "_profiler", "help", "prestapic", "about", "guide", "policies");
    } 
    
    public static function getAllowedChar(){
        return array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9','-','_','.');
    }        
}
