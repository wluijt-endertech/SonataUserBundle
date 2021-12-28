<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\UserBundle\Entity;

use FOS\UserBundle\Doctrine\GroupManager as BaseGroupManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\UserBundle\Model\GroupManagerInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GroupManager extends BaseGroupManager implements GroupManagerInterface
{
    public function findGroupsBy(?array $criteria = null, ?array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/user-bundle 4.14, to be removed in 5.0.
     */
    public function getPager(array $criteria, int $page, int $limit = 10, array $sort = []): PagerInterface
    {
        $query = $this->repository
            ->createQueryBuilder('g')
            ->select('g');

        $fields = $this->objectManager->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!\in_array($field, $fields, true)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->class));
            }
        }

        if (0 === \count($sort)) {
            $sort = ['name' => 'ASC'];
        }

        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('g.%s', $field), strtoupper($direction));
        }

        $parameters = [];

        if (isset($criteria['enabled'])) {
            $query->andWhere('g.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        $query->setParameters($parameters);

        return Pager::create($query, $limit, $page);
    }
}
