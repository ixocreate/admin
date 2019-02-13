<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Action\Api\Resource;

use Doctrine\Common\Collections\Criteria;
use Ixocreate\Admin\Response\ApiListResponse;
use Ixocreate\ApplicationHttp\Middleware\MiddlewareSubManager;
use Ixocreate\Contract\Resource\AdminAwareInterface;
use Ixocreate\Contract\Schema\Listing\ElementInterface;
use Ixocreate\Database\EntityManager\Factory\EntityManagerSubManager;
use Ixocreate\Database\Repository\Factory\RepositorySubManager;
use Ixocreate\Database\Repository\RepositoryInterface;
use Ixocreate\Entity\Entity\EntityInterface;
use Ixocreate\Resource\SubManager\ResourceSubManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\MiddlewarePipe;

final class IndexAction implements MiddlewareInterface
{
    /**
     * @var RepositorySubManager
     */
    private $repositorySubManager;

    /**
     * @var MiddlewareSubManager
     */
    private $middlewareSubManager;

    /**
     * @var ResourceSubManager
     */
    private $resourceSubManager;

    /**
     * @var EntityManagerSubManager
     */
    private $entitySubManager;

    public function __construct(
        RepositorySubManager $repositorySubManager,
        MiddlewareSubManager $middlewareSubManager,
        ResourceSubManager $resourceSubManager,
        EntityManagerSubManager $entitySubManager
    )
    {
        $this->repositorySubManager = $repositorySubManager;
        $this->middlewareSubManager = $middlewareSubManager;
        $this->resourceSubManager = $resourceSubManager;
        $this->entitySubManager = $entitySubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var AdminAwareInterface $resource */
        $resource = $this->resourceSubManager->get($request->getAttribute("resource"));

        $middlewarePipe = new MiddlewarePipe();

        if (!empty($resource->indexAction())) {
            /** @var MiddlewareInterface $action */
            $action = $this->middlewareSubManager->get($resource->indexAction());
            $middlewarePipe->pipe($action);
        }

        $middlewarePipe->pipe(new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($resource) {
            return $this->handleRequest($resource, $request, $handler);
        }));

        return $middlewarePipe->process($request, $handler);
    }

    private function handleRequest(AdminAwareInterface $resource, ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $listSchema = $resource->listSchema();

        /** @var RepositoryInterface $repository */
        $repository = $this->repositorySubManager->get($resource->repository());
        $criteria = new Criteria();

        /**
         * apply soft deletes
         * TODO: make this overridable so deleted items can be listed as well
         */
        if (\method_exists($repository->getEntityName(), 'deletedAt')) {
            $criteria->andWhere(Criteria::expr()->isNull('deletedAt'));
        }

        /**
         * extract limit, offset, filters and sorts from query string
         */
        //?sort[column1]=ASC&sort[column2]=DESC&filter[column1]=test&filter[column2]=foobar
        $queryParams = $request->getQueryParams();
        $sorting = null;
        $filterExpressions = [];
        foreach ($queryParams as $key => $value) {
            /**
             * TODO: why not use $key === 'sort' and $key === 'filter'? legacy code depending on it? -> TBD
             */
            if (\mb_substr($key, 0, 4) === "sort") {
                $sorting = [];
                foreach ($value as $sortName => $sortValue) {
                    if (!$listSchema->has($sortName)) {
                        continue;
                    }
                    $sorting[$sortName] = $sortValue;
                }
            } elseif (\mb_substr($key, 0, 6) === "filter") {
                foreach ($value as $filterName => $filterValue) {
                    if (!\is_string($filterValue)) {
                        continue;
                    }
                    if (!$listSchema->has($filterName)) {
                        continue;
                    }
                    /** @var ElementInterface $element */
                    $element = $listSchema->elements()[$filterName];
                    if (!$element->searchable()) {
                        continue;
                    }
                    $filterExpressions[] = $criteria::expr()->contains($element->name(), $filterValue);
                }
            } elseif ($key === "search" && \is_string($value)) {
                foreach ($listSchema->elements() as $element) {
                    if (!$element->searchable()) {
                        continue;
                    }
                    $filterExpressions[] = $criteria::expr()->contains($element->name(), $value);
                }
                continue;
            } elseif ($key === "offset") {
                $value = (int)$value;
                if (!empty($value)) {
                    $criteria->setFirstResult($value);
                }
                continue;
            } elseif ($key === "limit") {
                $value = (int)$value;
                if (!empty($value)) {
                    $criteria->setMaxResults(\min($value, 500));
                }
                continue;
            }
        }

        /**
         * apply collected filters
         */
        if (!empty($filterExpressions)) {
            $criteria->andWhere(Criteria::expr()->andX(...$filterExpressions));
        }

        /**
         * apply collected sorts
         */
        if (empty($sorting) && !empty($resource->listSchema()->defaultSorting())) {
            $criteria->orderBy([$resource->listSchema()->defaultSorting()['sorting'] => $resource->listSchema()->defaultSorting()['direction']]);
        } elseif (!empty($sorting)) {
            $criteria->orderBy($sorting);
        }

        $result = $repository->matching($criteria);
        $items = [];
        //TODO Collection
        /** @var EntityInterface $entity */
        foreach ($result as $entity) {
            $items[] = $entity->toPublicArray();
        }
        $count = $repository->count($criteria);
        return new ApiListResponse($resource, $items, ['count' => $count]);
    }
}
