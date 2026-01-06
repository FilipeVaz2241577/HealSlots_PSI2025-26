<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Bloco;
use Yii;

class BlocoController extends ActiveController
{
    public $modelClass = 'common\models\Bloco';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Configurar CORS
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => null,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => [],
            ],
        ];

        // Configurar formatos de resposta
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

    /**
     * Relação master/detail: Bloco com suas salas
     * GET /api/bloco/{id}/salas
     */
    public function actionSalas($id)
    {
        $bloco = $this->findModel($id);

        $query = $bloco->getSalas();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => [
                    'nome' => SORT_ASC,
                ]
            ],
        ]);

        return [
            'bloco' => $bloco,
            'salas' => $dataProvider,
        ];
    }

    /**
     * Estatísticas do bloco
     * GET /api/bloco/{id}/estatisticas
     */
    public function actionEstatisticas($id)
    {
        $bloco = $this->findModel($id);

        $totalSalas = $bloco->getSalas()->count();
        $salasLivres = $bloco->getSalas()->where(['estado' => 'Livre'])->count();

        return [
            'bloco' => $bloco,
            'estatisticas' => [
                'total_salas' => $totalSalas,
                'salas_livres' => $salasLivres,
                'salas_em_uso' => $bloco->getSalas()->where(['estado' => 'EmUso'])->count(),
                'percentagem_ocupacao' => $totalSalas > 0 ? round((($totalSalas - $salasLivres) / $totalSalas) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Encontrar modelo
     */
    protected function findModel($id)
    {
        if (($model = Bloco::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('Bloco não encontrado.');
    }
}