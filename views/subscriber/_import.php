<?php

use Mailery\Widget\Select\Select;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Form\Helper\HtmlForm;
use Yiisoft\Form\Field;

/** @var Yiisoft\View\WebView $this */
/** @var Mailery\Subscriber\Form\ImportForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

?>

<?= Form::tag()
        ->csrf($csrf)
        ->enctype('multipart/form-data')
        ->id('subscriber-import-form')
        ->post()
        ->open(); ?>

<?= Field::file($form, 'file')
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

<?= Field::submitButton()
        ->buttonClass('btn btn-primary float-right mt-2')
        ->content('Save'); ?>

<?= Form::tag()->close(); ?>