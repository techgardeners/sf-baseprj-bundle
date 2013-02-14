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

use TechG\Bundle\SfBaseprjBundle\Entity\Setting;
use TechG\Bundle\SfBaseprjBundle\Repository\Base\BaseEntityRepository as BaseRepository;

class SettingRepository extends BaseRepository
{

    public function loadAll()
    {

        $qb = $this->getEntityManager()->createQueryBuilder();
     
        $qb->select('s');
        $qb->from('TechGSfBaseprjBundle:Setting','s');        

        $result = $qb->getQuery()->getArrayResult();
        $settingCache = null;
        
        // Se hanno un user_id settato allora li inserisco tra quelli utenti, altrimenti li considero come dei global
        foreach ($result as $setting) {
            if ($setting['user'] > 0) {
                if (array_key_exists($setting['user'], $settingCache['users'])) {
                    $settingCache['users'][$setting['user']] = array();
                }
                
                $settingCache['users'][$setting['user']][$setting['key']] = $setting['value'];
                    
            } else {
                $settingCache['global'][$setting['key']] = $setting['value'];               
            }
        }
        
        return $settingCache;
    }  
    
}