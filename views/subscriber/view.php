<?php declare(strict_types=1);

use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Widget\Dataview\DetailView;
use Mailery\Widget\Link\Link;

/** @var Mailery\Web\View\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Subscriber $subscriber */
/** @var bool $submitted */
$this->setTitle($subscriber->getName());

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Subscriber #<?= $subscriber->getId(); ?></h1>
            <div class="btn-toolbar float-right">
                <?= Link::widget()
                    ->label((string) Icon::widget()->name('delete')->options(['class' => 'mr-1']) . ' Delete')
                    ->method('delete')
                    ->href($urlGenerator->generate('/subscriber/subscriber/delete', ['id' => $subscriber->getId()]))
                    ->confirm('Are you sure?')
                    ->options([
                        'class' => 'btn btn-sm btn-danger mx-sm-1 mb-2',
                    ]);
                ?>
                <a class="btn btn-sm btn-secondary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/subscriber/edit', ['id' => $subscriber->getId()]); ?>">
                    <?= Icon::widget()->name('pencil')->options(['class' => 'mr-1']); ?>
                    Update
                </a>
                <div class="btn-toolbar float-right">
                    <a class="btn btn-sm btn-outline-secondary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/subscriber/index'); ?>">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="mb-2"></div>
<div class="row">
    <div class="col-12 grid-margin">
        <?= DetailView::widget()
            ->data($subscriber)
            ->options([
                'class' => 'table detail-view',
            ])
            ->emptyText('(not set)')
            ->emptyTextOptions([
                'class' => 'text-muted',
            ])
            ->attributes([
                [
                    'label' => 'Name',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getName();
                    },
                ],
                [
                    'label' => 'Email',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getEmail();
                    },
                ],
                [
                    'label' => 'Confirmed',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getConfirmed();
                    },
                ],
            ]);
        ?>
    </div>
</div>
