<?php

namespace PP\NotificationBundle\DateAgo;

/**
 * Description of PPDateAgo
 *
 * @author Olivier
 */
class PPDateAgo {
    
    public function __construct(){

    }
	/**
	* Vérifie si le texte est un spam ou non
	*
	* @param string $text
	* @return bool
	*/
	public function ago($datetime){
		$interval = date_create('now')->diff( $datetime );
                $suffix = ( $interval->invert ? ' ago' : '' );
                if ( $v = $interval->y >= 1 ) return $this->pluralize( $interval->y, 'year' ) . $suffix;
                if ( $v = $interval->m >= 1 ) return $datetime->format('M d');
                if ( $v = $interval->d > 1 )  return $datetime->format('M d');
                if ( $v = $interval->d == 1 ) return 'yesterday';
                if ( $v = $interval->h >= 1 ) return $this->pluralize( $interval->h, 'hour' ) . $suffix;
                if ( $v = $interval->i >= 1 ) return $this->pluralize( $interval->i, 'minute' ) . $suffix;
                return 'now';
	}
	
        private function pluralize( $count, $text ) 
        { 
            return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
        }
        	 

	public function getName()
	{
		return 'OCAntispam';
	}
    
}
