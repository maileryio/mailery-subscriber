<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Subscriber\Controller\GroupController;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Widget\Dataview\Columns\ActionColumn;
use Mailery\Widget\Dataview\Columns\DataColumn;
use Mailery\Widget\Dataview\GridView;
use Mailery\Widget\Dataview\GridView\LinkPager;
use Mailery\Widget\Link\Link;
use Mailery\Widget\Search\Widget\SearchWidget;
use Yiisoft\Html\Html;
use Yiisoft\Yii\Bootstrap5\Nav;

/** @var Yiisoft\Yii\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Mailery\Subscriber\Counter\SubscriberCounter $subscriberCounter */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Group $group */
/** @var Yiisoft\Yii\View\Csrf $csrf */
/** @var bool $submitted */

$this->setTitle($group->getName());

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
            <h1 class="h3"><?= $group->getName(); ?></h1>
            <div class="btn-toolbar float-right">
                <?= SearchWidget::widget()->form($searchForm); ?>
                <b-dropdown right size="sm" variant="secondary" class="mb-2">
                    <template v-slot:button-content>
                        <?= Icon::widget()->name('settings'); ?>
                    </template>
                    <b-dropdown-item href="<?= $urlGenerator->generate('/subscriber/group/edit', ['id' => $group->getId()]); ?>">Edit</b-dropdown-item>
                    <?= ActivityLogLink::widget()
                        ->tag('b-dropdown-item')
                        ->label('Activity log')
                        ->entity($group); ?>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-text variant="danger" class="dropdown-item-custom-link">
                        <?= Link::widget()
                            ->csrf($csrf)
                            ->label('Delete group')
                            ->method('delete')
                            ->href($urlGenerator->generate('/subscriber/group/delete', ['id' => $group->getId()]))
                            ->confirm('Are you sure?')
                            ->options([
                                'class' => 'btn btn-link text-decoration-none text-danger',
                            ]); ?>
                    </b-dropdown-text>
                </b-dropdown>
                <a class="btn btn-sm btn-primary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/subscriber/create', ['groupId' => $group->getId()]); ?>">
                    <?= Icon::widget()->name('plus')->options(['class' => 'mr-1']); ?>
                    Add subscribers
                </a>
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
    <div class="col-12">
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
                    (new DataColumn())
                        ->header('Name')
                        ->content(function (Subscriber $data, int $index) use ($urlGenerator) {
                            return $data->getName();
                        }),
                    (new ActionColumn())
                        ->contentOptions([
                            'style' => 'width: 80px;',
                        ])
                        ->header('Edit')
                        ->view('')
                        ->update(function (Subscriber $data, int $index) use ($urlGenerator) {
                            return Html::a(
                                Icon::widget()->name('pencil')->render(),
                                $urlGenerator->generate('/subscriber/subscriber/edit', ['id' => $data->getId()]),
                                [
                                    'class' => 'text-decoration-none mr-3',
                                ]
                            )
                            ->encode(false);
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
                                ->label(Icon::widget()->name('delete')->options(['class' => 'mr-1'])->render())
                                ->method('delete')
                                ->href($urlGenerator->generate('/subscriber/group/delete-subscriber', ['id' => $group->getId(), 'subscriberId' => $data->getId()]))
                                ->confirm('Are you sure?')
                                ->options([
                                    'class' => 'text-decoration-none text-danger',
                                ])
                                ->encode(false);
                        }),
                ]);
            }
        ?>

        <?= Nav::widget()
            ->items([
                [
                    'label' => 'All <b-badge pill variant="info">' . $subscriberCounter->withGroup($group)->getTotalCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId()]),
                    'active' => empty($tab),
                ],
                [
                    'label' => 'Active <b-badge pill variant="success">' . $subscriberCounter->withGroup($group)->getActiveCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_ACTIVE]),
                    'active' => $tab === GroupController::TAB_ACTIVE,
                ],
                [
                    'label' => 'Unconfirmed <b-badge pill variant="secondary">' . $subscriberCounter->withGroup($group)->getUnconfirmedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_UNCONFIRMED]),
                    'active' => $tab === GroupController::TAB_UNCONFIRMED,
                ],
                [
                    'label' => 'Unsubscribed <b-badge pill variant="warning">' . $subscriberCounter->withGroup($group)->getUnsubscribedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_UNSUBSCRIBED]),
                    'active' => $tab === GroupController::TAB_UNSUBSCRIBED,
                ],
                [
                    'label' => 'Bounced <b-badge pill variant="dark">' . $subscriberCounter->withGroup($group)->getBouncedCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_BOUNCED]),
                    'active' => $tab === GroupController::TAB_BOUNCED,
                ],
                [
                    'label' => 'Marked as spam <b-badge pill variant="danger">' . $subscriberCounter->withGroup($group)->getComplaintCount() . '</b-badge>',
                    'url' => $urlGenerator->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_COMPLAINT]),
                    'active' => $tab === GroupController::TAB_COMPLAINT,
                ],
            ])
            ->options([
                'class' => 'nav nav-tabs',
            ])
            ->withoutEncodeLabels();
        ?>

        <div class="tab-content">
            <div class="tab-pane fade show active" role="tabpanel">
                <?= $dataRenderer($paginator); ?>

                <?php
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
