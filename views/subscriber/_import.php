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
    ->options(
        [
            'id' => 'form-import',
            'csrf' => $csrf,
            'enctype' => 'multipart/form-data',
        ]
    )
    ->begin(); ?>

<?= $field->config($form, 'file')
        ->fileInput()
        ->template(strtr(
            "{label}\n{input}\n{hint}\n{error}",
            [
                '{input}' => Html::tag(
                    'ui-csv-import',
                    '',
                    [
                        'name' => HtmlForm::getInputName($form, 'file'),
                        'map-fields' => $form->getFields(),
                        'map-fields-name' => HtmlForm::getInputName($form, 'fieldsMap'),
                    ]
                ),
            ]
        ));
?>

<?= $field->config($form, 'groups')
        ->listBox(
            $form->getGroupListOptions(),
            [
                'class' => 'form-control',
                'multiple' => true,
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