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

use TechG\Bundle\SfBaseprjBundle\Entity\Language;
use TechG\Bundle\SfBaseprjBundle\Repository\Base\BaseEntityRepository as BaseRepository;

class LanguageRepository extends BaseRepository
{
    
    public function getEnabledLanguage($limit = false, $defaultValue = false, $returnObj = false, $returnOneElementAsArray = true)
    {
      
        $sql = "SELECT
                         l.*
                  
                  FROM
                         language l

                  WHERE 
                          l.enabled = 1
                  ".(($limit) ? $this->addLimit($limit) : '')."";             

        $result = $this->getEntityManager()->getConnection()->fetchAll($sql);

        return $this->elaborateResult($result, $limit, $defaultValue, $returnObj, $returnOneElementAsArray);
          
    } 
    
}