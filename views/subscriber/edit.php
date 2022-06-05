<?php declare(strict_types=1);

use Mailery\Web\Widget\FlashMessage;
use Yiisoft\Yii\Widgets\ContentDecorator;

/** @var Yiisoft\Form\Widget\Field $field */
/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Subscriber $subscriber */
/** @var Mailery\Subscriber\Form\SubscriberForm $form */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle('Edit subscriber #' . $subscriber->getId());

?>

<?= ContentDecorator::widget()
    ->viewFile('@vendor/maileryio/mailery-subscriber/views/subscriber/_layout.php')
    ->parameters(compact('subscriber', 'csrf'))
    ->begin(); ?>

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <?= FlashMessage::widget(); ?>
    </div>
</div>
<div class="mb-2"></div>

<div class="row">
    <div class="col-12">
        <?= $this->render('_form', compact('csrf', 'field', 'form')) ?>
    </div>
</div>

<?= ContentDecorator::end() ?>
