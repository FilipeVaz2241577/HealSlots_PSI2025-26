<?php

namespace common\models;
use backend\mosquitto\phpMQTT;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "equipamento".
 *
 * @property int $id
 * @property string $numeroSerie
 * @property int $tipoEquipamento_id
 * @property string $equipamento
 * @property string $estado
 *
 * @property TipoEquipamento $tipoEquipamento
 * @property SalaEquipamento[] $salaEquipamentos
 * @property Sala[] $salas
 */
class Equipamento extends ActiveRecord
{
    const ESTADO_OPERACIONAL = 'Operacional';
    const ESTADO_MANUTENCAO = 'Em Manutenção';
    const ESTADO_EM_USO = 'Em Uso';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%equipamento}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroSerie', 'tipoEquipamento_id', 'equipamento', 'estado'], 'required'],
            [['tipoEquipamento_id'], 'integer'],
            [['numeroSerie', 'equipamento'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'default', 'value' => self::ESTADO_OPERACIONAL],
            [['numeroSerie'], 'unique'],
            [['tipoEquipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => TipoEquipamento::class, 'targetAttribute' => ['tipoEquipamento_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'numeroSerie' => 'Número de Série',
            'tipoEquipamento_id' => 'Tipo de Equipamento',
            'equipamento' => 'Nome do Equipamento',
            'estado' => 'Estado',
        ];
    }

    /**
     * Gets query for [[TipoEquipamento]].
     */
    public function getTipoEquipamento()
    {
        return $this->hasOne(TipoEquipamento::class, ['id' => 'tipoEquipamento_id']);
    }

    /**
     * Gets query for [[SalaEquipamentos]].
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idEquipamento' => 'id']);
    }

    /**
     * Gets query for [[Salas]] through sala_equipamento.
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['id' => 'idSala'])
            ->via('salaEquipamentos');
    }


    /**
     * Get estado options
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
     * Get badge color for estado
     */
    public function getEstadoBadge()
    {
        $colors = [
            self::ESTADO_OPERACIONAL => 'success',
            self::ESTADO_MANUTENCAO => 'warning',
            self::ESTADO_EM_USO => 'primary',
        ];

        return '<span class="badge bg-' . ($colors[$this->estado] ?? 'secondary') . '">' . $this->estado . '</span>';
    }

    /**
     * Get count by estado for statistics
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy(['estado'])
            ->indexBy('estado')
            ->column();
    }

    /**
     * Get equipamentos em manutenção sem registo de manutenção ativa
     */
    public static function getEquipamentosManutencaoSemRegisto()
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
            ->all();
    }

    /**
     * Get count de equipamentos em manutenção sem registo
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
            ->count();
    }

    /**
     * Get current sala for this equipamento
     */
    public function getCurrentSala()
    {
        $salaEquipamento = SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->one();

        if ($salaEquipamento) {
            return $salaEquipamento->sala;
        }

        return null;
    }

    /**
     * Check if equipamento is in maintenance
     */
    public function isInMaintenance()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    /**
     * Check if equipamento is operational
     */
    public function isOperational()
    {
        return $this->estado === self::ESTADO_OPERACIONAL;
    }

    /**
     * Check if equipamento is in use
     */
    public function isInUse()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    /**
     * Check if equipamento has a sala assigned
     */
    public function hasSala()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->exists();
    }

    /**
     * Get salas count for this equipamento
     */
    public function getSalasCount()
    {
        return SalaEquipamento::find()
            ->where(['idEquipamento' => $this->id])
            ->count();
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

        // Log para debug
        Yii::info("afterSave: " . ($insert ? 'INSERT' : 'UPDATE') . " - ID: $id", 'equipamento');
        error_log("📝 Equipamento " . ($insert ? 'criado' : 'atualizado') . " - ID: $id");

        // Determinar canal
        $canal = $insert ? "INSERT_EQUIPAMENTO" : "UPDATE_EQUIPAMENTO";

        // 1. Primeiro salva a notificação no arquivo JSON
        $this->saveNotificationToFile($canal, $myJSON);

        // 2. Depois publica no MQTT
        $this->FazPublishNoMosquitto($canal, $myJSON);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $prod_id = $this->id;
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

// ADICIONE ESTE MÉTODO NO MESMO ARQUIVO (Equipamento.php)
    private function saveNotificationToFile($channel, $message)
    {
        try {
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

            // Criar ID único
            $notificationId = 'mqtt_' . time() . '_' . uniqid();

            // Determinar tipo de ação
            $action = 'info';
            if (strpos($channel, 'INSERT') !== false) $action = 'insert';
            if (strpos($channel, 'UPDATE') !== false) $action = 'update';
            if (strpos($channel, 'DELETE') !== false) $action = 'delete';

            // Extrair dados da mensagem
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

            // Usuário atual
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
                'time' => date('H:i:s'),
                'date' => date('d/m/Y'),
                'user' => $user,
                'read' => false,
                'timestamp' => time()
            ];

            // Limitar a 50 notificações (mantém as mais recentes)
            if (count($notifications) > 50) {
                // Ordenar por timestamp (mais antigas primeiro)
                uasort($notifications, function($a, $b) {
                    return $a['timestamp'] <=> $b['timestamp'];
                });
                // Manter apenas as 50 mais recentes
                $notifications = array_slice($notifications, -50, 50, true);
            }

            // Salvar no arquivo
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

// Atualize também o método FazPublishNoMosquitto para chamar saveNotificationToFile:
    public function FazPublishNoMosquitto($canal, $msg)
    {
        try {
            // Caminho ABSOLUTO para o phpMQTT
            $phpMQTTPath = Yii::getAlias('@backend') . '/mosquitto/phpMQTT.php';

            if (!file_exists($phpMQTTPath)) {
                error_log("MQTT ERRO: Arquivo não encontrado: $phpMQTTPath");
                return false;
            }

            require_once $phpMQTTPath;

            $server = "127.0.0.1";
            $port = 1883;
            $client_id = "yii_equipamento_" . uniqid();

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Conectar (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close();

                // Log de sucesso
                $data = json_decode($msg, true);
                $id = $data['id'] ?? 'N/A';
                error_log("✅ MQTT: Publicado em $canal - ID: $id");

                // Log em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_debug.log',
                    date('Y-m-d H:i:s') . " | $canal | ID: $id | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                // A NOTIFICAÇÃO JÁ FOI SALVA PELOS MÉTODOS afterSave/afterDelete
                // Então não precisamos chamar saveNotificationToFile aqui novamente

                return true;
            }

            error_log("❌ MQTT: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            error_log("❌ MQTT Exception: " . $e->getMessage());
            return false;
        }
    }
}