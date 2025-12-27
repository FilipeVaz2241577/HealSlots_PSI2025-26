<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;
use Yii;

class RequisicaoController extends ActiveController
{
    public $modelClass = 'common\models\Requisicao';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    /**
     * Endpoint personalizado: Buscar requisições
     * GET /api/requisicao/search?keyword=valor&status=Ativa&user_id=1
     */
    public function actionSearch($keyword = null, $status = null, $user_id = null)
    {
        $query = Requisicao::find()->joinWith(['user', 'sala']);

        if ($keyword) {
            $query->andWhere(['or',
                ['like', 'descricao', $keyword],
                ['like', 'user.username', $keyword],
                ['like', 'sala.nome', $keyword]
            ]);
        }

        if ($status) {
            $query->andWhere(['requisicao.status' => $status]);
        }

        if ($user_id) {
            $query->andWhere(['user_id' => $user_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'dataInicio' => SORT_DESC,
                ]
            ],
        ]);

        return $dataProvider;
    }
}