<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Formulário de login
 * Esta classe representa o formulário de autenticação do sistema
 */
class LoginForm extends Model
{
    // Atributos públicos que correspondem aos campos do formulário
    public $username; // Nome de utilizador/email para login
    public $password; // Palavra-passe do utilizador
    public $rememberMe = true; // Checkbox "Lembrar-me" (ativado por padrão)

    private $_user; // Propriedade privada para armazenar o objeto do utilizador


    /**
     * {@inheritdoc}
     * Define as regras de validação para os atributos do formulário
     */
    public function rules()
    {
        return [
            // username e password são ambos obrigatórios
            [['username', 'password'], 'required'],
            // rememberMe deve ser um valor booleano (true/false)
            ['rememberMe', 'boolean'],
            // password é validado pelo método validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Valida a palavra-passe.
     * Este método serve como validação inline para o campo password.
     *
     * @param string $attribute o atributo atualmente sendo validado
     * @param array $params os pares nome-valor adicionais dados na regra
     */
    public function validatePassword($attribute, $params)
    {
        // Só procede se não houver erros anteriores
        if (!$this->hasErrors()) {
            // Obtém o utilizador com base no username
            $user = $this->getUser();

            // Verifica se o utilizador existe e se a palavra-passe é válida
            if (!$user || !$user->validatePassword($this->password)) {
                // Adiciona erro genérico por segurança (não revela se username existe)
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Autentica um utilizador usando o username e password fornecidos.
     *
     * @return bool true se o utilizador for autenticado com sucesso
     */
    public function login()
    {
        // Primeiro valida os dados do formulário
        if ($this->validate()) {
            // Se validação passar, efetua o login
            return Yii::$app->user->login(
                $this->getUser(), // Objeto do utilizador
                $this->rememberMe ? 3600 * 24 * 30 : 0 // Duração da sessão: 30 dias se "Lembrar-me" ativo, 0 se não
            );
        }

        // Retorna false se a validação falhar
        return false;
    }

    /**
     * Encontra o utilizador pelo [[username]]
     * Este método implementa caching para evitar múltiplas consultas à base de dados
     *
     * @return User|null O objeto User ou null se não encontrado
     */
    protected function getUser()
    {
        // Verifica se o utilizador já foi carregado (caching)
        if ($this->_user === null) {
            // Busca o utilizador na base de dados usando o método estático da classe User
            $this->_user = User::findByUsername($this->username);
        }

        // Retorna o objeto do utilizador (ou null)
        return $this->_user;
    }
}