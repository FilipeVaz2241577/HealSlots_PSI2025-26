<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required', 'message' => 'Este campo é obrigatório.'],
            // email has to be a valid email address
            ['email', 'email', 'message' => 'Por favor insira um email válido.'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha', 'message' => 'Código de verificação incorreto.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Nome',
            'email' => 'Email',
            'subject' => 'Assunto',
            'body' => 'Mensagem',
            'verifyCode' => 'Código de Verificação',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     * @return bool whether the email was sent
     */
    public function sendEmail($email)
    {
        // Configurar o email para suporte
        return Yii::$app->mailer->compose()
            ->setTo($email ?: Yii::$app->params['supportEmail']) // Usar email de suporte se não especificado
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setReplyTo([$this->email => $this->name])
            ->setSubject('Contacto Suporte: ' . $this->subject)
            ->setTextBody($this->getEmailBody())
            ->send();
    }

    /**
     * Format the email body with contact information
     *
     * @return string
     */
    protected function getEmailBody()
    {
        return "
        Nova mensagem de contacto do suporte:
        
        Nome: {$this->name}
        Email: {$this->email}
        Assunto: {$this->subject}
        
        Mensagem:
        {$this->body}
        
        ---
        Enviado através do sistema HealSlots
        ";
    }

    /**
     * Sends an email to the support team
     *
     * @return bool whether the email was sent
     */
    public function sendSupportEmail()
    {
        return $this->sendEmail(Yii::$app->params['supportEmail'] ?? Yii::$app->params['adminEmail']);
    }
}