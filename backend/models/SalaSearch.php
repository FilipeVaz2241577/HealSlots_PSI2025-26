<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sala;
use backend\mosquitto\phpMQTT;

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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados da sala
        $id = $this->id;
        $nome = $this->nome;
        $estado = $this->estado;
        $bloco_id = $this->bloco_id;
        $capacidade = $this->capacidade;
        $equipamentos = $this->equipamentos;
        $descricao = $this->descricao;

        // Criar objeto JSON com dados completos
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->nome = $nome;
        $myObj->estado = $estado;
        $myObj->bloco_id = $bloco_id;
        $myObj->capacidade = $capacidade;
        $myObj->equipamentos = $equipamentos;
        $myObj->descricao = $descricao;

        // Adicionar relacionamentos se disponíveis
        if ($this->bloco) {
            $myObj->bloco_nome = $this->bloco->nome;
            $myObj->bloco_estado = $this->bloco->estado;
        }

        // Adicionar informações de equipamentos se disponíveis
        if ($this->equipamentosList) {
            $equipamentosArray = [];
            foreach ($this->equipamentosList as $equipamento) {
                $equipamentosArray[] = [
                    'id' => $equipamento->id,
                    'nome' => $equipamento->equipamento,
                    'estado' => $equipamento->estado
                ];
            }
            $myObj->equipamentos_detalhados = $equipamentosArray;
        }

        $myJSON = json_encode($myObj, JSON_UNESCAPED_UNICODE);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_SALA", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_SALA", $myJSON);

            // Notificação específica para mudança de estado
            if (isset($changedAttributes['estado'])) {
                $oldEstado = $changedAttributes['estado'];
                $newEstado = $this->estado;

                $estadoObj = new \stdClass();
                $estadoObj->id = $this->id;
                $estadoObj->nome = $nome;
                $estadoObj->old_estado = $oldEstado;
                $estadoObj->new_estado = $newEstado;
                $estadoObj->bloco_id = $bloco_id;

                if ($this->bloco) {
                    $estadoObj->bloco_nome = $this->bloco->nome;
                }

                $estadoJSON = json_encode($estadoObj, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("ESTADO_CHANGED_SALA", $estadoJSON);
            }

            // Notificação específica para mudança de capacidade
            if (isset($changedAttributes['capacidade'])) {
                $oldCapacidade = $changedAttributes['capacidade'];
                $newCapacidade = $this->capacidade;

                $capacidadeObj = new \stdClass();
                $capacidadeObj->id = $this->id;
                $capacidadeObj->nome = $nome;
                $capacidadeObj->old_capacidade = $oldCapacidade;
                $capacidadeObj->new_capacidade = $newCapacidade;

                $capacidadeJSON = json_encode($capacidadeObj, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("CAPACIDADE_CHANGED_SALA", $capacidadeJSON);
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $sala_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $sala_id;
        $myObj->nome = $this->nome;
        $myObj->bloco_id = $this->bloco_id;

        if ($this->bloco) {
            $myObj->bloco_nome = $this->bloco->nome;
        }

        $myJSON = json_encode($myObj, JSON_UNESCAPED_UNICODE);

        $this->FazPublishNoMosquitto("DELETE_SALA", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher-sala-" . uniqid(); // ID único

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();

            // Log de sucesso (opcional, para debug)
            if (defined('YII_DEBUG') && YII_DEBUG) {
                file_put_contents("debug_mosquitto_sala_success.log",
                    "[" . date('Y-m-d H:i:s') . "] Publicado em $canal: " . substr($msg, 0, 100) . "...\n",
                    FILE_APPEND);
            }

            return true;
        } else {
            // Log de erro
            error_log("Falha na conexão MQTT para o canal: $canal");
            file_put_contents("debug_mosquitto_sala_error.log",
                "[" . date('Y-m-d H:i:s') . "] Time out ao publicar em $canal\n",
                FILE_APPEND);
            return false;
        }
    }
}