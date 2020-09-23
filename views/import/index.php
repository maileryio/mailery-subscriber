<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\Import;
use Mailery\Subscriber\Module;
use Mailery\Subscriber\Widget\ImportStatusBadge;
use Mailery\Widget\Dataview\Columns\DataColumn;
use Mailery\Widget\Dataview\GridView;
use Mailery\Widget\Dataview\GridView\LinkPager;
use Mailery\Widget\Search\Widget\SearchWidget;
use Yiisoft\Html\Html;

/** @var Yiisoft\Yii\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Mailery\Subscriber\Counter\ImportCounter $importCounter */
/** @var Yiisoft\Aliases\Aliases $aliases */
/** @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator */
/** @var Yiisoft\Data\Reader\DataReaderInterface $dataReader*/
/** @var Yiisoft\Data\Paginator\PaginatorInterface $paginator */
/** @var string $csrf */

$this->setTitle('Import lists');

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
            <h1 class="h2">Import lists</h1>
            <div class="btn-toolbar float-right">
                <?= SearchWidget::widget()->form($searchForm); ?>
                <b-dropdown right size="sm" variant="secondary" class="mb-2">
                    <template v-slot:button-content>
                        <?= Icon::widget()->name('settings'); ?>
                    </template>
                    <?= ActivityLogLink::widget()
                        ->tag('b-dropdown-item')
                        ->label('Activity log')
                        ->module(Module::NAME); ?>
                </b-dropdown>
                    <a class="btn btn-sm btn-primary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/subscriber/import'); ?>">
                    <?= Icon::widget()->name('plus')->options(['class' => 'mr-1']); ?>
                    Import subscribers
                </a>
            </div>
        </div>
    </div>
</div>
<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <?= GridView::widget()
            ->paginator($paginator)
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
            ->columns([
                (new DataColumn())
                    ->header('Date')
                    ->content(function (Import $data, int $index) use ($urlGenerator) {
                        return Html::a(
                            $data->getCreatedAt()->format('Y-m-d H:i:s'),
                            $urlGenerator->generate('/subscriber/import/view', ['id' => $data->getId()])
                        );
                    }),
                (new DataColumn())
                    ->header('File name')
                    ->content(function (Import $data, int $index) use ($urlGenerator) {
                        return Html::a(
                            $data->getFile()->getName(),
                            $urlGenerator->generate('/storage/file/download', ['id' => $data->getFile()->getId()])
                        );
                    }),
                (new DataColumn())
                    ->header('Inserted')
                    ->content(function (Import $data, int $index) use ($importCounter) {
                        return $importCounter->withImport($data)->getInsertedCount();
                    }),
                (new DataColumn())
                    ->header('Updated')
                    ->content(function (Import $data, int $index) use ($importCounter) {
                        return $importCounter->withImport($data)->getUpdatedCount();
                    }),
                (new DataColumn())
                    ->header('Skipped')
                    ->content(function (Import $data, int $index) use ($importCounter) {
                        return $importCounter->withImport($data)->getSkippedCount();
                    }),
                (new DataColumn())
                    ->header('Status')
                    ->content(function (Import $data, int $index) use ($urlGenerator) {
                        return ImportStatusBadge::widget()
                            ->import($data);
                    }),
            ]);
        ?>
    </div>
</div><?php
if ($paginator->getTotalItems() > 0) {
            ?><div class="mb-4"></div>
    <div class="row">
        <div class="col-6">
            <?= GridView\OffsetSummary::widget()
                ->paginator($paginator); ?>
        </div>
        <div class="col-6">
            <?= LinkPager::widget()
                ->paginator($paginator)
                ->options([
                    'class' => 'float-right',
                ])
                ->prevPageLabel('Previous')
                ->nextPageLabel('Next')
                ->urlGenerator(function (int $page) use ($urlGenerator) {
                    $url = $urlGenerator->generate('/subscriber/import/index');
                    if ($page > 1) {
                        $url = $url . '?page=' . $page;
                    }

                    return $url;
                }); ?>
        </div>
    </div><?php
        }
?>
