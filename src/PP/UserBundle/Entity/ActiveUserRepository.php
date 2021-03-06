<?php

namespace PP\UserBundle\Entity;

/**
 * ActiveUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActiveUserRepository extends \Doctrine\ORM\EntityRepository
{
    public function getActiveUsers($limit){
                       
        $qb = $this->createQueryBuilder('au')  
                    ->leftJoin('au.user', 'u')                    
                    ->where('u.enabled = true')                    
                    ->addOrderBy('au.contributionNb', 'DESC')
                    ->setMaxResults($limit)                                        
        ; 
        return  $qb
                           ->getQuery()
                           ->getResult();        
    }
}
