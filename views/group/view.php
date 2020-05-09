<?php declare(strict_types=1);

use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Widget\Dataview\Columns\ActionColumn;
use Mailery\Widget\Dataview\Columns\DataColumn;
use Mailery\Widget\Dataview\GridView;
use Mailery\Widget\Dataview\GridView\LinkPager;
use Mailery\Widget\Link\Link;
use Mailery\Widget\Search\Widget\SearchWidget;
use Yiisoft\Html\Html;
use Yiisoft\Yii\Bootstrap4\Nav;

/** @var Mailery\Web\View\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Group $group */
/** @var bool $submitted */
$this->setTitle($group->getName());

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2"><?= $group->getName(); ?></h1>
            <div class="btn-toolbar float-right">
                <?= SearchWidget::widget()->form($searchForm); ?>
                <a class="btn btn-sm btn-primary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/group/edit', ['id' => $group->getId()]); ?>">
                    <?= Icon::widget()->name('plus')->options(['class' => 'mr-1']); ?>
                    Add subscribers
                </a>
                <b-dropdown right size="sm" variant="secondary" class="mb-2">
                    <template v-slot:button-content>
                        <?= Icon::widget()->name('settings'); ?>
                    </template>
                    <b-dropdown-item href="<?= $urlGenerator->generate('/subscriber/group/edit', ['id' => $group->getId()]); ?>">Edit</b-dropdown-item>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-text variant="danger" class="dropdown-item-custom-link"><?= Link::widget()
                        ->label('Delete group')
                        ->method('delete')
                        ->href($urlGenerator->generate('/subscriber/group/delete', ['id' => $group->getId()]))
                        ->confirm('Are you sure?')
                        ->options([
                            'class' => 'btn btn-link text-decoration-none text-danger',
                        ]);
                    ?></b-dropdown-text>
                </b-dropdown>
                <div class="btn-toolbar float-right">
                    <a class="btn btn-sm btn-outline-secondary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/group/index'); ?>">
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
        <?php $dataRenderer = function ($paginator) use ($group, $urlGenerator) {
                        return GridView::widget()
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
                        ->header('Email')
                        ->content(function (Subscriber $data, int $index) use ($urlGenerator) {
                            return Html::a(
                                $data->getEmail(),
                                $urlGenerator->generate('/subscriber/subscriber/view', ['id' => $data->getId()])
                            );
                        }),
                    (new ActionColumn())
                        ->contentOptions([
                            'style' => 'width: 80px;',
                        ])
                        ->header('Edit')
                        ->view('')
                        ->update(function (Subscriber $data, int $index) use ($urlGenerator) {
                            return Html::a(
                                (string) Icon::widget()->name('pencil'),
                                $urlGenerator->generate('/subscriber/subscriber/edit', ['id' => $data->getId()]),
                                [
                                    'class' => 'text-decoration-none mr-3',
                                ]
                            );
                        })
                        ->delete(''),
                    (new ActionColumn())
                        ->contentOptions([
                            'style' => 'width: 80px;',
                        ])
                        ->header('Delete')
                        ->view('')
                        ->update('')
                        ->delete(function (Subscriber $data, int $index) use ($group, $urlGenerator) {
                            return Link::widget()
                                ->label((string) Icon::widget()->name('delete')->options(['class' => 'mr-1']))
                                ->method('delete')
                                ->href($urlGenerator->generate('/subscriber/group/delete-subscriber', ['id' => $group->getId(), 'subscriberId' => $data->getId()]))
                                ->confirm('Are you sure?')
                                ->options([
                                    'class' => 'text-decoration-none text-danger',
                                ]);
                        }),
                ]);
                    }
        ?>

        <?= Nav::widget()
            ->items([
                [
                    'label' => 'All <b-badge pill variant="info">' . $group->getTotalCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]),
                    'encode' => false,
                    'active' => empty($tab),
                ],
                [
                    'label' => 'Active <b-badge pill variant="success">' . $group->getActiveCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => 'active']),
                    'encode' => false,
                    'active' => $tab === 'active',
                ],
                [
                    'label' => 'Unconfirmed <b-badge pill variant="secondary">' . $group->getUnconfirmedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => 'unconfirmed']),
                    'encode' => false,
                    'active' => $tab === 'unconfirmed',
                ],
                [
                    'label' => 'Unsubscribed <b-badge pill variant="warning">' . $group->getUnsubscribedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => 'unsubscribed']),
                    'encode' => false,
                    'active' => $tab === 'unsubscribed',
                ],
                [
                    'label' => 'Bounced <b-badge pill variant="dark">' . $group->getBouncedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => 'bounced']),
                    'encode' => false,
                    'active' => $tab === 'bounced',
                ],
                [
                    'label' => 'Marked as spam <b-badge pill variant="danger">' . $group->getComplaintCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => 'complaint']),
                    'encode' => false,
                    'active' => $tab === 'complaint',
                ],
            ])
            ->options([
                'class' => 'nav nav-tabs',
            ]);
        ?>

        <div class="tab-content">
            <div class="tab-pane fade show active" role="tabpanel">
                <?= $dataRenderer($paginator); ?>

                <?php
                    if ($paginator->getTotalCount() > 0) {
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
                                    ->urlGenerator(function (int $page) use ($urlGenerator, $tab) {
                                        $url = $urlGenerator->generate('/subscriber/group/index');
                                        $params = array_filter([
                                            'tab' => $tab,
                                            'page' => $page > 1 ? $page : null,
                                        ]);

                                        if (!empty($params)) {
                                            return $url . '?' . http_build_query($params);
                                        }

                                        return $url;
                                    }); ?>
                            </div>
                        </div><?php
                    }
                ?>
            </div>
        </div>
    </div>
</div>
