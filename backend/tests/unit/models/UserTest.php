<?php

namespace backend\tests\unit\models;

use common\models\User;
use Yii; // ← ADICIONAR ESTA LINHA

class UserTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    public function testValidacaoCamposObrigatorios()
    {
        $user = new User();

        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('username', $user->errors);
        $this->assertArrayHasKey('email', $user->errors);
    }

    public function testValidacaoEmail()
    {
        $user = new User();
        $user->username = 'testuser';
        $user->email = 'email-invalido';
        $user->status = User::STATUS_ACTIVE;

        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('email', $user->errors);
    }

    public function testValidacaoUsernameUnico()
    {
        // Criar um usuário primeiro
        $username = 'usuario_teste_' . uniqid();
        $this->tester->haveRecord('common\models\User', [
            'username' => $username,
            'email' => 'test1@example.com',
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Tentar criar outro usuário com mesmo username
        $user = new User([
            'username' => $username,
            'email' => 'test2@example.com',
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('username', $user->errors);
    }

    public function testValidacaoEmailUnico()
    {
        // Criar um usuário primeiro
        $email = 'email_teste_' . uniqid() . '@example.com';
        $this->tester->haveRecord('common\models\User', [
            'username' => 'user1_' . uniqid(),
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Tentar criar outro usuário com mesmo email
        $user = new User([
            'username' => 'user2_' . uniqid(),
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('email', $user->errors);
    }

    public function testModeloValido()
    {
        $username = 'usuario_valido_' . uniqid();
        $email = 'email_valido_' . uniqid() . '@example.com';

        $user = new User([
            'username' => $username,
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
            'password' => 'senhaSegura123',
            'password_repeat' => 'senhaSegura123',
        ]);

        // Usar cenário create para validar password
        $user->scenario = User::SCENARIO_CREATE;

        $this->assertTrue($user->validate(),
            'Erros: ' . print_r($user->errors, true));
    }

    public function testValidacaoStatus()
    {
        $user = new User();
        $user->username = 'testuser';
        $user->email = 'test@example.com';
        $user->status = 999; // Status inválido

        $this->assertFalse($user->validate());
        $this->assertArrayHasKey('status', $user->errors);
    }

    public function testStatusValidos()
    {
        $statusValidos = [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_DELETED];

        foreach ($statusValidos as $status) {
            $user = new User([
                'username' => 'user_' . $status . '_' . uniqid(),
                'email' => 'email_' . $status . '_' . uniqid() . '@example.com',
                'status' => $status,
            ]);

            $user->validate();
            $this->assertArrayNotHasKey('status', $user->errors,
                "Status $status deveria ser válido");
        }
    }

    public function testMetodosEstaticosFinders()
    {
        // Testar métodos de busca estáticos
        $username = 'user_finder_' . uniqid();

        // Criar usuário ativo
        $userId = $this->tester->haveRecord('common\models\User', [
            'username' => $username,
            'email' => 'finder@example.com',
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Testar findIdentity (deve encontrar usuário ativo)
        $user = User::findIdentity($userId);
        $this->assertNotNull($user);
        $this->assertEquals($username, $user->username);

        // Testar findByUsername (deve encontrar usuário ativo)
        $userByUsername = User::findByUsername($username);
        $this->assertNotNull($userByUsername);
        $this->assertEquals($userId, $userByUsername->id);
    }

    public function testMetodosAutenticacao()
    {
        $password = 'minhaSenha123';
        $user = new User();

        // Testar setPassword e validatePassword
        $user->setPassword($password);
        $this->assertTrue($user->validatePassword($password));
        $this->assertFalse($user->validatePassword('senhaErrada'));

        // Testar generateAuthKey
        $user->generateAuthKey();
        $this->assertNotEmpty($user->auth_key);

        // Testar validateAuthKey
        $authKey = $user->auth_key;
        $this->assertTrue($user->validateAuthKey($authKey));
        $this->assertFalse($user->validateAuthKey('chaveInvalida'));
    }

    public function testMetodosIdentidade()
    {
        $username = 'user_identity_' . uniqid();

        $userId = $this->tester->haveRecord('common\models\User', [
            'username' => $username,
            'email' => 'identity@example.com',
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $user = User::findOne($userId);

        // Testar métodos da interface IdentityInterface
        $this->assertEquals($userId, $user->getId());
        $this->assertNotEmpty($user->getAuthKey());

        // Testar getAuthKey e validateAuthKey
        $authKey = $user->getAuthKey();
        $this->assertTrue($user->validateAuthKey($authKey));
    }

    public function testScenarios()
    {
        $user = new User();
        $scenarios = $user->scenarios();

        $this->assertIsArray($scenarios);
        $this->assertArrayHasKey(User::SCENARIO_CREATE, $scenarios);
        $this->assertArrayHasKey(User::SCENARIO_UPDATE, $scenarios);
        $this->assertArrayHasKey('default', $scenarios);

        // Verificar que o cenário create tem campos de password
        $this->assertContains('password', $scenarios[User::SCENARIO_CREATE]);
        $this->assertContains('password_repeat', $scenarios[User::SCENARIO_CREATE]);
    }

    public function testSoftDeleteERestore()
    {
        $username = 'user_softdelete_' . uniqid();

        $userId = $this->tester->haveRecord('common\models\User', [
            'username' => $username,
            'email' => 'softdelete@example.com',
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $user = User::findOne($userId);

        // Testar softDelete
        $this->assertTrue($user->softDelete());
        $this->assertEquals(User::STATUS_INACTIVE, $user->status);
        $this->assertTrue($user->isDeleted());

        // Testar restore
        $this->assertTrue($user->restore());
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        $this->assertFalse($user->isDeleted());
    }

    public function testFindActive()
    {
        // Criar alguns usuários com diferentes status
        $activeUser = $this->tester->haveRecord('common\models\User', [
            'username' => 'active_' . uniqid(),
            'email' => 'active@example.com',
            'status' => User::STATUS_ACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $inactiveUser = $this->tester->haveRecord('common\models\User', [
            'username' => 'inactive_' . uniqid(),
            'email' => 'inactive@example.com',
            'status' => User::STATUS_INACTIVE,
            'password_hash' => Yii::$app->security->generatePasswordHash('password123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // Testar findActive (deve retornar apenas ativos)
        $activeUsers = User::findActive()->all();
        $this->assertNotEmpty($activeUsers);

        // Verificar que todos retornados estão ativos
        foreach ($activeUsers as $user) {
            $this->assertEquals(User::STATUS_ACTIVE, $user->status);
        }
    }

    public function testGetStatusLabel()
    {
        $user = new User();

        $user->status = User::STATUS_ACTIVE;
        $this->assertEquals('Ativo', $user->getStatusLabel());

        $user->status = User::STATUS_INACTIVE;
        $this->assertEquals('Inativo', $user->getStatusLabel());

        $user->status = User::STATUS_DELETED;
        $this->assertEquals('Eliminado', $user->getStatusLabel());

        $user->status = 999; // Status desconhecido
        $this->assertEquals('Desconhecido', $user->getStatusLabel());
    }

    public function testRegrasValidacao()
    {
        $user = new User();
        $rules = $user->rules();

        $this->assertIsArray($rules);

        // Verificar regras básicas
        $temUsernameObrigatorio = false;
        $temEmailObrigatorio = false;
        $temEmailValido = false;

        foreach ($rules as $rule) {
            if (isset($rule[0]) && isset($rule[1])) {
                $atributos = (array)$rule[0];
                $tipo = $rule[1];

                if (in_array('username', $atributos) && $tipo === 'required') {
                    $temUsernameObrigatorio = true;
                }

                if (in_array('email', $atributos) && $tipo === 'required') {
                    $temEmailObrigatorio = true;
                }

                if (in_array('email', $atributos) && $tipo === 'email') {
                    $temEmailValido = true;
                }
            }
        }

        $this->assertTrue($temUsernameObrigatorio, 'Deveria ter regra de username obrigatório');
        $this->assertTrue($temEmailObrigatorio, 'Deveria ter regra de email obrigatório');
        $this->assertTrue($temEmailValido, 'Deveria ter regra de email válido');
    }
}