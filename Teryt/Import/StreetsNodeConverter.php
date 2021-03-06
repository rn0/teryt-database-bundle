<?php

/**
 * (c) FSi sp. z o.o <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\TerytDatabaseBundle\Teryt\Import;

use FSi\Bundle\TerytDatabaseBundle\Entity\Street;

class StreetsNodeConverter extends NodeConverter
{
    const PLACE_CHILD_NODE = 4;
    const ID_CHILD_NODE = 5;
    const TYPE_CHILD_NODE = 6;
    const NAME_CHILD_NODE = 7;
    const ADDITIONAL_NAME_CHILD_NODE = 8;

    public function convertToEntity()
    {
        $streetEntity = $this->createStreetEntity();
        $streetEntity->setName($this->getName())
            ->setAdditionalName($this->getAdditionalName())
            ->setType($this->getStreetType());

        return $streetEntity;
    }

    /**
     * @return \FSi\Bundle\TerytDatabaseBundle\Entity\Street
     */
    private function createStreetEntity()
    {
        $place = $this->getPlace();

        return $this->findOneBy('FSiTerytDbBundle:Street', array(
            'id' => $this->getStreetId(),
            'place' => $place
        )) ?: new Street($place, $this->getStreetId());
    }

    /**
     * @return string
     */
    private function getStreetId()
    {
        return (int) $this->node->col[self::ID_CHILD_NODE];
    }

    /**
     * @return string
     */
    private function getName()
    {
        return trim((string) $this->node->col[self::NAME_CHILD_NODE]);
    }

    /**
     * @return string
     */
    private function getAdditionalName()
    {
        $additionalName = trim((string) $this->node->col[self::ADDITIONAL_NAME_CHILD_NODE]);

        return $additionalName ?: null;
    }

    /**
     * @return string
     */
    private function getStreetType()
    {
        return (string) $this->node->col[self::TYPE_CHILD_NODE];
    }

    /**
     * @return \FSi\Bundle\TerytDatabaseBundle\Entity\Place
     */
    private function getPlace()
    {
        return $this->om->getRepository('FSiTerytDbBundle:Place')->findOneBy(array(
            'id' => (int) $this->node->col[self::PLACE_CHILD_NODE]
        ));
    }
}
