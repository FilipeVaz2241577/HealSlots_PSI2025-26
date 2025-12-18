<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Manutencao;
use backend\mosquitto\phpMQTT;

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
            [['dataInicio', 'dataFim'], 'safe'],
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
            'asc' => ['equipamento.equipamento' => SORT_ASC],
            'desc' => ['equipamento.equipamento' => SORT_DESC],
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
            'manutencao.id' => $this->id, // Especificar tabela para id também
            'manutencao.equipamento_id' => $this->equipamento_id,
            'manutencao.user_id' => $this->user_id,
            'manutencao.sala_id' => $this->sala_id,
            'manutencao.status' => $this->status, // ← CORREÇÃO AQUI
        ]);

        $query->andFilterWhere(['like', 'manutencao.descricao', $this->descricao])
            ->andFilterWhere(['like', 'equipamento.equipamento', $this->equipamentoNome])
            ->andFilterWhere(['like', 'user.username', $this->userNome])
            ->andFilterWhere(['like', 'sala.nome', $this->salaNome]);

        if ($this->dataInicio) {
            $query->andFilterWhere(['>=', 'manutencao.dataInicio', $this->dataInicio]); // Especificar tabela
        }

        if ($this->dataFim) {
            $query->andFilterWhere(['>=', 'manutencao.dataFim', $this->dataFim]); // Especificar tabela
        }

        return $dataProvider;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados da manutenção
        $id = $this->id;
        $descricao = $this->descricao;
        $status = $this->status;
        $dataInicio = $this->dataInicio;
        $dataFim = $this->dataFim;
        $equipamento_id = $this->equipamento_id;
        $user_id = $this->user_id;
        $sala_id = $this->sala_id;

        // Criar objeto JSON com dados completos
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->descricao = $descricao;
        $myObj->status = $status;
        $myObj->dataInicio = $dataInicio;
        $myObj->dataFim = $dataFim;
        $myObj->equipamento_id = $equipamento_id;
        $myObj->user_id = $user_id;
        $myObj->sala_id = $sala_id;

        // Adicionar relacionamentos se disponíveis
        if ($this->equipamento) {
            $myObj->equipamento_nome = $this->equipamento->equipamento;
        }

        if ($this->user) {
            $myObj->user_nome = $this->user->username;
        }

        if ($this->sala) {
            $myObj->sala_nome = $this->sala->nome;
        }

        $myJSON = json_encode($myObj);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_MANUTENCAO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_MANUTENCAO", $myJSON);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $manutencao_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $manutencao_id;
        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE_MANUTENCAO", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher-manutencao-" . uniqid(); // ID único com timestamp

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();
            return true;
        } else {
            // Log de erro
            error_log("Falha na conexão MQTT para o canal: $canal");
            file_put_contents("debug_mosquitto_manutencao.log",
                "[" . date('Y-m-d H:i:s') . "] Time out ao publicar em $canal\n",
                FILE_APPEND);
            return false;
        }
    }
}