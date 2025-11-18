<?php

namespace frontend\models;

use Yii;
use common\models\User;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        // Salvar o usuário primeiro
        if ($user->save()) {
            // Atribuir a role "TecnicoSaude" usando RBAC
            $auth = Yii::$app->authManager;
            $tecnicoRole = $auth->getRole('TecnicoSaude');

            if ($tecnicoRole) {
                $auth->assign($tecnicoRole, $user->id);
                Yii::info("Role TecnicoSaude atribuída ao usuário: " . $user->id, 'signup');
            } else {
                Yii::error("Role TecnicoSaude não encontrada no RBAC", 'signup');
            }

            return true;
        }

        return false;
    }
}