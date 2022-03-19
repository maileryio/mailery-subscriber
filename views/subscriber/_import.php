<?php

use Yiisoft\Html\Html;
use Yiisoft\Form\Widget\Form;
use Yiisoft\Form\Helper\HtmlForm;

/** @var Yiisoft\Form\Widget\Field $field */
/** @var Yiisoft\View\WebView $this */
/** @var Mailery\Subscriber\Form\ImportForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

?>

<?= Form::widget()
        ->csrf($csrf)
        ->id('subscriber-import-form')
        ->begin(); ?>

<?= $field->file($form, 'file')
        ->template(strtr(
            "{label}\n{input}\n{hint}\n{error}",
            [
                '{input}' => Html::tag(
                    'ui-csv-import',
                    '',
                    [
                        'name' => HtmlForm::getInputName($form, 'file'),
                        'map-fields' => $form->getFieldLabels(),
                        'map-fields-name' => HtmlForm::getInputName($form, 'fields'),
                    ]
                ),
            ]
        ));
?>

<?= $field->select($form, 'groups', ['items()' => [$form->getGroupListOptions()], 'multiple()' => [true]]); ?>

<?= $field->submitButton()
        ->class('btn btn-primary float-right mt-2')
        ->value('Save'); ?>

<?= Form::end(); ?>