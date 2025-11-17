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

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'sala.id' => $this->id,
            'bloco_id' => $this->bloco_id,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'sala.nome', $this->nome])
            ->andFilterWhere(['like', 'bloco.nome', $this->blocoName]);

        return $dataProvider;
    }
}