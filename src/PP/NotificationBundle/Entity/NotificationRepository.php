<?php

namespace PP\NotificationBundle\Entity;

/**
 * NotificationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NotificationRepository extends \Doctrine\ORM\EntityRepository
{
    public function getNotifications($userId, $limit, $page){
        
        $qb = $this->createQueryBuilder('n')
                        ->distinct(true)   
                        ->leftJoin('n.notificationThread', 'nThread') 
                        ->leftJoin('nThread.user', 'u') 
                        ->where('u.id = :userId')
                        ->setParameter('userId', $userId)                                                                                        
                        ->orderBy('n.createDate', 'DESC')
                        ->setFirstResult(($page-1) * $limit)
                        ->setMaxResults($limit);
        
         try{
               return  $qb
                           ->getQuery()
                           ->getResult();
        }catch(\Doctrine\ORM\NoResultException $e){
               return null;
        }
    }
    
    public function getNotificationsNotViewed($userId){
        
        $qb = $this->createQueryBuilder('n')                        
                        ->distinct(true)   
                        ->leftJoin('n.notificationThread', 'nThread') 
                        ->leftJoin('nThread.user', 'u') 
                        ->where('u.id = :userId')
                        ->setParameter('userId', $userId)                        
                        ->andWhere('n.isViewed = false');
        
         try{
               return  $qb
                           ->getQuery()
                           ->getResult();
        }catch(\Doctrine\ORM\NoResultException $e){
               return null;
        }
    }
    
}