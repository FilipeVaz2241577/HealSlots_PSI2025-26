<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use backend\mosquitto\phpMQTT;

/**
 * Esta é a classe de modelo para a tabela "sala".
 * Representa as salas disponíveis no sistema.
 *
 * @property int $id - Identificador único da sala
 * @property string $nome - Nome da sala
 * @property string $estado - Estado atual da sala (Livre, EmUso, etc.)
 * @property int $bloco_id - ID do bloco a que pertence (chave estrangeira)
 *
 * @property Bloco $bloco - Relação com o bloco a que pertence
 */
class Sala extends ActiveRecord
{
    // Constantes para os estados possíveis da sala
    const ESTADO_LIVRE = 'Livre';
    const ESTADO_EM_USO = 'EmUso';
    const ESTADO_MANUTENCAO = 'Manutencao';
    const ESTADO_DESATIVADA = 'Desativada';

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%sala}}'; // Nome da tabela na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define os comportamentos do modelo
     */
    public function behaviors()
    {
        return []; // Pode adicionar comportamentos aqui se necessário
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // nome e bloco_id são obrigatórios
            [['nome', 'bloco_id'], 'required'],
            // bloco_id deve ser um inteiro
            [['bloco_id'], 'integer'],
            // nome é uma string com máximo de 100 caracteres
            [['nome'], 'string', 'max' => 100],
            // estado é uma string com máximo de 20 caracteres
            [['estado'], 'string', 'max' => 20],
            // Valor padrão para estado é 'Livre'
            [['estado'], 'default', 'value' => self::ESTADO_LIVRE],
            // estado deve ser um dos valores permitidos
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            // Validação de existência do bloco (chave estrangeira)
            [['bloco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bloco::class, 'targetAttribute' => ['bloco_id' => 'id']],
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
            'nome' => 'Nome', // Rótulo para o nome da sala
            'estado' => 'Estado', // Rótulo para o estado da sala
            'bloco_id' => 'Bloco', // Rótulo para o ID do bloco
            'blocoName' => 'Bloco', // Rótulo para o nome do bloco (propriedade virtual)
        ];
    }

    /**
     * Obtém a relação com o bloco a que pertence
     * @return \yii\db\ActiveQuery - Query para obter o bloco
     */
    public function getBloco()
    {
        return $this->hasOne(Bloco::class, ['id' => 'bloco_id']);
    }

    /**
     * Obtém o nome do bloco (propriedade virtual)
     * @return string - Nome do bloco ou 'N/A' se não existir
     */
    public function getBlocoName()
    {
        return $this->bloco ? $this->bloco->nome : 'N/A';
    }

    /**
     * Obtém os rótulos para os valores do ENUM do estado
     * @return string[] - Array com os pares chave-valor dos estados
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_LIVRE => 'Livre',
            self::ESTADO_EM_USO => 'Em Uso',          // Nota: "EmUso" mapeado para "Em Uso"
            self::ESTADO_MANUTENCAO => 'Em Manutenção',
            self::ESTADO_DESATIVADA => 'Desativada',
        ];
    }

    /**
     * Obtém o rótulo do estado atual
     * @return string - Rótulo do estado ou 'Desconhecido' se não existir
     */
    public function getEstadoLabel()
    {
        $opts = self::optsEstado();

        // Verificar exatamente o valor armazenado
        if (isset($opts[$this->estado])) {
            return $opts[$this->estado];
        }

        // Se não encontrar, verificar case-insensitive (para compatibilidade)
        $estadoLower = strtolower($this->estado);
        foreach ($opts as $key => $label) {
            if (strtolower($key) === $estadoLower) {
                return $label;
            }
        }

        return 'Desconhecido (' . $this->estado . ')'; // Fallback com valor original
    }

    /**
     * Verifica se a sala está livre
     * @return bool - True se o estado for 'Livre'
     */
    public function isEstadoLivre()
    {
        return $this->estado === self::ESTADO_LIVRE;
    }

    /**
     * Define o estado da sala como livre
     */
    public function setEstadoToLivre()
    {
        $this->estado = self::ESTADO_LIVRE;
    }

    /**
     * Verifica se a sala está em uso
     * @return bool - True se o estado for 'EmUso'
     */
    public function isEstadoEmUso()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    /**
     * Define o estado da sala como em uso
     */
    public function setEstadoToEmUso()
    {
        $this->estado = self::ESTADO_EM_USO;
    }

    /**
     * Verifica se a sala está em manutenção
     * @return bool - True se o estado for 'Manutencao'
     */
    public function isEstadoManutencao()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    /**
     * Define o estado da sala como em manutenção
     */
    public function setEstadoToManutencao()
    {
        $this->estado = self::ESTADO_MANUTENCAO;
    }

    /**
     * Verifica se a sala está desativada
     * @return bool - True se o estado for 'Desativada'
     */
    public function isEstadoDesativada()
    {
        return $this->estado === self::ESTADO_DESATIVADA;
    }

    /**
     * Define o estado da sala como desativada
     */
    public function setEstadoToDesativada()
    {
        $this->estado = self::ESTADO_DESATIVADA;
    }

    /**
     * Verifica se a sala está disponível para reserva
     * @return bool - True se o estado permitir reserva
     */
    public function isDisponivelParaReserva()
    {
        return in_array($this->estado, [
            self::ESTADO_LIVRE,
            self::ESTADO_EM_USO  // Nota: Sala ainda pode ser reservada mesmo se já estiver em uso
        ]);
    }

    /**
     * Obtém a contagem de salas por estado para estatísticas
     * @return array - Array com a contagem de salas por estado
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy('estado')
            ->indexBy('estado') // Usa o estado como índice do array
            ->column(); // Retorna apenas a coluna de contagens
    }

    /**
     * Obtém salas em manutenção sem registo de manutenção ativa
     * @return array - Array de salas em manutenção sem registo ativo
     */
    public static function getSalasManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['sala_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']]) // Estados de manutenção ativos
                    ->andWhere(['IS NOT', 'sala_id', null]) // Apenas registos com sala associada
            ])
            ->all(); // Retorna todos os resultados
    }

    /**
     * Obtém a contagem de salas em manutenção sem registo ativo
     * @return int - Número de salas em manutenção sem registo
     */
    public static function getCountSalasManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['sala_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'sala_id', null])
            ])
            ->count(); // Retorna apenas a contagem
    }

    /**
     * Obtém os equipamentos associados a esta sala (via tabela de ligação)
     * @return \yii\db\ActiveQuery - Query para obter os equipamentos
     */
    public function getEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])
            ->viaTable('sala_equipamento', ['idSala' => 'id']);
    }

    /**
     * Obtém os registos de associação sala-equipamento
     * @return \yii\db\ActiveQuery - Query para obter as associações
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idSala' => 'id']);
    }

    /**
     * Método de debug para verificar o estado da sala
     * @return array - Array com informações de debug
     */
    public function debugEstado()
    {
        return [
            'estado' => $this->estado,
            'constante_EM_USO' => self::ESTADO_EM_USO,
            'getEstadoLabel' => $this->getEstadoLabel(),
            'optsEstado' => self::optsEstado(),
            'estado_in_opts' => isset(self::optsEstado()[$this->estado]),
        ];
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

        // Preparar dados da sala para envio MQTT
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'estado' => $this->estado,
            'bloco_id' => $this->bloco_id,
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        // Adicionar informações do bloco (se disponível)
        if ($this->bloco) {
            $data['bloco_nome'] = $this->bloco->nome;
            $data['bloco_estado'] = $this->bloco->estado;
        }

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE); // Converter para JSON

        // Publicar no Mosquitto MQTT baseado no tipo de operação
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_SALA", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_SALA", $myJSON);

            // Notificação específica para mudança de estado
            if (isset($changedAttributes['estado'])) {
                $oldEstado = $changedAttributes['estado'];
                $newEstado = $this->estado;

                $estadoData = [
                    'id' => $this->id,
                    'nome' => $this->nome,
                    'old_estado' => $oldEstado,
                    'new_estado' => $newEstado,
                    'bloco_id' => $this->bloco_id,
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                if ($this->bloco) {
                    $estadoData['bloco_nome'] = $this->bloco->nome;
                }

                $estadoJSON = json_encode($estadoData, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("ESTADO_CHANGED_SALA", $estadoJSON);
            }
        }
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Preparar dados mínimos para notificar a eliminação
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'bloco_id' => $this->bloco_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($this->bloco) {
            $data['bloco_nome'] = $this->bloco->nome;
        }

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Publicar no tópico de eliminação
        $this->FazPublishNoMosquitto("DELETE_SALA", $myJSON);
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
            $client_id = "yii_sala_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT Sala: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_sala.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT Sala: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT Sala Exception: " . $e->getMessage());
            return false;
        }
    }
}