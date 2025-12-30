<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use backend\mosquitto\phpMQTT; // ADICIONE ESTA LINHA

/**
 * This is the model class for table "manutencao".
 *
 * @property int $id
 * @property int $equipamento_id
 * @property int $user_id
 * @property int $sala_id
 * @property string $dataInicio
 * @property string $dataFim
 * @property string $descricao
 * @property string $status
 *
 * @property User $user
 * @property Equipamento $equipamento
 * @property Sala $sala
 */
class Manutencao extends ActiveRecord
{
    const STATUS_PENDENTE = 'Pendente';
    const STATUS_EM_CURSO = 'Em Curso';
    const STATUS_CONCLUIDA = 'Concluída';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%manutencao}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dataInicio'], 'required'],
            [['equipamento_id', 'user_id', 'sala_id'], 'integer'],
            [['dataInicio', 'dataFim'], 'safe'],
            [['descricao'], 'string'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'default', 'value' => self::STATUS_PENDENTE],
            // Validar que pelo menos um (equipamento OU sala) está preenchido
            [['equipamento_id', 'sala_id'], 'validateEquipamentoOrSala', 'skipOnEmpty' => false],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['equipamento_id'], 'exist', 'skipOnError' => true, 'targetClass' => Equipamento::class, 'targetAttribute' => ['equipamento_id' => 'id']],
            [['sala_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sala::class, 'targetAttribute' => ['sala_id' => 'id']],
        ];
    }

    /**
     * Validação personalizada: Pelo menos equipamento OU sala deve ser preenchido
     */
    public function validateEquipamentoOrSala($attribute, $params, $validator)
    {
        if (empty($this->equipamento_id) && empty($this->sala_id)) {
            $this->addError('equipamento_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
            $this->addError('sala_id', 'Deve selecionar pelo menos um equipamento OU uma sala.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'equipamento_id' => 'Equipamento',
            'user_id' => 'Técnico',
            'sala_id' => 'Sala',
            'dataInicio' => 'Data Início',
            'dataFim' => 'Data Fim',
            'descricao' => 'Descrição',
            'status' => 'Estado',
        ];
    }

    /**
     * Gets query for [[User]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Equipamento]].
     */
    public function getEquipamento()
    {
        return $this->hasOne(Equipamento::class, ['id' => 'equipamento_id']);
    }

    /**
     * Gets query for [[Sala]].
     */
    public function getSala()
    {
        return $this->hasOne(Sala::class, ['id' => 'sala_id']);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_EM_CURSO => 'Em Curso',
            self::STATUS_CONCLUIDA => 'Concluída',
        ];
    }

    /**
     * Get badge color for status
     */
    public function getStatusBadge()
    {
        $colors = [
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_EM_CURSO => 'primary',
            self::STATUS_CONCLUIDA => 'success',
        ];

        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '">' . $this->status . '</span>';
    }

    /**
     * Check if maintenance is in progress
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_EM_CURSO;
    }

    /**
     * Check if maintenance is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_CONCLUIDA;
    }

    /**
     * Get duration in hours
     */
    public function getDuracao()
    {
        if ($this->dataInicio && $this->dataFim) {
            $inicio = new \DateTime($this->dataInicio);
            $fim = new \DateTime($this->dataFim);
            $diff = $inicio->diff($fim);
            return $diff->h + ($diff->days * 24);
        }
        return null;
    }

    /**
     * Get the title for display
     */
    public function getTitle()
    {
        if ($this->equipamento) {
            return 'Manutenção do Equipamento: ' . $this->equipamento->equipamento;
        } elseif ($this->sala) {
            return 'Manutenção da Sala: ' . $this->sala->nome;
        }
        return 'Manutenção #' . $this->id;
    }

    /**
     * Get current location (sala) for equipment maintenance
     */
    public function getLocalizacao()
    {
        if ($this->sala) {
            return $this->sala->nome . ($this->sala->bloco ? ' (' . $this->sala->bloco->nome . ')' : '');
        } elseif ($this->equipamento) {
            $sala = $this->equipamento->getCurrentSala();
            if ($sala) {
                return $sala->nome . ($sala->bloco ? ' (' . $sala->bloco->nome . ')' : '');
            }
        }
        return 'Não localizado';
    }

    /**
     * Before save logic
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Se estiver concluindo a manutenção
            if (!$insert && $this->status === self::STATUS_CONCLUIDA && !$this->dataFim) {
                $this->dataFim = date('Y-m-d H:i:s');
            }

            return true;
        }
        return false;
    }

    /**
     * Get equipamentos que NÃO estão em manutenção ativa
     */
    public static function getEquipamentosDisponiveis()
    {
        // Buscar IDs de equipamentos que estão em manutenção ativa (Pendente ou Em Curso)
        $equipamentosEmManutencao = self::find()
            ->select('equipamento_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['equipamento_id' => null]])
            ->column();

        // Buscar todos os equipamentos que NÃO estão na lista acima
        return Equipamento::find()
            ->where(['not in', 'id', $equipamentosEmManutencao])
            ->all();
    }

    /**
     * Get salas que NÃO estão em manutenção ativa
     */
    public static function getSalasDisponiveis()
    {
        // Buscar IDs de salas que estão em manutenção ativa (Pendente ou Em Curso)
        $salasEmManutencao = self::find()
            ->select('sala_id')
            ->where(['status' => [self::STATUS_PENDENTE, self::STATUS_EM_CURSO]])
            ->andWhere(['not', ['sala_id' => null]])
            ->column();

        // Buscar todas as salas que NÃO estão na lista acima
        return Sala::find()
            ->where(['not in', 'id', $salasEmManutencao])
            ->all();
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

        // Obter dados da manutenção
        $data = [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'equipamento_id' => $this->equipamento_id,
            'user_id' => $this->user_id,
            'sala_id' => $this->sala_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Adicionar relacionamentos se disponíveis
        if ($this->equipamento) {
            $data['equipamento_nome'] = $this->equipamento->equipamento;
            $data['equipamento_numeroSerie'] = $this->equipamento->numeroSerie;
        }

        if ($this->user) {
            $data['user_nome'] = $this->user->username;
        }

        if ($this->sala) {
            $data['sala_nome'] = $this->sala->nome;
            if ($this->sala->bloco) {
                $data['bloco_nome'] = $this->sala->bloco->nome;
            }
        }

        $myJSON = json_encode($data);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_MANUTENCAO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_MANUTENCAO", $myJSON);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $data = ['id' => $this->id];
        $myJSON = json_encode($data);

        $this->FazPublishNoMosquitto("DELETE_MANUTENCAO", $myJSON);
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
            $client_id = "yii_manutencao_" . uniqid();

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close();

                // Log de sucesso
                error_log("✅ MQTT Manutenção: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_manutencao.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true;
            }

            error_log("❌ MQTT Manutenção: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            error_log("❌ MQTT Manutenção Exception: " . $e->getMessage());
            return false;
        }
    }
}