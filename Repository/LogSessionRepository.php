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

use TechG\Bundle\SfBaseprjBundle\Entity\Log;
use TechG\Bundle\SfBaseprjBundle\Entity\LogSession;
use TechG\Bundle\SfBaseprjBundle\Repository\Base\BaseEntityRepository as BaseRepository;

class LogSessionRepository extends BaseRepository
{
    
    public function getActiveSession($session_active_duration = 90, $limit = false, $defaultValue = false, $returnObj = false, $returnOneElementAsArray = true)
    {
                                  
        $ora = new \DateTime();
        $ora_formattata = $ora->format('Y-m-d H:i:s');
        
        $sql = "SELECT
                         l.id                  
                  FROM
                         log_session l
                  WHERE 
                      (DATE_ADD(last_activity,INTERVAL $session_active_duration MINUTE) > '$ora_formattata') OR
                      (last_activity IS NULL AND DATE_ADD(log_date,INTERVAL $session_active_duration MINUTE) > '$ora_formattata')
                  ORDER BY
                       last_activity DESC, log_date DESC
                  
                  ".(($limit) ? $this->addLimit($limit) : '')."";      
                  
        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return $this->elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray);
          
    }     
    
    
}