<?php

namespace PP\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PPUserBundle extends Bundle
{
    public function getParent()
    {
            return 'FOSUserBundle';
    }
}
