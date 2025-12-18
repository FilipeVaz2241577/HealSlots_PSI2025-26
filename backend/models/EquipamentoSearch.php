<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Equipamento;
use backend\mosquitto\phpMQTT;

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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados do registo
        $id = $this->id;
        $numeroSerie = $this->numeroSerie;
        $equipamento = $this->equipamento;
        $estado = $this->estado;
        $tipoEquipamento_id = $this->tipoEquipamento_id;

        // Criar objeto JSON
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->numeroSerie = $numeroSerie;
        $myObj->equipamento = $equipamento;
        $myObj->estado = $estado;
        $myObj->tipoEquipamento_id = $tipoEquipamento_id;

        $myJSON = json_encode($myObj);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_EQUIPAMENTO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_EQUIPAMENTO", $myJSON);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $prod_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $prod_id;
        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE_EQUIPAMENTO", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher"; // único para cada publicador

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
        } else {
            file_put_contents("debug_mosquitto.output", "Time out!");
        }
    }
}