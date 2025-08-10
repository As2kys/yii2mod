<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\widgets\Pjax;

/** @var yii\web\View $this */

$this->title = 'Модимир тест';
$this->registerCss('
    thead td { border-bottom: 1px solid #ccc; }
    .pagination a {
        color: #000;
        background: #ccc; border: 1px solid #ccc; border-radius: 10px;
        display: inline-block; margin: 0 10px; padding: 2px 10px 4px;
        text-decoration: none;
    }
    .pages-nav { margin: 20px 0 10px }
    .pagination li.active a { background: #fff; text-decoration: none }
');
?>
<div class="site-index">
    <div class="body-content">
        <div class="row">
            <?php
                foreach ($datesCount as $dateCount) {
                    echo '&nbsp; ', $dateCount['date'], ': &ndash; ', $dateCount['count'], ' &nbsp; ';
                }
            ?></p>
            <div class="text-center" style="border: 1px solid #000; max-width: 600px; margin: 10px 0; padding: 10px;">
                график <?= date('H:i:s') ?>
            </div>
            <table>
                <thead>
                    <td>Дата / время</td>
                    <td>IP</td>
                    <td>OC</td>
                    <td>Браузер / User agent <i>(полное название при наведении)</i></td>
                </thead>
                <tbody>
                <?php
                    if (!count($rows)) {
                        echo "<p>Empty log, run nginx/import?</p>\n";
                    }
                    else foreach ($rows as $row) {
                        echo '<tr>',
                            '<td>', substr($row['datetime'], 0, 17), '</td>',
                            '<td>', $row['ip'], '</td>',
                            '<td>', $row['os'] ?? '-', ' ', $row['x64'], '</td>',
                            '<td>', Html::tag('span',
                                    $row['browser'] . ' (' . substr($row['useragent'], 0, 70) . '...)',
                                    ['title' => $row['useragent']]),
                            '</td>',
                        '</tr>';
                    }
                ?>
                </tbody>
            </table>
            <?php
                $pjaxId = 'nginx-pjax-' . $pages->page;
                $pjax = Pjax::begin([
                    'id' => $pjaxId,
                    'linkSelector' => '#' . $pjaxId . ' a[data-page]',
                    'options' => ['class' => 'pages-nav', "data-pjax-container" => 2],
                    'timeout' => 5000
                ]);

                echo
                    LinkPager::widget([
                        'pagination' => $pages,
                        'maxButtonCount' => 8,
                        'nextPageLabel' => 'Вперед ›',
                        'prevPageLabel' => '‹ Назад',
                ]);
                Pjax::end();
            ?>
        </div>
    </div>
</div>
