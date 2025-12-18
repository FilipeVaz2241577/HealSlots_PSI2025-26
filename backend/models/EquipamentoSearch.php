<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Equipamento;

class EquipamentoSearch extends Equipamento
{
    public $tipoEquipamentoName;

    public function rules()
    {
        return [
            [['id', 'tipoEquipamento_id'], 'integer'],
            [['numeroSerie', 'equipamento', 'estado', 'tipoEquipamentoName'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        // Usar JOIN para permitir filtros por tipoEquipamento
        $query = Equipamento::find()->joinWith(['tipoEquipamento']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // Configurar ordenação CORRETAMENTE sem usar aliases complexos
        $dataProvider->sort->attributes['tipoEquipamentoName'] = [
            'asc' => ['tipoEquipamento.nome' => SORT_ASC],
            'desc' => ['tipoEquipamento.nome' => SORT_DESC],
        ];

        $this->load($params);

        if (!$this->validate()) {
            // Se a validação falhar, remover o JOIN para evitar ambiguidade
            $query->joinWith(['tipoEquipamento' => function($q) {
                $q->where('0=1'); // JOIN vazio
            }]);
            return $dataProvider;
        }

        // Para evitar ambiguidade, especificar a tabela quando houver JOIN
        // Mas de forma segura
        $query->andFilterWhere([
            'equipamento.id' => $this->id, // Especificar tabela
            'equipamento.tipoEquipamento_id' => $this->tipoEquipamento_id,
            'equipamento.estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'equipamento.numeroSerie', $this->numeroSerie])
            ->andFilterWhere(['like', 'equipamento.equipamento', $this->equipamento])
            ->andFilterWhere(['like', 'tipoEquipamento.nome', $this->tipoEquipamentoName]);

        return $dataProvider;
    }
}