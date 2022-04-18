<?php

namespace Mailery\Subscriber\Filter;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\All;
use Mailery\Widget\Search\Form\SearchForm;

class GroupFilter implements FilterInterface
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
