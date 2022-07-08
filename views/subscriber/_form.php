<?php

use Mailery\Widget\Select\Select;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Form\Field;

/** @var Yiisoft\View\WebView $this */
/** @var Mailery\Subscriber\Form\SubscriberForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

?>

<?= Form::tag()
        ->csrf($csrf)
        ->id('subscriber-form')
        ->post()
        ->open(); ?>

<?= Field::text($form, 'name')->autofocus(); ?>

<?= Field::text($form, 'email'); ?>

<?= Field::input(
        Select::class,
        $form,
        'groups',
        [
            'optionsData()' => [$form->getGroupListOptions()],
            'multiple()' => [true],
            'taggable()' => [true],
            'deselectFromDropdown()' => [true],
        ]
    ); ?>

<?= Field::input(
        Select::class,
        $form,
        'confirmed',
        [
            'optionsData()' => [$form->getBooleanListOptions()],
            'clearable()' => [false],
            'searchable()' => [false],
        ]
    ); ?>

<?= Field::submitButton()
        ->content('Save'); ?>

<?= Form::tag()->close(); ?>
