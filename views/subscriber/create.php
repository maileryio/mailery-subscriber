<?php declare(strict_types=1);

use Mailery\Subscriber\Form\SubscriberForm;
use Mailery\Subscriber\Form\ImportForm;
use Yiisoft\Yii\Bootstrap5\Nav;

/** @var Yiisoft\Form\Widget\Field $field */
/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Form\SubscriberForm|Mailery\Subscriber\Form\ImportForm $form */
/** @var string $csrf */

$this->setTitle('Add subscribers');

?><div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
            <h1 class="h3">Add subscribers</h1>
            <div class="btn-toolbar float-right">
                <a class="btn btn-sm btn-outline-secondary mx-sm-1 mb-2" href="<?= $urlGenerator->generate('/subscriber/subscriber/index'); ?>">
                    Back
                </a>
            </div>
        </div>
    </div>
</div>
<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <?php
            $routeParams = array_filter([
                'groupId' => $group !== null ? $group->getId() : null,
            ]);
        ?>
        <?= Nav::widget()
            ->items([
                [
                    'label' => 'Add single subscriber',
                    'url' => $urlGenerator->generate('/subscriber/subscriber/create', $routeParams),
                    'active' => $form instanceof SubscriberForm,
                ],
                [
                    'label' => 'Import from file',
                    'url' => $urlGenerator->generate('/subscriber/subscriber/import', $routeParams),
                    'active' => $form instanceof ImportForm,
                ],
            ])
            ->options([
                'class' => 'nav nav-tabs',
            ]);
        ?>

        <div class="mb-4"></div>
        <div class="tab-content">
            <div class="tab-pane fade show active" role="tabpanel">
                <div class="row"><?php
                    if ($form instanceof SubscriberForm) {
                        ?><div class="col-6">
                            <?= $this->render('_form', compact('csrf', 'field', 'form')) ?>
                        </div><?php
                    } else if ($form instanceof ImportForm) {
                        ?><div class="col-6">
                            <?= $this->render('_import', compact('csrf', 'field', 'form')) ?>
                        </div><?php
                    }
                ?></div>
            </div>
        </div>
    </div>
</div>
