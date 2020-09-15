<?php

namespace Mailery\Subscriber\Filter;

use Yiisoft\Data\Reader\Filter\GroupFilter;
use Yiisoft\Data\Reader\Filter\FilterInterface;

class SubscriberFilter extends GroupFilter implements FilterInterface
{
    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->withFiltersArray($filtersArray)->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function getOperator(): string
    {
        return 'and';
    }
}
