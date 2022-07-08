<?php

use Yiisoft\Html\Tag\Form;
use Yiisoft\Form\Field;

/** @var Yiisoft\View\WebView $this */
/** @var Mailery\Subscriber\Form\GroupForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

?>
<div class="row">
    <div class="col-12">
        <?= Form::tag()
                ->csrf($csrf)
                ->id('group-form')
                ->post()
                ->open(); ?>

        <?= Field::text($form, 'name')->autofocus(); ?>

        <?= Field::submitButton()
                ->content('Save'); ?>

        <?= Form::tag()->close(); ?>
    </div>
</div>