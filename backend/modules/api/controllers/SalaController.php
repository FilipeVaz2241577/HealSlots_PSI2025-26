<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Sala;
use Yii;

class SalaController extends ActiveController
{
    public $modelClass = 'common\models\Sala';

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
     * Endpoint personalizado: Buscar salas
     * GET /api/sala/search?keyword=valor&estado=Livre&bloco_id=1
     */
    public function actionSearch($keyword = null, $estado = null, $bloco_id = null)
    {
        $query = Sala::find()->joinWith(['bloco']);

        if ($keyword) {
            $query->andWhere(['or',
                ['like', 'sala.nome', $keyword],
                ['like', 'bloco.nome', $keyword]
            ]);
        }

        if ($estado) {
            $query->andWhere(['sala.estado' => $estado]);
        }

        if ($bloco_id) {
            $query->andWhere(['bloco_id' => $bloco_id]);
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