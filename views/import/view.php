<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\ImportError;
use Mailery\Subscriber\Widget\ImportStatusBadge;
use Yiisoft\Yii\DataView\GridView;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Import $import */
/** @var Mailery\Subscriber\Counter\ImportCounter $importCounter */
/** @var Mailery\Storage\Filesystem\FileInfo $fileInfo */
/** @var Yiisoft\Yii\View\Csrf $csrf */
/** @var bool $submitted */

$this->setTitle($import->getFile()->getTitle());

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
            <h1 class="h3">Import #<?= $import->getId(); ?></h1>
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
</div>
<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <div>
            <p>File name: <b><?= $import->getFile()->getTitle(); ?></b></p>
            <p>File size:
                <b><?php
                    $fileSize = $fileInfo->getFileSize();
                    if ($fileSize > 1000000) {
                        echo ByteUnits\bytes($fileSize)->format('MB', ' ');
                    } else {
                        echo ByteUnits\bytes($fileSize)->format('kB', ' ');
                    }
                ?></b>
            </p>
            <p>Status: <?= ImportStatusBadge::widget()->import($import); ?></p>
        </div>
        <div class="progress">
            <?php
                $total = $import->getTotalCount();
                $processed = $importCounter->getProcessedCount();
                $progress = $processed > 0 ? round(($processed / $total) * 100, 2) : 0;
                $percent = $progress > 100 ? 100 : $progress;
            ?>
            <div class="progress-bar" role="progressbar" style="width: <?= $percent; ?>%;" aria-valuenow="<?= $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?= $percent; ?>%</div>
        </div>
    </div>
</div>
<div class="mb-4"></div>
<div class="row">
    <div class="col-4">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="h6">New subscribers added</h4>
                <span class="badge badge-pill badge-success" style="font-size: 20px">
                    <?= $importCounter->getInsertedCount(); ?>
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
<div class="mb-5"></div>
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
