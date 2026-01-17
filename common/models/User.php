<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use backend\mosquitto\phpMQTT;

/**
 * Modelo de utilizador
 * Esta classe representa os utilizadores do sistema e implementa autenticação
 *
 * @property integer $id - Identificador único do utilizador
 * @property string $username - Nome de utilizador para login
 * @property string $password_hash - Hash da palavra-passe (armazenada de forma segura)
 * @property string $password_reset_token - Token para redefinição de palavra-passe
 * @property string $verification_token - Token para verificação de email
 * @property string $email - Endereço de email do utilizador
 * @property string $auth_key - Chave de autenticação para "lembrar-me"
 * @property integer $status - Estado do utilizador (ativo/inativo/eliminado)
 * @property integer $created_at - Timestamp de criação do utilizador
 * @property integer $updated_at - Timestamp da última atualização
 * @property string $password write-only password - Palavra-passe (apenas para escrita)
 * @property string $password_repeat write-only password repeat - Confirmação de palavra-passe
 * @property string $role virtual property for form - Role do utilizador (propriedade virtual para formulários)
 */
class User extends ActiveRecord implements IdentityInterface
{
    // Constantes para os estados possíveis do utilizador
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    // Constantes para cenários de validação
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    // Propriedades virtuais para formulários
    public $password;
    public $password_repeat;
    public $role;

    /**
     * {@inheritdoc}
     * Retorna o nome da tabela associada a este modelo
     */
    public static function tableName()
    {
        return '{{%user}}'; // Nome da tabela na base de dados (com prefixo se aplicável)
    }

