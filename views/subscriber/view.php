<?php declare(strict_types=1);

use Mailery\Subscriber\Entity\Subscriber;
use Yiisoft\Yii\Widgets\ContentDecorator;
use Yiisoft\Yii\DataView\DetailView;

/** @var Yiisoft\Yii\WebView $this */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Subscriber $subscriber */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle($subscriber->getName());

?>

<?= ContentDecorator::widget()
    ->viewFile('@vendor/maileryio/mailery-subscriber/views/subscriber/_layout.php')
    ->parameters(compact('subscriber', 'csrf'))
    ->begin(); ?>

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <h6 class="font-weight-bold">General details</h6>
    </div>
</div>

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <?= DetailView::widget()
            ->model($subscriber)
            ->options([
                'class' => 'table detail-view',
            ])
            ->emptyValue('<span class="text-muted">(not set)</span>')
            ->attributes([
                [
                    'label' => 'Name',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getName();
                    },
                ],
                [
                    'label' => 'Email',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getEmail();
                    },
                ],
                [
                    'label' => 'Confirmed',
                    'value' => function (Subscriber $data, $index) {
                        return $data->getConfirmed();
                    },
                ],
            ]);
        ?>
    </div>
</div>

<?= ContentDecorator::end() ?>
