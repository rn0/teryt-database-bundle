<?php

/**
 * (c) FSi sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\TerytDatabaseBundle\Model\Territory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Province extends Territory
{
    /**
     * @var Collection|District[]
     */
    protected $districts;

    /**
     * @param int $code
     */
    public function __construct($code)
    {
        parent::__construct($code);
        $this->districts = new ArrayCollection();
    }

    /**
     * @return Collection|District[]
     */
    public function getDistricts()
    {
        return $this->districts;
    }
}
