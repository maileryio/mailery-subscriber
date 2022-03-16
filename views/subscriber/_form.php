<?php

use Yiisoft\Html\Html;
use Yiisoft\Form\Widget\Form;

/** @var Yiisoft\Form\Widget\Field $field */
/** @var Yiisoft\View\WebView $this */
/** @var Mailery\Subscriber\Form\SubscriberForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

//var_dump($form->getErrors('groups'));exit;

?>

<?= Form::widget()
    ->options(
        [
            'id' => 'form-subscriber',
            'csrf' => $csrf,
            'enctype' => 'multipart/form-data',
        ]
    )
    ->begin(); ?>

<?= $field->config($form, 'name'); ?>

<?= $field->config($form, 'email'); ?>

<?= $field->config($form, 'groups')
        ->listBox(
            $form->getGroupListOptions(),
            [
                'class' => 'form-control',
                'multiple' => true,
            ]
        );
?>

<?= $field->config($form, 'confirmed')
        ->dropDownList(
            $form->getConfirmedListOptions(),
            [
                'class' => 'form-control',
            ]
        );
?>

<?= Html::submitButton(
    'Save',
    [
        'class' => 'btn btn-primary float-right mt-2',
    ]
); ?>

<?= Form::end(); ?>