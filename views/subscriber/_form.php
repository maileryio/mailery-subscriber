<?php

use Mailery\Widget\Select\Select;
use Yiisoft\Form\Widget\Form;

/** @var Yiisoft\Form\Widget\Field $field */
/** @var Yiisoft\View\WebView $this */
/** @var \Yiisoft\Form\FormModelInterface $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

?>

<?= Form::widget()
        ->csrf($csrf)
        ->id('subscriber-form')
        ->begin(); ?>

<?= $field->text($form, 'name')->autofocus(); ?>

<?= $field->text($form, 'email'); ?>

<?= $field->select(
        $form,
        'groups',
        [
            'class' => Select::class,
            'items()' => [$form->getGroupListOptions()],
            'multiple()' => [true],
            'taggable()' => [true],
            'deselectFromDropdown()' => [true],
        ]
    ); ?>

<?= $field->select(
        $form,
        'confirmed',
        [
            'class' => Select::class,
            'items()' => [$form->getConfirmedListOptions()],
            'clearable()' => [false],
            'searchable()' => [false],
        ]
    ); ?>

<?= $field->submitButton()
        ->class('btn btn-primary float-right mt-2')
        ->value('Save'); ?>

<?= Form::end(); ?>
