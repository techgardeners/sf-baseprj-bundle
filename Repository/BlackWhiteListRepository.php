<?php
/*
 * This file is part of the SfBaseprjBundle project
 *
 * (c) Roberto Beccaceci <roberto@beccaceci.it>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TechG\Bundle\SfBaseprjBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;

use TechG\Bundle\SfBaseprjBundle\Entity\BlackWhiteList;
use TechG\Bundle\SfBaseprjBundle\Repository\Base\BaseEntityRepository as BaseRepository;

class BlackWhiteListRepository extends BaseRepository
{

    public function isInList($list, $data, $type = BlackWhiteList::DATA_TYPE_IP, $origin = null, $enabled = true)
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
     
        $qb->select('count(bwlist.id)');
        $qb->from('TechGSfBaseprjBundle:BlackWhiteList','bwlist')
            ->where('bwlist.data = :data')
            ->andWhere('bwlist.enabled = :enabled')
            ->andWhere('bwlist.list = :list')
            ->andWhere('bwlist.type = :type');

        $qb->setParameter('data', $data)
           ->setParameter('enabled', $enabled)
           ->setParameter('list', $list)
           ->setParameter('type', $type);                    
        
        if (!is_null($origin)) {
            $qb->andWhere('bwlist.origin = :origin')    
               ->setParameter('origin', $origin);            
        }        
        
        $count = $qb->getQuery()->getSingleScalarResult();
        
        return ($count > 0);
    }  
    
}