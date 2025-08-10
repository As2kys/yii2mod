<?php

namespace app\controllers;

use app\models\NginxLog;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\Response;
use Yii;


class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = NginxLog::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $rows = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();/**/

        $datesCount = NginxLog::find()
            ->select('date, COUNT(*) as count')->groupBy('date')
            ->asArray()
            ->all();
 
        return $this->render('index', [
            'rows' => $rows,
            'pages' => $pages,
            'datesCount' => $datesCount
        ]);
    }
}