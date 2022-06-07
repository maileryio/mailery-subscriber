<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Widget\ImportStatusBadge;
use Mailery\Web\Widget\ByteUnitsFormat;
use Mailery\Web\Widget\DateTimeFormat;
use Yiisoft\Yii\DataView\GridView;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Import $import */
/** @var Mailery\Subscriber\Counter\ImportCounter $importCounter */
/** @var Mailery\Storage\Filesystem\FileInfo $fileInfo */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle($import->getFile()->getTitle());

?><div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <h4 class="mb-0">Import #<?= $import->getId(); ?></h4>
                        <p class="mt-1 mb-0 small">
                            Changed at <?= DateTimeFormat::widget()->dateTime($import->getUpdatedAt())->run() ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-toolbar float-right">
                            <b-dropdown right size="sm" variant="secondary" class="mb-2">
                                <template v-slot:button-content>
                                    <?= Icon::widget()->name('settings'); ?>
                                </template>
                                <?= ActivityLogLink::widget()
                                    ->tag('b-dropdown-item')
                                    ->label('Activity log')
                                    ->entity($import); ?>
                            </b-dropdown>
                            <div class="btn-toolbar float-right">
                                <a class="btn btn-sm btn-outline-secondary mx-sm-1 mb-2" href="<?= $url->generate('/subscriber/import/index'); ?>">
                                    Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4"></div>
                <div class="row">
                    <div class="col-12">
                        <div>
                            <p>File name: <b><?= $import->getFile()->getTitle(); ?></b></p>
                            <p>File size: <b><?= ByteUnitsFormat::widget()->bytes($fileInfo->getFileSize()) ?></b></p>
                            <p>Status: <?= ImportStatusBadge::widget()->import($import); ?></p>
                        </div>
                        <div class="progress">
                            <?php
                                $total = $importCounter->getTotalCount();
                                $processed = $importCounter->getProcessedCount();
                                $progress = $processed > 0 ? round(($processed / $total) * 100, 2) : 0;
                                $percent = $progress > 100 ? 100 : $progress;
                            ?>
                            <div class="progress-bar" role="progressbar" style="width: <?= $percent; ?>%;" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?= $percent; ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-2"></div>
<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="h6">New subscribers added</h4>
                <span class="badge badge-pill badge-success" style="font-size: 20px">
                    <?= $importCounter->getCreatedCount(); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="h6">Subscribers updated</h4>
                <span class="badge badge-pill badge-info" style="font-size: 20px">
                    <?= $importCounter->getUpdatedCount(); ?>
                </span>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="h6">Skipped records</h4>
                <span class="badge badge-pill badge-danger" style="font-size: 20px">
                    <?= $importCounter->getSkippedCount(); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="mb-4"></div>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3 class="h3">Import log</h3>
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
                                    'label()' => ['Error message'],
                                    'value()' => [fn (ImportError $model) => $model->getError()],
                                ],
                                [
                                    'label()' => ['Field'],
                                    'value()' => [fn (ImportError $model) => $model->getName()],
                                ],
                                [
                                    'label()' => ['Value'],
                                    'value()' => [fn (ImportError $model) => $model->getValue()],
                                ],
                            ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
