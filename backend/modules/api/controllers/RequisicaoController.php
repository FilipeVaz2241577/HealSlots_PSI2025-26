<?php
namespace backend\modules\api\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;
use common\models\Sala;
use Yii;

class RequisicaoController extends ActiveController
{
    public $modelClass = 'common\models\Requisicao';

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
     * Endpoint personalizado: Buscar requisições
     * GET /api/requisicao/search?keyword=valor&status=Ativa&user_id=1
     */
    public function actionSearch($keyword = null, $status = null, $user_id = null, $sala_id = null)
    {
        $query = Requisicao::find()->joinWith(['user', 'sala.bloco']);

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

        if ($sala_id) {
            $query->andWhere(['sala_id' => $sala_id]);
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

    /**
     * Endpoint para criar requisição com validação
     * POST /api/requisicao/criar
     */
    public function actionCriar()
    {
        $model = new Requisicao();
        $data = Yii::$app->request->post();

        // Carregar dados
        $model->load($data, '');

        // Definir status padrão se não vier
        if (!isset($data['status'])) {
            $model->status = Requisicao::STATUS_ATIVA;
        }

        // Validar se sala existe
        if (isset($data['sala_id'])) {
            $sala = Sala::findOne($data['sala_id']);
            if (!$sala) {
                return [
                    'success' => false,
                    'message' => 'Sala não encontrada'
                ];
            }

            // Verificar se sala está livre
            if ($sala->estado !== Sala::ESTADO_LIVRE) {
                return [
                    'success' => false,
                    'message' => 'Sala não está disponível'
                ];
            }
        }

        // Validar datas
        if ($model->dataInicio && $model->dataFim) {
            $inicio = strtotime($model->dataInicio);
            $fim = strtotime($model->dataFim);

            if ($fim <= $inicio) {
                return [
                    'success' => false,
                    'message' => 'Data de fim deve ser posterior à data de início'
                ];
            }
        }

        if ($model->save()) {
            // Atualizar estado da sala para "Em Uso"
            $model->atualizarEstadoSala();

            return [
                'success' => true,
                'message' => 'Requisição criada com sucesso',
                'data' => $model
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao criar requisição',
                'errors' => $model->errors
            ];
        }
    }

    /**
     * Minhas requisições
     * GET /api/requisicao/minhas?user_id=1&status=Ativa
     */
    public function actionMinhas($user_id = null, $status = null)
    {
        if (!$user_id) {
            return [
                'success' => false,
                'message' => 'Parâmetro user_id é obrigatório'
            ];
        }

        $query = Requisicao::find()
            ->where(['user_id' => $user_id])
            ->joinWith(['sala.bloco']);

        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        return $query->orderBy(['dataInicio' => SORT_DESC])->all();
    }

    /**
     * Concluir requisição
     * POST /api/requisicao/{id}/concluir
     */
    public function actionConcluir($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Requisicao::STATUS_ATIVA) {
            return [
                'success' => false,
                'message' => 'Apenas requisições ativas podem ser concluídas'
            ];
        }

        if ($model->marcarComoConcluida()) {
            return [
                'success' => true,
                'message' => 'Requisição concluída com sucesso',
                'data' => $model
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao concluir requisição',
                'errors' => $model->errors
            ];
        }
    }

    /**
     * Cancelar requisição
     * POST /api/requisicao/{id}/cancelar
     */
    public function actionCancelar($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Requisicao::STATUS_ATIVA) {
            return [
                'success' => false,
                'message' => 'Apenas requisições ativas podem ser canceladas'
            ];
        }

        if ($model->marcarComoCancelada()) {
            return [
                'success' => true,
                'message' => 'Requisição cancelada com sucesso',
                'data' => $model
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erro ao cancelar requisição',
                'errors' => $model->errors
            ];
        }
    }

    /**
     * Verificar disponibilidade da sala
     * GET /api/requisicao/verificar-disponibilidade?sala_id=1&dataInicio=2024-01-20T14:00&dataFim=2024-01-20T16:00
     */
    public function actionVerificarDisponibilidade($sala_id, $dataInicio, $dataFim = null)
    {
        // Criar modelo temporário para validação
        $model = new Requisicao();
        $model->sala_id = $sala_id;
        $model->dataInicio = $dataInicio;
        $model->dataFim = $dataFim;

        $disponivel = $model->validarDisponibilidade();

        $sala = Sala::findOne($sala_id);

        return [
            'sala_id' => $sala_id,
            'sala_nome' => $sala ? $sala->nome : null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'disponivel' => $disponivel,
            'mensagem' => $disponivel ? 'Sala disponível' : 'Sala não disponível'
        ];
    }

    /**
     * Encontrar modelo
     */
    protected function findModel($id)
    {
        if (($model = Requisicao::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException('Requisição não encontrada.');
    }
}