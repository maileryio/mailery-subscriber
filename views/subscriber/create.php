<?php declare(strict_types=1);

use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Form\ImportForm;
use Yiisoft\Yii\Bootstrap5\Nav;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Form\SubscriberForm|Mailery\Subscriber\Form\ImportForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle('Add subscribers');

?><div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <h4 class="mb-0">Add subscribers</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-toolbar float-right">
                            <a class="btn btn-sm btn-outline-secondary mx-sm-1" href="<?= $url->generate('/subscriber/subscriber/index'); ?>">
                                Back
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
                <?php
                    $routeParams = array_filter([
                        'groupId' => $group !== null ? $group->getId() : null,
                    ]);
                ?>
                <?= Nav::widget()
                    ->items([
                        [
                            'label' => 'Add single subscriber',
                            'url' => $url->generate('/subscriber/subscriber/create', $routeParams),
                            'active' => $form instanceof SubscriberForm,
                        ],
                        [
                            'label' => 'Import from file',
                            'url' => $url->generate('/subscriber/subscriber/import', $routeParams),
                            'active' => $form instanceof ImportForm,
                        ],
                    ])
                    ->options([
                        'class' => 'nav nav-tabs nav-tabs-bordered font-weight-bold',
                    ])
                    ->withoutEncodeLabels();
                ?>

                <div class="mb-4"></div>
                <div class="tab-content">
                    <div class="tab-pane fade show active" role="tabpanel">
                        <div class="row"><?php
                            if ($form instanceof SubscriberForm) {
                                ?><div class="col-6">
                                    <?= $this->render('_form', compact('csrf', 'form')) ?>
                                </div><?php
                            } else if ($form instanceof ImportForm) {
                                ?><div class="col-6">
                                    <?= $this->render('_import', compact('csrf', 'form')) ?>
                                </div><?php
                            }
                        ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
