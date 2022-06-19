<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Widget\ImportStatusBadge;
use Mailery\Widget\Search\Widget\SearchWidget;
use Yiisoft\Html\Html;
use Yiisoft\Yii\DataView\GridView;
use Mailery\Web\Widget\DateTimeFormat;

/** @var Yiisoft\Yii\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Mailery\Subscriber\Counter\ImportCounter $importCounter */
/** @var Yiisoft\Aliases\Aliases $aliases */
/** @var Yiisoft\Router\UrlGeneratorInterface $url */
/** @var Yiisoft\Data\Paginator\PaginatorInterface $paginator */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle('Import lists');

?><div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <h4 class="mb-0">Import lists</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-toolbar float-right">
                            <?= SearchWidget::widget()->form($searchForm); ?>
                            <b-dropdown right size="sm" variant="secondary" class="mb-2">
                                <template v-slot:button-content>
                                    <?= Icon::widget()->name('settings'); ?>
                                </template>
                                <?= ActivityLogLink::widget()
                                    ->tag('b-dropdown-item')
                                    ->label('Activity log')
                                    ->group('subscriber'); ?>
                            </b-dropdown>
                                <a class="btn btn-sm btn-primary mx-sm-1 mb-2" href="<?= $url->generate('/subscriber/subscriber/import'); ?>">
                                <?= Icon::widget()->name('plus')->options(['class' => 'mr-1']); ?>
                                Import subscribers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <?= GridView::widget()
                    ->layout("{items}\n<div class=\"mb-4\"></div>\n{summary}\n<div class=\"float-right\">{pager}</div>")
                    ->options([
                        'class' => 'table-responsive',
                    ])
                    ->tableOptions([
                        'class' => 'table table-hover',
                    ])
                    ->emptyText('No data')
                    ->emptyTextOptions([
                        'class' => 'text-center text-muted mt-4 mb-4',
                    ])
                    ->paginator($paginator)
                    ->currentPage($paginator->getCurrentPage())
                    ->columns([
                        [
                            'label()' => ['Date'],
                            'value()' => [static function (Import $model) use ($url) {
                                return Html::a(
                                    DateTimeFormat::widget()->dateTime($model->getCreatedAt()),
                                    $url->generate('/subscriber/import/view', ['id' => $model->getId()])
                                );
                            }],
                        ],
                        [
                            'label()' => ['File name'],
                            'value()' => [static function (Import $model) use ($url) {
                                return Html::a(
                                    $model->getFile()->getTitle(),
                                    $url->generate('/storage/file/download', ['id' => $model->getFile()->getId()])
                                );
                            }],
                        ],
                        [
                            'label()' => ['Created'],
                            'value()' => [fn (Import $model) => $importCounter->withImport($model)->getCreatedCount()],
                            'emptyValue()' => ['0'],
                        ],
                        [
                            'label()' => ['Updated'],
                            'value()' => [fn (Import $model) => $importCounter->withImport($model)->getUpdatedCount()],
                            'emptyValue()' => ['0'],
                        ],
                        [
                            'label()' => ['Skipped'],
                            'value()' => [fn (Import $model) => $importCounter->withImport($model)->getSkippedCount()],
                            'emptyValue()' => ['0'],
                        ],
                        [
                            'label()' => ['Status'],
                            'value()' => [fn (Import $model) => ImportStatusBadge::widget()->import($model)],
                        ],
                    ]);
                ?>
            </div>
        </div>
    </div>
</div>
