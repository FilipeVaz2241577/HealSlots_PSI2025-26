<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Requisicao;
use backend\mosquitto\phpMQTT;

class RequisicaoSearch extends Requisicao
{
    public $sala_nome;
    public $user_name;
    public $bloco_nome;
    // Remover as propriedades range e manter apenas as datas simples
    // public $dataInicio_range;
    // public $dataFim_range;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'sala_id'], 'integer'],
            [['status'], 'string'],
            [['dataInicio', 'dataFim', 'sala_nome', 'user_name', 'bloco_nome'], 'safe'],
            // Remover range das regras
            // [['dataInicio_range', 'dataFim_range'], 'safe'],
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

        // Filtro por data de início específica - converter dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataInicio) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataInicio);
            if ($dataFormatada) {
                $query->andFilterWhere(['DATE(requisicao.dataInicio)' => $dataFormatada]);
            }
        }

        // Filtro por data de fim específica - converter dd/mm/aaaa para aaaa-mm-dd
        if ($this->dataFim) {
            $dataFormatada = $this->converterDataParaMySQL($this->dataFim);
            if ($dataFormatada) {
                $query->andFilterWhere(['DATE(requisicao.dataFim)' => $dataFormatada]);
            }
        }

        // Filtros de texto
        $query->andFilterWhere(['like', 'sala.nome', $this->sala_nome])
            ->andFilterWhere(['like', 'user.username', $this->user_name])
            ->andFilterWhere(['like', 'bloco.nome', $this->bloco_nome]);

        return $dataProvider;
    }

    /**
     * Converte data de dd/mm/aaaa para aaaa-mm-dd
     * @param string $data Data no formato dd/mm/aaaa
     * @return string|null Data no formato aaaa-mm-dd ou null se inválida
     */
    private function converterDataParaMySQL($data)
    {
        if (empty($data)) {
            return null;
        }

        // Verificar se já está no formato aaaa-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return $data;
        }

        // Tentar converter de dd/mm/aaaa para aaaa-mm-dd
        $partes = explode('/', $data);
        if (count($partes) === 3) {
            $dia = $partes[0];
            $mes = $partes[1];
            $ano = $partes[2];

            // Validar se são números
            if (is_numeric($dia) && is_numeric($mes) && is_numeric($ano)) {
                // Garantir 2 dígitos para dia e mês
                $dia = str_pad($dia, 2, '0', STR_PAD_LEFT);
                $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

                // Verificar se é uma data válida
                if (checkdate((int)$mes, (int)$dia, (int)$ano)) {
                    return $ano . '-' . $mes . '-' . $dia;
                }
            }
        }
        return null;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados da requisição
        $id = $this->id;
        $status = $this->status;
        $dataInicio = $this->dataInicio;
        $dataFim = $this->dataFim;
        $user_id = $this->user_id;
        $sala_id = $this->sala_id;
        $observacoes = $this->observacoes;

        // Criar objeto JSON com dados completos
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->status = $status;
        $myObj->dataInicio = $dataInicio;
        $myObj->dataFim = $dataFim;
        $myObj->user_id = $user_id;
        $myObj->sala_id = $sala_id;
        $myObj->observacoes = $observacoes;

        // Adicionar relacionamentos se disponíveis
        if ($this->sala) {
            $myObj->sala_nome = $this->sala->nome;
            $myObj->sala_capacidade = $this->sala->capacidade;

            // Adicionar bloco se disponível
            if ($this->sala->bloco) {
                $myObj->bloco_nome = $this->sala->bloco->nome;
            }
        }

        if ($this->user) {
            $myObj->user_name = $this->user->username;
            $myObj->user_email = $this->user->email;
        }

        $myJSON = json_encode($myObj);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_REQUISICAO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_REQUISICAO", $myJSON);

            // Notificação específica para mudança de status
            if (isset($changedAttributes['status'])) {
                $oldStatus = $changedAttributes['status'];
                $newStatus = $this->status;

                $statusObj = new \stdClass();
                $statusObj->id = $this->id;
                $statusObj->old_status = $oldStatus;
                $statusObj->new_status = $newStatus;
                $statusObj->dataInicio = $dataInicio;
                $statusObj->sala_id = $sala_id;

                if ($this->sala) {
                    $statusObj->sala_nome = $this->sala->nome;
                }

                $statusJSON = json_encode($statusObj);
                $this->FazPublishNoMosquitto("STATUS_CHANGED_REQUISICAO", $statusJSON);
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $requisicao_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $requisicao_id;

        // Adicionar informações adicionais para contexto
        $myObj->dataInicio = $this->dataInicio;
        $myObj->sala_id = $this->sala_id;

        if ($this->sala) {
            $myObj->sala_nome = $this->sala->nome;
        }

        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE_REQUISICAO", $myJSON);
    }

    public function FazPublishNoMosquitto($canal, $msg)
    {
        $server = "127.0.0.1";   // ou localhost
        $port = 1883;
        $username = "";          // se tiver autenticação
        $password = "";
        $client_id = "phpMQTT-publisher-requisicao-" . uniqid(); // ID único

        $mqtt = new phpMQTT($server, $port, $client_id);

        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($canal, $msg, 0);
            $mqtt->close();

            // Log de sucesso (opcional)
            file_put_contents("debug_mosquitto_requisicao_success.log",
                "[" . date('Y-m-d H:i:s') . "] Publicado em $canal\n",
                FILE_APPEND);

            return true;
        } else {
            // Log de erro
            error_log("Falha na conexão MQTT para o canal: $canal");
            file_put_contents("debug_mosquitto_requisicao_error.log",
                "[" . date('Y-m-d H:i:s') . "] Time out ao publicar em $canal\n",
                FILE_APPEND);
            return false;
        }
    }
}