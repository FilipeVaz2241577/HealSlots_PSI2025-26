<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $password_repeat write-only password repeat
 * @property string $role virtual property for form
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public $password;
    public $password_repeat;
    public $role;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],

            [['username', 'email'], 'required'],
            [['username', 'email', 'password'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6, 'on' => self::SCENARIO_CREATE],
            ['email', 'email'],
            [['username'], 'unique'],
            [['email'], 'unique'],
            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            ['password_repeat', 'required', 'on' => self::SCENARIO_CREATE],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['role', 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['username', 'email', 'password', 'password_repeat', 'status', 'role'];
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'password', 'password_repeat', 'status', 'role'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Nome de Utilizador',
            'email' => 'Email',
            'password' => 'Password',
            'password_repeat' => 'Confirmar Password',
            'role' => 'Role',
            'status' => 'Estado',
            'created_at' => 'Data de Criação',
            'updated_at' => 'Data de Atualização',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->generateAuthKey();
            }

            if (!empty($this->password)) {
                $this->setPassword($this->password);
            }

            return true;
        }
        return false;
    }

    /**
     * After save - assign role
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Assign role if provided
        if ($this->role) {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($this->id);

            $role = $auth->getRole($this->role);
            if ($role) {
                $auth->assign($role, $this->id);
            }
        }
    }

    /**
     * RELAÇÕES ADICIONADAS AQUI:
     */

    /**
     * Gets query for [[AuthAssignments]].
     * Relação com a tabela auth_assignment
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(\yii\db\ActiveRecord::class, ['user_id' => 'id'])
            ->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Roles]].
     * Relação com as roles através da tabela auth_assignment
     */
    public function getRoles()
    {
        return $this->hasMany(\yii\db\ActiveRecord::class, ['name' => 'item_name'])
            ->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($roleName)
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return isset($roles[$roleName]);
    }

    /**
     * Get user roles as array
     */
    public function getRoleNames()
    {
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($this->id);
        return array_keys($roles);
    }

    /**
     * Get primary role name
     */
    public function getPrimaryRole()
    {
        $roles = $this->getRoleNames();
        return !empty($roles) ? $roles[0] : null;
    }

    /**
     * Soft delete - marca como inativo
     */
    public function softDelete()
    {
        $this->status = self::STATUS_INACTIVE;
        return $this->save(false);
    }

    /**
     * Restaurar utilizador
     */
    public function restore()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * Verificar se está eliminado (inativo)
     */
    public function isDeleted()
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Query para utilizadores ativos (exclui inativos)
     */
    public static function findActive()
    {
        return static::find()->where(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
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
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
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
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get status label
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
     * Get formatted created date
     */
    public function getCreatedDate()
    {
        return Yii::$app->formatter->asDatetime($this->created_at);
    }

    /**
     * Get formatted updated date
     */
    public function getUpdatedDate()
    {
        return Yii::$app->formatter->asDatetime($this->updated_at);
    }
}