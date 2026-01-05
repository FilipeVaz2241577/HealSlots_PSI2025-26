<?php

namespace common\models;

use Yii;

/**
 * Esta é a classe de modelo para a tabela "tipoEquipamento".
 * Representa os tipos/categorias de equipamentos disponíveis no sistema.
 *
 * @property int $id - Identificador único do tipo de equipamento
 * @property string $nome - Nome do tipo de equipamento
 *
 * @property Equipamento[] $equipamentos - Relação com os equipamentos deste tipo
 */
class TipoEquipamento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%tipoEquipamento}}'; // Nome da tabela na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // nome é obrigatório
            [['nome'], 'required'],
            // nome é uma string com máximo de 100 caracteres
            [['nome'], 'string', 'max' => 100],
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
            'nome' => 'Nome', // Rótulo para o nome do tipo de equipamento
        ];
    }

    /**
     * Obtém a relação com os equipamentos deste tipo
     * @return \yii\db\ActiveQuery - Query para obter os equipamentos
     */
    public function getEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['tipoEquipamento_id' => 'id']);
    }

    /**
     * Obtém todos os tipos de equipamento como array para dropdowns/selects
     * @return array - Array no formato [id => nome] para usar em dropdowns
     */
    public static function getTiposArray()
    {
        return self::find()->select(['nome', 'id'])->indexBy('id')->column();
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

        // Preparar dados do tipo de equipamento para envio MQTT
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        // Adicionar contagem de equipamentos deste tipo
        $data['total_equipamentos'] = $this->getEquipamentos()->count();

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE); // Converter para JSON

        // Publicar no Mosquitto MQTT baseado no tipo de operação
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_TIPO_EQUIPAMENTO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_TIPO_EQUIPAMENTO", $myJSON);
        }
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Preparar dados para notificar a eliminação
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'deleted_at' => date('Y-m-d H:i:s'), // Data da eliminação
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Publicar no tópico de eliminação
        $this->FazPublishNoMosquitto("DELETE_TIPO_EQUIPAMENTO", $myJSON);
    }

    /**
     * Publica uma mensagem no servidor Mosquitto MQTT
     * @param string $canal - Nome do tópico/canal MQTT
     * @param string $msg - Mensagem a publicar (em formato JSON)
     * @return bool - True se a publicação for bem-sucedida
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
            $client_id = "yii_tipoequipamento_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT TipoEquipamento: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_catalogo.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT TipoEquipamento: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT TipoEquipamento Exception: " . $e->getMessage());
            return false;
        }
    }
}