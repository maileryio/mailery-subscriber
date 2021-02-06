<?php declare(strict_types=1);

use Mailery\Widget\Form\FormRenderer;
use Yiisoft\Yii\Bootstrap4\Nav;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var FormManager\Form $subscriberForm */
/** @var FormManager\Form $importForm */
/** @var string $csrf */
/** @var bool $submitted */

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
                    'encode' => false,
                    'active' => !empty($subscriberForm),
                ],
                [
                    'label' => 'Import from file',
                    'url' => $urlGenerator->generate('/subscriber/subscriber/import', $routeParams),
                    'encode' => false,
                    'active' => !empty($importForm),
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
                    if (!empty($subscriberForm)) {
                        ?><div class="col-6">
                            <?= (new FormRenderer($subscriberForm->withCsrf($csrf)))($submitted); ?>
                        </div><?php
                    } else {
                        if (!empty($importForm)) {
                            ?><div class="col-6">
                            <?= (new FormRenderer($importForm->withCsrf($csrf)))($submitted); ?>
                        </div><?php
                        }
                    }
                ?></div>
            </div>
        </div>
    </div>
</div>
