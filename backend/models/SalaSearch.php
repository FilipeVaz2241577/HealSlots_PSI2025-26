<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sala;

class SalaSearch extends Sala
{
    public $blocoName;

    public function rules()
    {
        return [
            [['id', 'bloco_id'], 'integer'],
            [['nome', 'estado', 'blocoName'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
<<<<<<< HEAD
        $query = Sala::find()->joinWith(['bloco']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['nome' => SORT_ASC]]
        ]);

        // Configurar ordenação para bloco name
        $dataProvider->sort->attributes['blocoName'] = [
            'asc' => ['bloco.nome' => SORT_ASC],
            'desc' => ['bloco.nome' => SORT_DESC],
        ];

=======
        // Query SIMPLES sem alias - vamos evitar complexidade
        $query = Sala::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['nome' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

>>>>>>> origin/filipe
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

<<<<<<< HEAD
        $query->andFilterWhere([
            'sala.id' => $this->id,
=======
        // Filtros básicos
        $query->andFilterWhere([
            'id' => $this->id,
>>>>>>> origin/filipe
            'bloco_id' => $this->bloco_id,
            'estado' => $this->estado,
        ]);

<<<<<<< HEAD
        $query->andFilterWhere(['like', 'sala.nome', $this->nome])
            ->andFilterWhere(['like', 'bloco.nome', $this->blocoName]);
=======
        $query->andFilterWhere(['like', 'nome', $this->nome]);

        // Se precisar filtrar por nome do bloco, adicionar o JOIN apenas aqui
        if (!empty($this->blocoName)) {
            $query->joinWith(['bloco']);
            $query->andFilterWhere(['like', 'bloco.nome', $this->blocoName]);
        }
>>>>>>> origin/filipe

        return $dataProvider;
    }
}