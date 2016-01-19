<?php

namespace PP\RequestBundle\Entity;

/**
 * PopularRequestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PopularRequestRepository extends \Doctrine\ORM\EntityRepository
{    
    public function getPopularImageRequests($limit){
        
        $qb = $this->createQueryBuilder('pir')
                ->distinct(true)
                ->leftJoin('pir.imageRequest', 'ir')
                ->where('ir.enabled = true')
                ->leftJoin('ir.author', 'irA')                
                ->andWhere('irA.enabled = true')                
                ->setMaxResults($limit)
        ; 
        return  $qb
                ->getQuery()
                ->getResult(); 
    }
    
}