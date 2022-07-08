<?php declare(strict_types=1);

use Mailery\Icon\Icon;
use Mailery\Web\Widget\FlashMessage;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Group $group */
/** @var Mailery\Subscriber\Form\GroupForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle('Edit Group #' . $group->getId());

?><div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md">
                        <h4 class="mb-0">Edit group #<?= $group->getId(); ?></h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-toolbar float-right">
                            <a class="btn btn-sm btn-info mx-sm-1 mb-2" href="<?= $url->generate('/subscriber/group/view', ['id' => $group->getId()]); ?>">
                                <?= Icon::widget()->name('eye')->options(['class' => 'mr-1']); ?>
                                View
                            </a>
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

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"></div>
                <div class="row">
                    <div class="col-12">
                        <?= FlashMessage::widget(); ?>
                    </div>
                </div>

                <div class="mb-2"></div>
                <?= $this->render('_form', compact('csrf', 'form')) ?>
            </div>
        </div>
    </div>
</div>
