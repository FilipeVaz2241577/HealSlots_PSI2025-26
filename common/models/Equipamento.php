<?php

namespace common\models;
use backend\mosquitto\phpMQTT;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Esta é a classe de modelo para a tabela "equipamento".
 *
 * @property int $id - Identificador único do equipamento
 * @property string $numeroSerie - Número de série do equipamento
 * @property int $tipoEquipamento_id - ID do tipo de equipamento (chave estrangeira)
 * @property string $equipamento - Nome do equipamento
 * @property string $estado - Estado atual do equipamento
 *
 * @property TipoEquipamento $tipoEquipamento - Relação com o tipo de equipamento
 * @property SalaEquipamento[] $salaEquipamentos - Relação com os registos de associação a salas
 * @property Sala[] $salas - Salas onde este equipamento está alocado (via relação)
 */
class Equipamento extends ActiveRecord
{
    // Constantes para os estados possíveis do equipamento
    const ESTADO_OPERACIONAL = 'Operacional';
    const ESTADO_MANUTENCAO = 'Em Manutenção';
    const ESTADO_EM_USO = 'Em Uso';

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%equipamento}}'; // Nome da tabela na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define os comportamentos do modelo
     */
    public function behaviors()
    {
        return [
            // Pode adicionar comportamentos como TimestampBehavior ou BlameableBehavior aqui
        ];
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Todos estes campos são obrigatórios
            [['numeroSerie', 'tipoEquipamento_id', 'equipamento', 'estado'], 'required'],
            // tipoEquipamento_id deve ser um inteiro
            [['tipoEquipamento_id'], 'integer'],
            // numeroSerie e equipamento são strings com máximo de 100 caracteres
            [['numeroSerie', 'equipamento'], 'string', 'max' => 100],
            // estado é uma string com máximo de 20 caracteres
            [['estado'], 'string', 'max' => 20],
            // Valor padrão para estado é 'Operacional'
            [['estado'], 'default', 'value' => self::ESTADO_OPERACIONAL],
            // numeroSerie deve ser único na base de dados
            [['numeroSerie'], 'unique'],
            // Validação de existência do tipo de equipamento (chave estrangeira)
            [['tipoEquipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => TipoEquipamento::class, 'targetAttribute' => ['tipoEquipamento_id' => 'id']],
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
            'numeroSerie' => 'Número de Série', // Rótulo para o número de série
            'tipoEquipamento_id' => 'Tipo de Equipamento', // Rótulo para o tipo de equipamento
            'equipamento' => 'Nome do Equipamento', // Rótulo para o nome do equipamento
            'estado' => 'Estado', // Rótulo para o estado
        ];
    }

    /**
     * Obtém a relação com o tipo de equipamento
     * @return \yii\db\ActiveQuery - Query para obter o tipo de equipamento
     */
    public function getTipoEquipamento()
    {
        return $this->hasOne(TipoEquipamento::class, ['id' => 'tipoEquipamento_id']);
    }

    /**
     * Obtém a relação com os registos de associação a salas
     * @return \yii\db\ActiveQuery - Query para obter as associações com salas
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idEquipamento' => 'id']);
    }

    /**
     * Obtém a relação com as salas através da tabela de associação sala_equipamento
     * @return \yii\db\ActiveQuery - Query para obter as salas associadas
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['id' => 'idSala'])
            ->via('salaEquipamentos'); // Usa a relação salaEquipamentos como ponte
    }

    /**
     * Obtém as opções para o campo estado
     * @return string[] - Array com os pares chave-valor dos estados
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_OPERACIONAL => 'Operacional',
            self::ESTADO_MANUTENCAO => 'Em Manutenção',
            self::ESTADO_EM_USO => 'Em Uso',
        ];
    }

    /**
     * Obtém um badge colorido para o estado do equipamento
     * @return string - HTML do badge com a cor apropriada
     */
    public function getEstadoBadge()
    {
        // Mapeamento de cores para cada estado
        $colors = [
            self::ESTADO_OPERACIONAL => 'success', // Verde para operacional
            self::ESTADO_MANUTENCAO => 'warning', // Amarelo para manutenção
            self::ESTADO_EM_USO => 'primary', // Azul para em uso
        ];

        // Retorna o badge HTML com a classe de cor apropriada
        return '<span class="badge bg-' . ($colors[$this->estado] ?? 'secondary') . '">' . $this->estado . '</span>';
    }

    /**
     * Obtém a contagem de equipamentos por estado para estatísticas
     * @return array - Array com a contagem de equipamentos por estado
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy(['estado'])
            ->indexBy('estado') // Usa o estado como índice do array
            ->column(); // Retorna apenas a coluna de contagens
    }

    /**
     * Obtém equipamentos em manutenção sem registo de manutenção ativa
     * @return array - Array de equipamentos em manutenção sem registo ativo
     */
    public static function getEquipamentosManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['equipamento_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']]) // Estados de manutenção ativos
                    ->andWhere(['IS NOT', 'equipamento_id', null]) // Apenas registos com equipamento associado
            ])
            ->all(); // Retorna todos os resultados
    }

    /**
     * Obtém a contagem de equipamentos em manutenção sem registo ativo
     * @return int - Número de equipamentos em manutenção sem registo
     */
    public static function getCountEquipamentosManutencaoSemRegisto()
    {
        return self::find()
            ->where(['estado' => self::ESTADO_MANUTENCAO])
            ->andWhere(['NOT IN', 'id',
                (new \yii\db\Query())
                    ->select(['equipamento_id'])
                    ->from('manutencao')
                    ->where(['status' => ['Pendente', 'Em Curso']])
                    ->andWhere(['IS NOT', 'equipamento_id', null])
            ])
            ->count(); // Retorna apenas a contagem
    }

    /**
     * Obtém a sala atual onde este equipamento se encontra
     * @return Sala|null - A sala atual ou null se não estiver associado a nenhuma sala
     */
    public function getCurrentSala()
    {
        $salaEquipamento = SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->one(); // Obtém o primeiro registo (se existir)

        if ($salaEquipamento) {
            return $salaEquipamento->sala; // Retorna a sala associada
        }

        return null; // Retorna null se não houver associação
    }

    /**
     * Verifica se o equipamento está em manutenção
     * @return bool - True se o estado for 'Em Manutenção'
     */
    public function isInMaintenance()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    /**
     * Verifica se o equipamento está operacional
     * @return bool - True se o estado for 'Operacional'
     */
    public function isOperational()
    {
        return $this->estado === self::ESTADO_OPERACIONAL;
    }

    /**
     * Verifica se o equipamento está em uso
     * @return bool - True se o estado for 'Em Uso'
     */
    public function isInUse()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    /**
     * Verifica se o equipamento está associado a alguma sala
     * @return bool - True se existir alguma associação com sala
     */
    public function hasSala()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->exists(); // Retorna true se existir pelo menos um registo
    }

    /**
     * Obtém o número de salas associadas a este equipamento
     * @return int - Contagem de salas associadas
     */
    public function getSalasCount()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->count(); // Retorna o número de associações
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
        $id = $this->id; // ID do equipamento
        $numeroSerie = $this->numeroSerie; // Número de série
        $equipamento = $this->equipamento; // Nome do equipamento
        $estado = $this->estado; // Estado atual
        $tipoEquipamento_id = $this->tipoEquipamento_id; // ID do tipo de equipamento

        // Criar objeto JSON com os dados do equipamento
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->numeroSerie = $numeroSerie;
        $myObj->equipamento = $equipamento;
        $myObj->estado = $estado;
        $myObj->tipoEquipamento_id = $tipoEquipamento_id;

        $myJSON = json_encode($myObj); // Converter objeto para JSON

        // Log para debug no sistema
        Yii::info("afterSave: " . ($insert ? 'INSERT' : 'UPDATE') . " - ID: $id", 'equipamento');
        error_log("📝 Equipamento " . ($insert ? 'criado' : 'atualizado') . " - ID: $id");

        // Determinar o canal MQTT baseado no tipo de operação
        $canal = $insert ? "INSERT_EQUIPAMENTO" : "UPDATE_EQUIPAMENTO";

        // 1. Primeiro salva a notificação no arquivo JSON (para histórico)
        $this->saveNotificationToFile($canal, $myJSON);

        // 2. Depois publica no MQTT (para comunicação em tempo real)
        $this->FazPublishNoMosquitto($canal, $myJSON);
    }

    /**
     * {@inheritdoc}
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // Obter o ID do equipamento eliminado
        $prod_id = $this->id;

        // Criar objeto JSON apenas com o ID
        $myObj = new \stdClass();
        $myObj->id = $prod_id;
        $myJSON = json_encode($myObj);

        // Log para debug
        Yii::info("afterDelete: ID: $prod_id", 'equipamento');
        error_log("🗑️ Equipamento excluído - ID: $prod_id");

        // 1. Primeiro salva a notificação no arquivo JSON
        $this->saveNotificationToFile("DELETE_EQUIPAMENTO", $myJSON);

        // 2. Depois publica no MQTT
        $this->FazPublishNoMosquitto("DELETE_EQUIPAMENTO", $myJSON);
    }

    /**
     * Salva uma notificação no arquivo JSON para histórico
     * @param string $channel - Canal/tópico da notificação
     * @param string $message - Mensagem em formato JSON
     * @return bool - True se a operação for bem-sucedida
     */
    private function saveNotificationToFile($channel, $message)
    {
        try {
            // Caminho para o arquivo de notificações
            $logFile = Yii::getAlias('@backend/runtime/mqtt_notifications.json');

            // Criar pasta runtime se não existir
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // Ler notificações existentes
            $notifications = [];
            if (file_exists($logFile)) {
                $content = file_get_contents($logFile);
                $notifications = json_decode($content, true) ?: [];
            }

            // Criar ID único para a notificação
            $notificationId = 'mqtt_' . time() . '_' . uniqid();

            // Determinar tipo de ação baseado no canal
            $action = 'info';
            if (strpos($channel, 'INSERT') !== false) $action = 'insert';
            if (strpos($channel, 'UPDATE') !== false) $action = 'update';
            if (strpos($channel, 'DELETE') !== false) $action = 'delete';

            // Extrair dados da mensagem para criar um título descritivo
            $title = 'Evento do Sistema';
            $data = json_decode($message, true);
            $equipamentoId = '';
            $equipamentoNome = '';

            if ($data) {
                $equipamentoId = $data['id'] ?? '';
                $equipamentoNome = $data['equipamento'] ?? '';

                if ($equipamentoNome) {
                    $title = $equipamentoNome;
                    if ($equipamentoId) {
                        $title = '#' . $equipamentoId . ' - ' . $equipamentoNome;
                    }
                } else if ($equipamentoId) {
                    $title = 'Equipamento #' . $equipamentoId;
                }
            }

            // Obter o utilizador atual (se estiver autenticado)
            $user = 'Sistema';
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                $user = Yii::$app->user->identity->username;
            }

            // Criar nova notificação
            $notifications[$notificationId] = [
                'id' => $notificationId,
                'topic' => $channel,
                'message' => $message,
                'title' => $title,
                'action' => $action,
                'time' => date('H:i:s'), // Hora atual
                'date' => date('d/m/Y'), // Data atual
                'user' => $user,
                'read' => false, // Notificação não lida por padrão
                'timestamp' => time() // Timestamp UNIX
            ];

            // Limitar a 50 notificações (mantém apenas as mais recentes)
            if (count($notifications) > 50) {
                // Ordenar por timestamp (mais antigas primeiro)
                uasort($notifications, function($a, $b) {
                    return $a['timestamp'] <=> $b['timestamp'];
                });
                // Manter apenas as 50 mais recentes
                $notifications = array_slice($notifications, -50, 50, true);
            }

            // Salvar no arquivo JSON
            $result = file_put_contents($logFile, json_encode($notifications, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($result === false) {
                error_log("❌ Erro ao salvar notificação no arquivo: $logFile");
                Yii::error("Erro ao salvar notificação MQTT no arquivo", 'mqtt');
            } else {
                error_log("✅ Notificação salva: $channel | ID: $equipamentoId");
                Yii::info("Notificação MQTT salva: {$channel} - ID: {$equipamentoId}", 'mqtt');
            }

            return $result !== false;

        } catch (\Exception $e) {
            error_log("❌ Exception ao salvar notificação: " . $e->getMessage());
            Yii::error("Exception ao salvar notificação MQTT: " . $e->getMessage(), 'mqtt');
            return false;
        }
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
            $client_id = "yii_equipamento_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                $data = json_decode($msg, true);
                $id = $data['id'] ?? 'N/A';
                error_log("✅ MQTT: Publicado em $canal - ID: $id");

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_debug.log',
                    date('Y-m-d H:i:s') . " | $canal | ID: $id | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true;
            }

            // Se falhar a conexão
            error_log("❌ MQTT: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT Exception: " . $e->getMessage());
            return false;
        }
    }
}