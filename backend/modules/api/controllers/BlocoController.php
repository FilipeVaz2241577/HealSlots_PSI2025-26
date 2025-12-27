<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Bloco;

class BlocoController extends ActiveController
{
    public $modelClass = 'common\models\Bloco';

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
     * Endpoint personalizado: Buscar blocos
     * GET /api/bloco/search?keyword=valor&estado=ativo
     */
    public function actionSearch($keyword = null, $estado = null)
    {
        $query = Bloco::find();

        if ($keyword) {
            $query->andWhere(['or',
                ['like', 'nome', $keyword],
                ['like', 'descricao', $keyword],
                ['like', 'localizacao', $keyword]
            ]);
        }

        if ($estado) {
            $query->andWhere(['estado' => $estado]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'nome' => SORT_ASC,
                ]
            ],
        ]);

        return $dataProvider;
    }
}