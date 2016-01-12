<?php

namespace PP\RequestBundle\Constant;
/**
 * Description of Constants
 *
 * @author Olivier
 */
class Constants {
    const DISPLAY_REQUEST = 1;
    const DISPLAY_PROPOSITION = 2;
    const DISPLAY_REQUEST_PENDING = 3;
    const DISPLAY_REQUEST_CLOSED = 4;
    
    const REQUEST_PENDING = 1;
    const REQUEST_CLOSED = 2;
    
    const REQUEST_PER_PAGE =  10;
    const PROPOSITION_PER_PAGE =  6;
    const PROPOSITION_PER_HOME_PAGE =  10;
    const PROPOSITION_PER_GALLERY_PAGE =  10;
    const ORDER_BY_DATE =  1;
    const ORDER_BY_UPVOTE =  2;
    const ORDER_BY_INTEREST =  3;
    
    const USER_PER_PAGE = 10;
}
