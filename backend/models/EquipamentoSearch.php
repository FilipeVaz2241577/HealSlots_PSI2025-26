<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Equipamento;

class EquipamentoSearch extends Equipamento
{
<<<<<<< HEAD
    /**
     * {@inheritdoc}
     */
=======
    public $tipoEquipamentoName;

>>>>>>> origin/filipe
    public function rules()
    {
        return [
            [['id', 'tipoEquipamento_id'], 'integer'],
<<<<<<< HEAD
            [['numeroSerie', 'equipamento', 'estado'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
=======
            [['numeroSerie', 'equipamento', 'estado', 'tipoEquipamentoName'], 'safe'],
        ];
    }

>>>>>>> origin/filipe
    public function scenarios()
    {
        return Model::scenarios();
    }

<<<<<<< HEAD
    /**
     * Creates data provider instance with search query applied
     */
    public function search($params)
    {
        $query = Equipamento::find();
        $query->joinWith(['tipoEquipamento']);
=======
    public function search($params)
    {
        // Usar JOIN para permitir filtros por tipoEquipamento
        $query = Equipamento::find()->joinWith(['tipoEquipamento']);
>>>>>>> origin/filipe

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

<<<<<<< HEAD
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'tipoEquipamento_id' => $this->tipoEquipamento_id,
            'estado' => $this->estado,
        ]);

        $query->andFilterWhere(['like', 'numeroSerie', $this->numeroSerie])
            ->andFilterWhere(['like', 'equipamento', $this->equipamento]);
=======
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
>>>>>>> origin/filipe

        return $dataProvider;
    }
}