<?php

namespace PP\MessageBundle\Entity;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository
{
    
    
    public function getCommonMessageThread($currentUserId, $targetUserId){
        $qb = $this->createQueryBuilder('m')
                        ->distinct(true)
                        ->leftJoin('m.author', 'ma')
                        ->leftJoin('m.target', 'mt')
                        ->where('(ma.id = :currentUserId AND mt.id = :targetUserId) OR (mt.id = :currentUserId AND ma.id = :targetUserId)')
                        ->setParameter('currentUserId', $currentUserId)
                        ->setParameter('targetUserId', $targetUserId)
                        ->leftJoin('m.messageThread', 'mThread')
                        ->select('mThread.id');
        try{
            $result = $qb
                    ->getQuery()
                    ->getSingleScalarResult();            
            return $result;
            
        } catch (\Doctrine\ORM\NoResultException $ex) {            
            return null;
        }       
    }
    
   
    
}