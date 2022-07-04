<?php declare(strict_types=1);

use Mailery\Activity\Log\Widget\ActivityLogLink;
use Mailery\Icon\Icon;
use Mailery\Widget\Link\Link;
use Mailery\Web\Widget\DateTimeFormat;
use Mailery\Widget\Search\Widget\SearchWidget;
use Mailery\Subscriber\Controller\GroupController;
use Yiisoft\Yii\Bootstrap5\Nav;

/** @var Yiisoft\Yii\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Mailery\Subscriber\Counter\SubscriberCounter $subscriberCounter */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Group $group */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle($group->getName());

?><div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <h4 class="mb-0"><?= $group->getName(); ?></h4>
                        <p class="mt-1 mb-0 small">
                            Changed at <?= DateTimeFormat::widget()->dateTime($group->getUpdatedAt()) ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-toolbar float-right">
                            <?= SearchWidget::widget()->form($searchForm); ?>
                            <b-dropdown right size="sm" variant="secondary" class="mb-2">
                                <template v-slot:button-content>
                                    <?= Icon::widget()->name('settings'); ?>
                                </template>
                                <b-dropdown-item href="<?= $url->generate('/subscriber/group/edit', ['id' => $group->getId()]); ?>">Edit</b-dropdown-item>
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
                                        ->href($url->generate('/subscriber/group/delete', ['id' => $group->getId()]))
                                        ->confirm('Are you sure?')
                                        ->afterRequest(<<<JS
                                            (res) => {
                                                res.redirected && res.url && (window.location.href = res.url);
                                            }
                                            JS
                                        )
                                        ->options([
                                            'class' => 'btn btn-link text-decoration-none text-danger',
                                        ]); ?>
                                </b-dropdown-text>
                            </b-dropdown>
                            <a class="btn btn-sm btn-primary mx-sm-1 mb-2" href="<?= $url->generate('/subscriber/subscriber/create', ['groupId' => $group->getId()]); ?>">
                                <?= Icon::widget()->name('plus')->options(['class' => 'mr-1']); ?>
                                Add subscribers
                            </a>
                            <div class="btn-toolbar float-right">
                                <a class="btn btn-sm btn-outline-secondary mx-sm-1 mb-2" href="<?= $url->generate('/subscriber/group/index'); ?>">
                                    Back
                                </a>
                            </div>
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
                <?= Nav::widget()
                    ->items([
                        [
                            'label' => 'All <b-badge pill variant="info">' . $subscriberCounter->withGroup($group)->getTotalCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId()]),
                            'active' => empty($tab),
                        ],
                        [
                            'label' => 'Active <b-badge pill variant="success">' . $subscriberCounter->withGroup($group)->getActiveCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_ACTIVE]),
                            'active' => $tab === GroupController::TAB_ACTIVE,
                        ],
                        [
                            'label' => 'Unconfirmed <b-badge pill variant="secondary">' . $subscriberCounter->withGroup($group)->getUnconfirmedCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_UNCONFIRMED]),
                            'active' => $tab === GroupController::TAB_UNCONFIRMED,
                        ],
                        [
                            'label' => 'Unsubscribed <b-badge pill variant="warning">' . $subscriberCounter->withGroup($group)->getUnsubscribedCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_UNSUBSCRIBED]),
                            'active' => $tab === GroupController::TAB_UNSUBSCRIBED,
                        ],
                        [
                            'label' => 'Bounced <b-badge pill variant="dark">' . $subscriberCounter->withGroup($group)->getBouncedCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_BOUNCED]),
                            'active' => $tab === GroupController::TAB_BOUNCED,
                        ],
                        [
                            'label' => 'Marked as spam <b-badge pill variant="danger">' . $subscriberCounter->withGroup($group)->getComplaintCount() . '</b-badge>',
                            'url' => $url->generate('/subscriber/group/view', ['id' => $group->getId(), 'tab' => GroupController::TAB_COMPLAINT]),
                            'active' => $tab === GroupController::TAB_COMPLAINT,
                        ],
                    ])
                    ->options([
                        'class' => 'nav nav-tabs nav-tabs-bordered font-weight-bold',
                    ])
                    ->withoutEncodeLabels();
                ?>

                <div class="mb-4"></div>
                <div class="tab-content">
                    <div class="tab-pane active" role="tabpanel">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
