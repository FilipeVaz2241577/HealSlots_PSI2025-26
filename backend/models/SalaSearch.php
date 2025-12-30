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

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros básicos
        $query->andFilterWhere([
            'id' => $this->id,
            'bloco_id' => $this->bloco_id,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'nome', $this->nome]);

        // Se precisar filtrar por nome do bloco, adicionar o JOIN apenas aqui
        if (!empty($this->blocoName)) {
            $query->joinWith(['bloco']);
            $query->andFilterWhere(['like', 'bloco.nome', $this->blocoName]);
        }

        return $dataProvider;
    }

    // REMOVA os métodos afterSave, afterDelete e FazPublishNoMosquitto!
    // Eles devem estar APENAS no modelo principal (common/models/Sala.php)
}