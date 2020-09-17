<?php

namespace Mailery\Subscriber\Filter;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Mailery\Subscriber\Entity\Group;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Equals;
use Mailery\Widget\Search\Form\SearchForm;

final class SubscriberFilter implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private array $filters = [];

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->filters);
    }

    /**
     * @param Group $group
     * @return self
     */
    public function withGroup(Group $group): self
    {
        $new = clone $this;
        $new->filters[] = new Equals('groups.id', $group->getId());

        return $new;
    }

    /**
     * @param SearchForm $searchForm
     * @return self
     */
    public function withSearchForm(SearchForm $searchForm): self
    {
        $new = clone $this;

        if (($searchBy = $searchForm->getSearchBy()) !== null) {
            $new->filters[] = $searchBy;
        }

        return $new;
    }

    /**
     * @return self
     */
    public function withActive(): self
    {
        $new = clone $this;
        $new->filters[] = (new All())->withFiltersArray([
            ['=', 'confirmed', true],
            ['=', 'unsubscribed', false],
            ['=', 'bounced', false],
            ['=', 'complaint', false],
        ]);

        return $new;
    }

    /**
     * @return self
     */
    public function withUnconfirmed(): self
    {
        $new = clone $this;
        $new->filters[] = (new All())->withFiltersArray([
            ['=', 'confirmed', false],
        ]);

        return $new;
    }

    /**
     * @return self
     */
    public function withUnsubscribed(): self
    {
        $new = clone $this;
        $new->filters[] = (new All())->withFiltersArray([
            ['=', 'unsubscribed', true],
        ]);

        return $new;
    }

    /**
     * @return self
     */
    public function withBounced(): self
    {
        $new = clone $this;
        $new->filters[] = (new All())->withFiltersArray([
            ['=', 'bounced', true],
        ]);

        return $new;
    }

    /**
     * @return self
     */
    public function withComplaint(): self
    {
        $new = clone $this;
        $new->filters[] = (new All())->withFiltersArray([
            ['=', 'complaint', true],
        ]);

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return (new All(...$this->filters))->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getOperator(): string
    {
        return 'and';
    }
}
