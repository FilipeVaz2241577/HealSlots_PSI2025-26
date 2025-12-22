<?php

namespace backend\tests\unit\models;

use common\models\Bloco;

class BlocoTest extends \Codeception\Test\Unit
{
    /**
     * @var \backend\tests\UnitTester
     */
    protected $tester;

    public function testValidacaoNomeObrigatorio()
    {
        $bloco = new Bloco();

        // Teste: Nome é obrigatório
        $this->assertFalse($bloco->validate());
        $this->assertArrayHasKey('nome', $bloco->errors);
    }

    public function testValidacaoNomeMaximoCaracteres()
    {
        $bloco = new Bloco();
        $bloco->nome = str_repeat('a', 101); // 101 caracteres - acima do limite
        $bloco->estado = 'ativo';

        $this->assertFalse($bloco->validate());
        $this->assertArrayHasKey('nome', $bloco->errors);
    }

    public function testValidacaoEstadoValido()
    {
        $bloco = new Bloco();
        $bloco->nome = 'Bloco Teste';
        $bloco->estado = 'estado_invalido';

        $this->assertFalse($bloco->validate());
        $this->assertArrayHasKey('estado', $bloco->errors);
    }

    public function testModeloValido()
    {
        // Para testar um modelo válido SEM usar banco de dados,
        // precisamos mockar a validação ou usar um bloco que já existe
        // Vamos usar o $tester do Codeception para criar um bloco real

        $id = $this->tester->haveRecord('common\models\Bloco', [
            'nome' => 'Bloco Teste ' . uniqid(),
            'estado' => 'ativo',
        ]);

        $bloco = Bloco::findOne($id);
        $this->assertNotNull($bloco);

        // Agora testamos se o modelo carregado é válido
        $this->assertTrue($bloco->validate());
    }

    public function testMetodosEstaticos()
    {
        // Testar métodos estáticos (não precisam de BD)
        $opts = Bloco::optsEstado();

        $this->assertIsArray($opts);
        $this->assertArrayHasKey('ativo', $opts);
        $this->assertArrayHasKey('desativado', $opts);
        $this->assertEquals('Ativo', $opts['ativo']);
        $this->assertEquals('Desativado', $opts['desativado']);
    }

    public function testRegrasValidacao()
    {
        // Teste alternativo: verificar as regras programaticamente
        $bloco = new Bloco();
        $rules = $bloco->rules();

        $this->assertIsArray($rules);

        // Verificar se tem regra para nome obrigatório
        $temNomeObrigatorio = false;
        foreach ($rules as $rule) {
            if (isset($rule[0]) && in_array('nome', (array)$rule[0]) && isset($rule[1]) && $rule[1] === 'required') {
                $temNomeObrigatorio = true;
                break;
            }
        }

        $this->assertTrue($temNomeObrigatorio, 'Deveria ter regra de nome obrigatório');
    }
}