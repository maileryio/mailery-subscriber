<?php declare(strict_types=1);

use Mailery\Icon\Icon;
use Mailery\Subscriber\Entity\Subscriber;
use Mailery\Widget\Link\Link;
use Yiisoft\Html\Html;
use Yiisoft\Yii\Widgets\ContentDecorator;
use Yiisoft\Yii\DataView\GridView;
use Mailery\Web\Vue\Directive;

/** @var Yiisoft\Yii\WebView $this */
/** @var Mailery\Widget\Search\Form\SearchForm $searchForm */
/** @var Mailery\Subscriber\Counter\SubscriberCounter $subscriberCounter */
/** @var Psr\Http\Message\ServerRequestInterface $request */
/** @var Mailery\Subscriber\Entity\Group $group */
/** @var Yiisoft\Yii\View\Csrf $csrf */

$this->setTitle($group->getName());

?>

<?= ContentDecorator::widget()
    ->viewFile('@vendor/maileryio/mailery-subscriber/views/group/_layout.php')
    ->parameters(compact('group', 'searchForm', 'subscriberCounter', 'tab', 'csrf'))
    ->begin(); ?>

<div class="mb-2"></div>
<div class="row">
    <div class="col-12">
        <?= GridView::widget()
            ->layout("{items}\n<div class=\"mb-4\"></div>\n{summary}\n<div class=\"float-right\">{pager}</div>")
            ->options([
                'class' => 'table-responsive',
            ])
            ->tableOptions([
                'class' => 'table table-hover',
            ])
            ->emptyText('No data')
            ->emptyTextOptions([
                'class' => 'text-center text-muted mt-4 mb-4',
            ])
            ->paginator($paginator)
            ->currentPage($paginator->getCurrentPage())
            ->columns([
                [
                    'label()' => ['Email'],
                    'value()' => [fn (Subscriber $model) => Html::a(Directive::pre($model->getEmail()), $url->generate($model->getViewRouteName(), $model->getViewRouteParams()))],
                ],
                [
                    'label()' => ['Name'],
                    'value()' => [fn (Subscriber $model) => Directive::pre($model->getName())],
                ],
                [
                    'label()' => ['Edit'],
                    'value()' => [static function (Subscriber $model) use ($url) {
                        return Html::a(
                            Icon::widget()->name('pencil')->render(),
                            $url->generate($model->getEditRouteName(), $model->getEditRouteParams()),
                            [
                                'class' => 'text-decoration-none mr-3',
                            ]
                        )
                        ->encode(false);
                    }],
                ],
                [
                    'label()' => ['Delete'],
                    'value()' => [static function (Subscriber $model) use ($csrf, $url) {
                        return Link::widget()
                            ->csrf($csrf)
                            ->label(Icon::widget()->name('delete')->options(['class' => 'mr-1'])->render())
                            ->method('delete')
                            ->href($url->generate($model->getDeleteRouteName(), $model->getDeleteRouteParams()))
                            ->confirm('Are you sure?')
                            ->afterRequest(<<<JS
                                (res) => {
                                    res.redirected && res.url && (window.location.href = res.url);
                                }
                                JS
                            )
                            ->options([
                                'class' => 'text-decoration-none text-danger',
                            ])
                            ->encode(false);
                    }],
                ],
            ]);
        ?>
    </div>
</div>

<?= ContentDecorator::end() ?>
