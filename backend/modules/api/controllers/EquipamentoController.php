<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Equipamento;
use common\models\TipoEquipamento;
use Yii;

class EquipamentoController extends ActiveController
{
    public $modelClass = 'common\models\Equipamento';

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
     * Endpoint personalizado: Buscar equipamentos
     * GET /api/equipamento/search?keyword=valor&estado=Operacional&tipoEquipamento_id=1
     */
    public function actionSearch($keyword = null, $estado = null, $tipoEquipamento_id = null)
    {
        $query = Equipamento::find()->joinWith(['tipoEquipamento']);

        if ($keyword) {
            $query->andWhere(['or',
                ['like', 'equipamento', $keyword],
                ['like', 'numeroSerie', $keyword],
                ['like', 'tipoEquipamento.nome', $keyword]
            ]);
        }

        if ($estado) {
            $query->andWhere(['equipamento.estado' => $estado]);
        }

        if ($tipoEquipamento_id) {
            $query->andWhere(['tipoEquipamento_id' => $tipoEquipamento_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'equipamento' => SORT_ASC,
                ]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Equipamentos operacionais (disponíveis)
     * GET /api/equipamento/disponiveis
     */
    public function actionDisponiveis()
    {
        $query = Equipamento::find()
            ->where(['estado' => Equipamento::ESTADO_OPERACIONAL])
            ->joinWith(['tipoEquipamento']);

        return $query->all();
    }

    /**
     * Endpoint para criar equipamento com validação
     * POST /api/equipamento/criar
     */
    public function actionCriar()
    {
        $model = new Equipamento();
        $data = Yii::$app->request->post();

        // Verificar se número de série já existe
        if (isset($data['numeroSerie'])) {
            $existe = Equipamento::find()
                ->where(['numeroSerie' => $data['numeroSerie']])
                ->exists();

            if ($existe) {
                return [
                    'success' => false,
                    'message' => 'Número de série já existe'
                ];
            }
        }

        // Verificar se tipo de equipamento existe
        if (isset($data['tipoEquipamento_id'])) {
            $tipoExiste = TipoEquipamento::find()
                ->where(['id' => $data['tipoEquipamento_id']])
                ->exists();

            if (!$tipoExiste) {
                return [
                    'success' => false,
                    'message' => 'Tipo de equipamento não existe'
                ];
            }
        }

        $model->load($data, '');

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Equipamento criado com sucesso',
                'data' => $model
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao criar equipamento',
                'errors' => $model->errors
            ];
        }
    }

    /**
     * Endpoint para verificar se número de série existe
     * GET /api/equipamento/verificar-serie?numeroSerie=ABC123
     */
    public function actionVerificarSerie($numeroSerie)
    {
        $existe = Equipamento::find()
            ->where(['numeroSerie' => $numeroSerie])
            ->exists();

        return [
            'numero_serie' => $numeroSerie,
            'existe' => $existe,
            'mensagem' => $existe ? 'Número de série já registado' : 'Número de série disponível'
        ];
    }

    /**
     * Encontrar modelo
     */
    protected function findModel($id)
    {
        if (($model = Equipamento::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('Equipamento não encontrado.');
    }
}