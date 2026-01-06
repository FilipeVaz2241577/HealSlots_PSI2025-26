<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use common\models\Sala;
use yii\data\ActiveDataProvider;
use Yii;

class SalaController extends ActiveController
{
    public $modelClass = 'common\models\Sala';

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

    /**
     * Endpoint personalizado: Salas disponíveis para reserva
     * GET /api/sala/disponiveis?data_inicio=2024-01-20 14:00:00&data_fim=2024-01-20 16:00:00
     */
    public function actionDisponiveis($data_inicio = null, $data_fim = null)
    {
        $query = Sala::find()
            ->where(['estado' => 'Livre'])
            ->joinWith(['bloco']);

        // Se tiver datas, verificar conflitos com requisições
        if ($data_inicio && $data_fim) {
            $query->andWhere(['not exists',
                \common\models\Requisicao::find()
                    ->where('requisicao.sala_id = sala.id')
                    ->andWhere(['requisicao.status' => 'Ativa'])
                    ->andWhere(['or',
                        ['between', 'dataInicio', $data_inicio, $data_fim],
                        ['between', 'dataFim', $data_inicio, $data_fim]
                    ])
            ]);
        }

        return $query->all();
    }

    /**
     * Endpoint personalizado: Detalhes de uma sala
     * GET /api/sala/{id}/detalhes
     */
    public function actionDetalhes($id)
    {
        $sala = $this->findModel($id);

        return [
            'sala' => $sala,
            'bloco' => $sala->bloco,
            'estado_label' => $sala->getEstadoLabel(),
            'is_disponivel' => $sala->isDisponivelParaReserva(),
        ];
    }

    /**
     * Encontrar modelo
     */
    protected function findModel($id)
    {
        if (($model = Sala::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('Sala não encontrada.');
    }
}