    /**
     * {@inheritdoc}
     * Define os comportamentos do modelo
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class, // Comportamento que automaticamente preenche created_at e updated_at
        ];
    }

    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do modelo
     */
    public function rules()
    {
        return [
            // Define o valor padrão para status como 'Ativo'
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            // status deve ser um dos valores permitidos
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],

            // username e email são obrigatórios
            [['username', 'email'], 'required'],
            // Validação de tipo e tamanho dos campos
            [['username', 'email', 'password'], 'string', 'max' => 255],
            // password deve ter pelo menos 6 caracteres (apenas no cenário de criação)
            [['password'], 'string', 'min' => 6, 'on' => self::SCENARIO_CREATE],
            // Validação de formato de email
            ['email', 'email'],
            // username e email devem ser únicos
            [['username'], 'unique'],
            [['email'], 'unique'],
            // password é obrigatória apenas no cenário de criação
            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            // password_repeat é obrigatória apenas no cenário de criação
            ['password_repeat', 'required', 'on' => self::SCENARIO_CREATE],
            // password_repeat deve ser igual a password
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            // role é um campo seguro (não valida conteúdo, apenas permite atribuição)
            ['role', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     * Define os cenários de validação do modelo
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        // Campos permitidos no cenário de criação
        $scenarios[self::SCENARIO_CREATE] = ['username', 'email', 'password', 'password_repeat', 'status', 'role'];
        // Campos permitidos no cenário de atualização
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'password', 'password_repeat', 'status', 'role'];
        $scenarios = parent::scenarios();
        $scenarios['updateProfile'] = ['nome_completo', 'email'];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     * Define os rótulos para os atributos (usados em formulários)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID', // Rótulo para o ID
            'username' => 'Nome de Utilizador', // Rótulo para o nome de utilizador
            'email' => 'Email', // Rótulo para o email
            'password' => 'Password', // Rótulo para a palavra-passe
            'password_repeat' => 'Confirmar Password', // Rótulo para confirmação de palavra-passe
            'role' => 'Role', // Rótulo para a role
            'status' => 'Estado', // Rótulo para o estado
            'created_at' => 'Data de Criação', // Rótulo para data de criação
            'updated_at' => 'Data de Atualização', // Rótulo para data de atualização
        ];
    }

    /**
     * {@inheritdoc}
     * Executa antes de salvar o registo
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Se for um novo registo, gera a auth_key
            if ($this->isNewRecord) {
                $this->generateAuthKey();
            }

            // Se foi fornecida uma nova palavra-passe, gera o hash
            if (!empty($this->password)) {
                $this->setPassword($this->password);
            }

            return true;
        }
        return false;
    }

    /**
     * Executa após salvar o registo
     * Atribui roles ao utilizador e publica eventos MQTT
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Atribui role se fornecida
        if ($this->role) {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($this->id); // Remove todas as roles existentes

            $role = $auth->getRole($this->role);
            if ($role) {
                $auth->assign($role, $this->id); // Atribui a nova role
            }
        }

        // ==============================================
        // CÓDIGO MQTT PARA UTILIZADOR
        // ==============================================

        // Preparar dados do utilizador para envio MQTT
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'status' => $this->status,
            'status_text' => $this->getStatusLabel(),
            'created_at' => date('Y-m-d H:i:s', $this->created_at),
            'updated_at' => date('Y-m-d H:i:s', $this->updated_at),
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        // Obter roles do utilizador para incluir na mensagem
        $auth = Yii::$app->authManager;
        $userRoles = $auth->getRolesByUser($this->id);
        $rolesArray = [];
        foreach ($userRoles as $roleName => $role) {
            $rolesArray[] = $roleName;
        }
        $data['roles'] = $rolesArray;

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE); // Converter para JSON

        // Publicar no Mosquitto MQTT baseado no tipo de operação
        if ($insert) {
            $this->FazPublishNoMosquitto("INSERT_USER", $myJSON);
        } else {
            $this->FazPublishNoMosquitto("UPDATE_USER", $myJSON);

            // Notificação específica para mudança de status
            if (isset($changedAttributes['status'])) {
                $oldStatus = $changedAttributes['status'];
                $newStatus = $this->status;

                $statusData = [
                    'id' => $this->id,
                    'username' => $this->username,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'old_status_text' => $this->getStatusLabelFromCode($oldStatus),
                    'new_status_text' => $this->getStatusLabel(),
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                $statusJSON = json_encode($statusData, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("STATUS_CHANGED_USER", $statusJSON);
            }

            // Notificação específica para mudança de email
            if (isset($changedAttributes['email'])) {
                $oldEmail = $changedAttributes['email'];
                $newEmail = $this->email;

                $emailData = [
                    'id' => $this->id,
                    'username' => $this->username,
                    'old_email' => $oldEmail,
                    'new_email' => $newEmail,
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                $emailJSON = json_encode($emailData, JSON_UNESCAPED_UNICODE);
                $this->FazPublishNoMosquitto("EMAIL_CHANGED_USER", $emailJSON);
            }
        }
    }

    /**
     * Obtém o rótulo do status a partir do código numérico
     * @param int $status - Código do status
     * @return string - Rótulo do status
     */
    private function getStatusLabelFromCode($status)
    {
        $statuses = [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
            self::STATUS_DELETED => 'Eliminado',
        ];

        return $statuses[$status] ?? 'Desconhecido';
    }

    /**
     * Executa após eliminar o registo
     */
    public function afterDelete()
    {
        parent::afterDelete();

        // ==============================================
        // CÓDIGO MQTT PARA EXCLUSÃO DE UTILIZADOR
        // ==============================================

        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'deleted_at' => date('Y-m-d H:i:s'), // Data da eliminação
            'timestamp' => date('Y-m-d H:i:s') // Timestamp atual
        ];

        $myJSON = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->FazPublishNoMosquitto("DELETE_USER", $myJSON);
    }

    /**
     * Obtém a relação com as atribuições de autenticação (auth_assignment)
     * @return \yii\db\ActiveQuery - Query para obter as atribuições
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(\yii\db\ActiveRecord::class, ['user_id' => 'id'])
            ->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * Obtém a relação com as roles através da tabela auth_assignment
     * @return \yii\db\ActiveQuery - Query para obter as roles
     */
    public function getRoles()
    {
        return $this->hasMany(\yii\db\ActiveRecord::class, ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * Verifica se o utilizador tem uma role específica
     * @param string $roleName - Nome da role a verificar
     * @return bool - True se o utilizador tiver a role
     */
    public function hasRole($roleName)
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return isset($roles[$roleName]);
    }

    /**
     * Obtém os nomes das roles do utilizador como array
     * @return array - Array com os nomes das roles
     */
    public function getRoleNames()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return array_keys($roles);
    }

    /**
     * Obtém a role primária do utilizador (primeira role da lista)
     * @return string|null - Nome da role primária ou null se não tiver roles
     */
    public function getPrimaryRole()
    {
        $roles = $this->getRoleNames();
        return !empty($roles) ? $roles[0] : null;
    }

    /**
     * Eliminação "soft" - marca o utilizador como inativo em vez de eliminar fisicamente
     * @return bool - True se a operação for bem-sucedida
     */
    public function softDelete()
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save(false);
    }

    /**
     * Restaura um utilizador previamente marcado como inativo
     * @return bool - True se a operação for bem-sucedida
     */
    public function restore()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * Verifica se o utilizador está eliminado (inativo)
     * @return bool - True se o status for 'Inativo'
     */
    public function isDeleted()
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Query para utilizadores ativos (exclui utilizadores inativos)
     * @return \yii\db\ActiveQuery - Query para utilizadores ativos
     */
    public static function findActive()
    {
        return static::find()->where(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     * Encontra um utilizador pelo seu ID (apenas utilizadores ativos)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     * Não implementado nesta versão (para APIs REST)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Encontra um utilizador pelo nome de utilizador (apenas utilizadores ativos)
     * @param string $username - Nome de utilizador
     * @return static|null - Objeto User ou null se não encontrado
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Encontra um utilizador pelo token de redefinição de palavra-passe
     * @param string $token - Token de redefinição de palavra-passe
     * @return static|null - Objeto User ou null se token inválido
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Encontra um utilizador pelo token de verificação de email
     * @param string $token - Token de verificação de email
     * @return static|null - Objeto User ou null se não encontrado
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Verifica se um token de redefinição de palavra-passe é válido
     * @param string $token - Token a verificar
     * @return bool - True se o token for válido
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     * Retorna o ID do utilizador
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     * Retorna a chave de autenticação
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     * Valida a chave de autenticação
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Valida uma palavra-passe
     * @param string $password - Palavra-passe a validar
     * @return bool - True se a palavra-passe for válida
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Gera o hash da palavra-passe e define no modelo
     * @param string $password - Palavra-passe em texto plano
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Gera a chave de autenticação para "lembrar-me"
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Gera um token para redefinição de palavra-passe
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Gera um token para verificação de email
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Remove o token de redefinição de palavra-passe
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Obtém o rótulo do status atual
     * @return string - Rótulo do status
     */
    public function getStatusLabel()
    {
        $statuses = [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_INACTIVE => 'Inativo',
            self::STATUS_DELETED => 'Eliminado',
        ];

        return $statuses[$this->status] ?? 'Desconhecido';
    }

    /**
     * Obtém a data de criação formatada
     * @return string - Data formatada
     */
    public function getCreatedDate()
    {
        return Yii::$app->formatter->asDatetime($this->created_at);
    }

    /**
     * Obtém a data de atualização formatada
     * @return string - Data formatada
     */
    public function getUpdatedDate()
    {
        return Yii::$app->formatter->asDatetime($this->updated_at);
    }

    // ==============================================
    // MÉTODOS PARA COMUNICAÇÃO MQTT
    // ==============================================

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
            $client_id = "yii_user_" . uniqid(); // ID único do cliente

            // Criar instância do cliente MQTT
            $mqtt = new \backend\mosquitto\phpMQTT($server, $port, $client_id);

            // Tentar conectar ao servidor MQTT (timeout de 5 segundos)
            if ($mqtt->connect(true, null, null, null, 5)) {
                // Publicar a mensagem no tópico especificado (QoS 0 = sem confirmação)
                $mqtt->publish($canal, $msg, 0);
                $mqtt->close(); // Fechar a conexão

                // Log de sucesso
                error_log("✅ MQTT User: Publicado em $canal - ID: " . json_decode($msg)->id);

                // Log adicional em arquivo para debug
                file_put_contents(Yii::getAlias('@backend') . '/mqtt_user.log',
                    date('Y-m-d H:i:s') . " | $canal | " . substr($msg, 0, 100) . "\n",
                    FILE_APPEND
                );

                return true; // Sucesso
            }

            // Se falhar a conexão
            error_log("❌ MQTT User: Falha na conexão para $canal");
            return false;

        } catch (\Exception $e) {
            // Em caso de exceção
            error_log("❌ MQTT User Exception: " . $e->getMessage());
            return false;
        }
    }


}