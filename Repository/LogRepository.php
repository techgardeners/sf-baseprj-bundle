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
use TechG\Bundle\SfBaseprjBundle\Repository\Base\BaseEntityRepository as BaseRepository;

class LogRepository extends BaseRepository
{
    public function getRequestIdsBySessionId($cookie_id, $limit = false, $defaultValue = false, $returnObj = false, $returnOneElementAsArray = true)
    {
      
        $sql = "SELECT
                         l.request_id as id                 
                  FROM
                         log l
                  WHERE 
                      l.cookie_id = '$cookie_id'
                  GROUP BY
                      l.request_id
                  ORDER BY
                       l.log_date DESC
                  
                  ".(($limit) ? $this->addLimit($limit) : '')."";             

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return $this->elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray);
          
    }     

    public function getLogsByRequestId($request_id, $limit = false, $defaultValue = false, $returnObj = false, $returnOneElementAsArray = true)
    {
      
        $sql = "SELECT
                         l.*                  
                  FROM
                         log l
                  WHERE 
                      l.request_id = '$request_id'
                  ORDER BY
                       l.log_date
                  
                  ".(($limit) ? $this->addLimit($limit) : '')."";             

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return $this->elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray);
          
    }     

    public function getLogById($log_id, $limit = false, $defaultValue = false, $returnObj = false, $returnOneElementAsArray = true)
    {
      
        $sql = "SELECT
                         l.*                  
                  FROM
                         log l
                  WHERE 
                      l.id = $log_id
                  
                  ".(($limit) ? $this->addLimit($limit) : '')."";             

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return $this->elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray);
          
    }     

    public function linkLogToNewCookieId($tempCookieId, $cookieId)
    {      
        $sql = "UPDATE log SET cookie_id = '$cookieId' WHERE session_id = '$tempCookieId'; DELETE FROM log_session WHERE id = 'NO-COOKIE-SUPPORT' AND session_id = '$tempCookieId'";
        return $this->getEntityManager()->getConnection()->exec($sql);
    }     
}