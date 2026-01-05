<?php

namespace common\models;

use Yii;
use backend\mosquitto\phpMQTT;

/**
 * Esta é a classe de modelo para a tabela "bloco".
 *
 * @property int $id - Identificador único do bloco
 * @property string $nome - Nome do bloco
 * @property string|null $estado - Estado do bloco (ativo/inativo)
 *
 * @property Sala[] $salas - Relação com as salas pertencentes a este bloco
 */
class Bloco extends \yii\db\ActiveRecord
{
    /**
     * Valores do campo ENUM para o estado
     */
    const ESTADO_ATIVO = 'ativo';
    const ESTADO_DESATIVADO = 'inativo';

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return 'bloco'; // Nome da tabela na base de dados
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Define o valor padrão para o estado como 'ativo'
            [['estado'], 'default', 'value' => 'ativo'],
            // O nome é obrigatório
            [['nome'], 'required', 'message' => 'O nome do bloco é obrigatório.'],
            // O estado deve ser uma string
            [['estado'], 'string'],
            // O nome não pode exceder 100 caracteres
            [['nome'], 'string', 'max' => 100, 'tooLong' => 'O nome não pode exceder 100 caracteres.'],
            // O estado deve ser um dos valores permitidos
            ['estado', 'in', 'range' => array_keys(self::optsEstado()), 'message' => 'Estado inválido.'],
            // O nome deve ser único na base de dados
            [['nome'], 'unique', 'message' => 'Já existe um bloco com este nome. Por favor, escolha outro nome.'],
        ];
    }

    /**
     * {@inheritdoc}
     * Define os rótulos para os atributos (usados em formulários)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID', // Rótulo para o ID
            'nome' => 'Nome', // Rótulo para o nome
            'estado' => 'Estado', // Rótulo para o estado
        ];
    }

    /**
     * Obtém a relação com as salas pertencentes a este bloco
     *
     * @return \yii\db\ActiveQuery - Query para obter as salas
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['bloco_id' => 'id']);
    }

    /**
     * Obtém os rótulos para os valores do ENUM do estado
     * @return string[] - Array com os pares chave-valor do estado
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVO => 'Ativo', // Rótulo para estado ativo
            self::ESTADO_DESATIVADO => 'inativo', // Rótulo para estado inativo
        ];
    }

    /**
     * Obtém o rótulo do estado atual
     * @return string - Rótulo do estado ou 'Desconhecido' se não existir
     */
    public function getEstadoLabel()
    {
        return self::optsEstado()[$this->estado] ?? 'Desconhecido';
    }

    /**
     * Sobrescreve a validação beforeSave para garantir unicidade do nome
     * Executa antes de salvar o registo
     */
    public function beforeSave($insert)
    {
        // Primeiro verifica se a validação do parente passa
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Verificar unicidade do nome antes de salvar
        if ($this->isNewRecord) {
            // Para um novo registo: verifica se já existe um bloco com este nome
            $exists = self::find()->where(['nome' => $this->nome])->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        } else {
            // Para atualização: verifica se já existe outro bloco com este nome (excluindo o atual)
            $exists = self::find()
                ->where(['nome' => $this->nome])
                ->andWhere(['!=', 'id', $this->id])
                ->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        }

        return true; // Permite a operação de save
    }

    /**
     * Verifica se o bloco está ativo
     * @return bool - True se o estado for 'ativo'
     */
    public function isEstadoAtivo()
    {
        return $this->estado === self::ESTADO_ATIVO;
    }

    /**
     * Define o estado do bloco como ativo
     */
    public function setEstadoToAtivo()
    {
        $this->estado = self::ESTADO_ATIVO;
    }

    /**
     * Verifica se o bloco está desativado
     * @return bool - True se o estado for 'inativo'
     */
    public function isEstadoDesativado()
    {
        return $this->estado === self::ESTADO_DESATIVADO;
    }

    /**
     * Define o estado do bloco como desativado
     */
    public function setEstadoToDesativado()
    {
        $this->estado = self::ESTADO_DESATIVADO;
    }

    // ==============================================
    // MÉTODOS PARA COMUNICAÇÃO MQTT
    // ==============================================

    /**
     * {@inheritdoc}
     * Executa após salvar o registo (tanto inserção como atualização)
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados do registo para enviar via MQTT
        $id = $this->id; // ID do bloco
        $nome = $this->nome; // Nome do bloco
        $estado = $this->estado; // Estado do bloco

        // Criar objeto JSON com os dados do bloco
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->nome = $nome;
        $myObj->estado = $estado;

        $myJSON = json_encode($myObj); // Converter objeto para JSON

        // Publicar no Mosquitto MQTT
        if ($insert) {
            // Se for uma inserção, publica no tópico INSERT_BLOCO
            $this->FazPublishNoMosquitto("INSERT_BLOCO", $myJSON);
        } else {
            // Se for uma atualização, publica no tópico UPDATE_BLOCO
            $this->FazPublishNoMosquitto("UPDATE_BLOCO", $myJSON);
        }
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Obter o ID do bloco eliminado
        $bloco_id = $this->id;

        // Criar objeto JSON apenas com o ID
        $myObj = new \stdClass();
        $myObj->id = $bloco_id;
        $myJSON = json_encode($myObj);

        // Publicar no tópico DELETE_BLOCO
        $this->FazPublishNoMosquitto("DELETE_BLOCO", $myJSON);
    }

    /**
     * Publica uma mensagem no servidor Mosquitto MQTT
     * @param string $canal - Nome do tópico/canal MQTT
     * @param string $msg - Mensagem a publicar (em formato JSON)
     * @return bool - True se a publicação for bem-sucedida, False caso contrário
     */
    public function FazPublishNoMosquitto($canal, $msg)
    {
        try {
            // Caminho ABSOLUTO para o ficheiro phpMQTT.php
            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            // Verificar se o ficheiro existe
            if (!file_exists($phpMQTTPath)) {
                error_log("MQTT ERRO: Arquivo não encontrado: $phpMQTTPath");
                return false;
            }

            // Incluir a biblioteca MQTT
            require_once $phpMQTTPath;

            // Configurações do servidor MQTT
            $server = "127.0.0.1"; // Endereço do servidor Mosquitto (localhost)
            $port = 1883; // Porta padrão do MQTT
            $client_id = "yii_bloco_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT Bloco: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_bloco.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT Bloco: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT Bloco Exception: " . $e->getMessage());
            return false;
        }
    }
}