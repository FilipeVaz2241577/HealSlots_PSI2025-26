<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Manutencao;

class ManutencaoSearch extends Manutencao
{
    public $equipamentoNome;
    public $userNome;
    public $salaNome;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'equipamento_id', 'user_id', 'sala_id'], 'integer'],
            [['descricao', 'status', 'equipamentoNome', 'userNome', 'salaNome'], 'safe'],
<<<<<<< HEAD
            [['dataInicio', 'dataFim', 'created_at', 'updated_at'], 'safe'],
=======
            [['dataInicio', 'dataFim'], 'safe'],
>>>>>>> origin/filipe
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
     */
    public function search($params)
    {
        $query = Manutencao::find();
        $query->joinWith(['equipamento', 'user', 'sala']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $dataProvider->sort->attributes['equipamentoNome'] = [
<<<<<<< HEAD
            'asc' => ['equipamento.nome' => SORT_ASC],
            'desc' => ['equipamento.nome' => SORT_DESC],
=======
            'asc' => ['equipamento.equipamento' => SORT_ASC],
            'desc' => ['equipamento.equipamento' => SORT_DESC],
>>>>>>> origin/filipe
        ];

        $dataProvider->sort->attributes['userNome'] = [
            'asc' => ['user.username' => SORT_ASC],
            'desc' => ['user.username' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['salaNome'] = [
            'asc' => ['sala.nome' => SORT_ASC],
            'desc' => ['sala.nome' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
<<<<<<< HEAD
            'id' => $this->id,
            'equipamento_id' => $this->equipamento_id,
            'user_id' => $this->user_id,
            'sala_id' => $this->sala_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'descricao', $this->descricao])
            ->andFilterWhere(['like', 'equipamento.nome', $this->equipamentoNome])
=======
            'manutencao.id' => $this->id, // Especificar tabela para id também
            'manutencao.equipamento_id' => $this->equipamento_id,
            'manutencao.user_id' => $this->user_id,
            'manutencao.sala_id' => $this->sala_id,
            'manutencao.status' => $this->status, // ← CORREÇÃO AQUI
        ]);

        $query->andFilterWhere(['like', 'manutencao.descricao', $this->descricao])
            ->andFilterWhere(['like', 'equipamento.equipamento', $this->equipamentoNome])
>>>>>>> origin/filipe
            ->andFilterWhere(['like', 'user.username', $this->userNome])
            ->andFilterWhere(['like', 'sala.nome', $this->salaNome]);

        if ($this->dataInicio) {
<<<<<<< HEAD
            $query->andFilterWhere(['>=', 'dataInicio', $this->dataInicio]);
        }

        if ($this->dataFim) {
            $query->andFilterWhere(['>=', 'dataFim', $this->dataFim]);
=======
            $query->andFilterWhere(['>=', 'manutencao.dataInicio', $this->dataInicio]); // Especificar tabela
        }

        if ($this->dataFim) {
            $query->andFilterWhere(['>=', 'manutencao.dataFim', $this->dataFim]); // Especificar tabela
>>>>>>> origin/filipe
        }

        return $dataProvider;
    }
}