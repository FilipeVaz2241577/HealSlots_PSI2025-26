<?php

namespace common\models;

use Yii;
use backend\mosquitto\phpMQTT; // ADICIONE ESTA LINHA

/**
 * This is the model class for table "bloco".
 *
 * @property int $id
 * @property string $nome
 * @property string|null $estado
 *
 * @property Sala[] $salas
 */
class Bloco extends \yii\db\ActiveRecord
{
    /**
     * ENUM field values
     */
    const ESTADO_ATIVO = 'ativo';
    const ESTADO_DESATIVADO = 'desativado';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bloco';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['estado'], 'default', 'value' => 'ativo'],
            [['nome'], 'required', 'message' => 'O nome do bloco é obrigatório.'],
            [['estado'], 'string'],
            [['nome'], 'string', 'max' => 100, 'tooLong' => 'O nome não pode exceder 100 caracteres.'],
            ['estado', 'in', 'range' => array_keys(self::optsEstado()), 'message' => 'Estado inválido.'],
            // Validação de unicidade
            [['nome'], 'unique', 'message' => 'Já existe um bloco com este nome. Por favor, escolha outro nome.'],
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
        ];
    }

    /**
     * Gets query for [[Salas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSalas()
    {
        return $this->hasMany(Sala::class, ['bloco_id' => 'id']);
    }

    /**
     * column estado ENUM value labels
     * @return string[]
     */
    public static function optsEstado()
    {
        return [
            self::ESTADO_ATIVO => 'Ativo',
            self::ESTADO_DESATIVADO => 'Desativado',
        ];
    }

    /**
     * @return string
     */
    public function getEstadoLabel()
    {
        return self::optsEstado()[$this->estado] ?? 'Desconhecido';
    }

    /**
     * Sobrescrevendo a validação beforeSave para garantir unicidade
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Verificar unicidade antes de salvar
        if ($this->isNewRecord) {
            $exists = self::find()->where(['nome' => $this->nome])->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        } else {
            $exists = self::find()
                ->where(['nome' => $this->nome])
                ->andWhere(['!=', 'id', $this->id])
                ->exists();
            if ($exists) {
                $this->addError('nome', 'Já existe um bloco com este nome.');
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEstadoAtivo()
    {
        return $this->estado === self::ESTADO_ATIVO;
    }

    public function setEstadoToAtivo()
    {
        $this->estado = self::ESTADO_ATIVO;
    }

    /**
     * @return bool
     */
    public function isEstadoDesativado()
    {
        return $this->estado === self::ESTADO_DESATIVADO;
    }

    public function setEstadoToDesativado()
    {
        $this->estado = self::ESTADO_DESATIVADO;
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

        // Obter dados do registo
        $id = $this->id;
        $nome = $this->nome;
        $estado = $this->estado;

        // Criar objeto JSON
        $myObj = new \stdClass();
        $myObj->id = $id;
        $myObj->nome = $nome;
        $myObj->estado = $estado;

        $myJSON = json_encode($myObj);

        // Publicar no Mosquitto
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_BLOCO", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_BLOCO", $myJSON);
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();

        $bloco_id = $this->id;
        $myObj = new \stdClass();
        $myObj->id = $bloco_id;
        $myJSON = json_encode($myObj);

        $this->FazPublishNoMosquitto("DELETE_BLOCO", $myJSON);
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
            $client_id = "yii_bloco_" . uniqid();

            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            if ($mqtt->connect(true, null, null, null, 5)) {
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close();

                // Log de sucesso
                error_log("✅ MQTT Bloco: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_bloco.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true;
            }

            error_log("❌ MQTT Bloco: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            error_log("❌ MQTT Bloco Exception: " . $e->getMessage());
            return false;
        }
    }
}