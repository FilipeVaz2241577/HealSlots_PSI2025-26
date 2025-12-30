<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use backend\mosquitto\phpMQTT; // ADICIONE ESTA LINHA

/**
 * This is the model class for table "sala".
 *
 * @property int $id
 * @property string $nome
 * @property string $estado
 * @property int $bloco_id
 *
 * @property Bloco $bloco
 */
class Sala extends ActiveRecord
{
    const ESTADO_LIVRE = 'Livre';
    const ESTADO_EM_USO = 'EmUso';
    const ESTADO_MANUTENCAO = 'Manutencao';
    const ESTADO_DESATIVADA = 'Desativada';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sala}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome', 'bloco_id'], 'required'],
            [['bloco_id'], 'integer'],
            [['nome'], 'string', 'max' => 100],
            [['estado'], 'string', 'max' => 20],
            [['estado'], 'default', 'value' => self::ESTADO_LIVRE],
            ['estado', 'in', 'range' => array_keys(self::optsEstado())],
            [['bloco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bloco::class, 'targetAttribute' => ['bloco_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nome' => 'Nome',
            'estado' => 'Estado',
            'bloco_id' => 'Bloco',
            'blocoName' => 'Bloco',
        ];
    }

    /**
     * Gets query for [[Bloco]].
     */
    public function getBloco()
    {
        return $this->hasOne(Bloco::class, ['id' => 'bloco_id']);
    }

    /**
     * Get bloco name
     */
    public function getBlocoName()
    {
        return $this->bloco ? $this->bloco->nome : 'N/A';
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_LIVRE => 'Livre',
            self::ESTADO_EM_USO => 'Em Uso',          // ← "EmUso" mapeado para "Em Uso"
            self::ESTADO_MANUTENCAO => 'Em Manutenção',
            self::ESTADO_DESATIVADA => 'Desativada',
        ];
    }

    /**
     * @return string
     */
    public function getEstadoLabel()
    {
        $opts = self::optsEstado();

        // Verificar exatamente o valor armazenado
        if (isset($opts[$this->estado])) {
            return $opts[$this->estado];
        }

        // Se não encontrar, verificar case-insensitive
        $estadoLower = strtolower($this->estado);
        foreach ($opts as $key => $label) {
            if (strtolower($key) === $estadoLower) {
                return $label;
            }
        }

        return 'Desconhecido (' . $this->estado . ')';
    }

    /**
     * @return bool
     */
    public function isEstadoLivre()
    {
        return $this->estado === self::ESTADO_LIVRE;
    }

    public function setEstadoToLivre()
    {
        $this->estado = self::ESTADO_LIVRE;
    }

    /**
     * @return bool
     */
    public function isEstadoEmUso()
    {
        return $this->estado === self::ESTADO_EM_USO;
    }

    public function setEstadoToEmUso()
    {
        $this->estado = self::ESTADO_EM_USO;
    }

    /**
     * @return bool
     */
    public function isEstadoManutencao()
    {
        return $this->estado === self::ESTADO_MANUTENCAO;
    }

    public function setEstadoToManutencao()
    {
        $this->estado = self::ESTADO_MANUTENCAO;
    }

    /**
     * @return bool
     */
    public function isEstadoDesativada()
    {
        return $this->estado === self::ESTADO_DESATIVADA;
    }

    public function setEstadoToDesativada()
    {
        $this->estado = self::ESTADO_DESATIVADA;
    }

    /**
     * Verifica se a sala está disponível para reserva
     * @return bool
     */
    public function isDisponivelParaReserva()
    {
        return in_array($this->estado, [
            self::ESTADO_LIVRE,
            self::ESTADO_EM_USO  // Sala ainda pode ser reservada mesmo se já estiver em uso
        ]);
    }

    /**
     * Get all salas count by estado
     */
    public static function getCountByEstado()
    {
        return self::find()
            ->select(['estado', 'COUNT(*) as count'])
            ->groupBy('estado')
            ->indexBy('estado')
            ->column();
    }

    /**
     * Get salas em manutenção sem registo de manutenção ativa
     */
    public static function getSalasManutencaoSemRegisto()
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
            ->all();
    }

    /**
     * Get count de salas em manutenção sem registo
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
            ->count();
    }

    /**
     * Get equipamentos in this sala
     */
    public function getEquipamentos()
    {
        return $this->hasMany(Equipamento::class, ['id' => 'idEquipamento'])
            ->viaTable('sala_equipamento', ['idSala' => 'id']);
    }

    /**
     * Get sala_equipamento relationships
     */
    public function getSalaEquipamentos()
    {
        return $this->hasMany(SalaEquipamento::class, ['idSala' => 'id']);
    }

    /**
     * Debug method to check state
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
    // ADICIONE ESTES 3 MÉTODOS PARA MQTT
    // ==============================================

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Obter dados da sala
        $data = [
            'id' => $this->id,
            'nome' => $this->nome,
            'estado' => $this->estado,
            'bloco_id' => $this->bloco_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Adicionar bloco se disponível
        if ($this->bloco) {
            $data['bloco_nome'] = $this->bloco->nome;
            $data['bloco_estado'] = $this->bloco->estado;
        }

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Publicar no Mosquitto
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
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

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
        $this->FazPublishNoMosquitto("DELETE_SALA", $myJSON);
    }

    /**
     * Publica mensagem no Mosquitto MQTT
     */
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
            $client_id = "yii_sala_" . uniqid();

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close();

                // Log de sucesso
                error_log("✅ MQTT Sala: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_sala.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true;
            }

            error_log("❌ MQTT Sala: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            error_log("❌ MQTT Sala Exception: " . $e->getMessage());
            return false;
        }
    }
}