<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;

class RequisicaoSearch extends Requisicao
{
    public $sala_nome;
    public $user_name;
    public $bloco_nome;
    public $dataInicio_range;
    public $dataFim_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sala_id'], 'integer'],
            [['status'], 'string'],
            [['dataInicio', 'dataFim', 'sala_nome', 'user_name', 'bloco_nome'], 'safe'],
            [['dataInicio_range', 'dataFim_range'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Requisicao::find()
            ->joinWith(['sala', 'user'])
            ->leftJoin('bloco', 'sala.bloco_id = bloco.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['dataInicio' => SORT_DESC],
                'attributes' => [
                    'id',
                    'dataInicio',
                    'dataFim',
                    'status',
                    'sala_nome' => [
                        'asc' => ['sala.nome' => SORT_ASC],
                        'desc' => ['sala.nome' => SORT_DESC],
                    ],
                    'user_name' => [
                        'asc' => ['user.username' => SORT_ASC],
                        'desc' => ['user.username' => SORT_DESC],
                    ],
                    'bloco_nome' => [
                        'asc' => ['bloco.nome' => SORT_ASC],
                        'desc' => ['bloco.nome' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros normais
        $query->andFilterWhere([
            'requisicao.id' => $this->id,
            'requisicao.user_id' => $this->user_id,
            'requisicao.sala_id' => $this->sala_id,
            'requisicao.status' => $this->status,
        ]);

        // Filtro por intervalo de data de início
        if ($this->dataInicio_range) {
            list($start, $end) = explode(' - ', $this->dataInicio_range);
            $query->andFilterWhere(['between', 'requisicao.dataInicio', $start, $end]);
        } elseif ($this->dataInicio) {
            $query->andFilterWhere(['date(requisicao.dataInicio)' => $this->dataInicio]);
        }

        // Filtro por intervalo de data de fim
        if ($this->dataFim_range) {
            list($start, $end) = explode(' - ', $this->dataFim_range);
            $query->andFilterWhere(['between', 'requisicao.dataFim', $start, $end]);
        } elseif ($this->dataFim) {
            $query->andFilterWhere(['date(requisicao.dataFim)' => $this->dataFim]);
        }

        // Filtros de texto
        $query->andFilterWhere(['like', 'sala.nome', $this->sala_nome])
            ->andFilterWhere(['like', 'user.username', $this->user_name])
            ->andFilterWhere(['like', 'bloco.nome', $this->bloco_nome]);

        return $dataProvider;
    }
}