<?php
 
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
 
class CategoriasTest extends TestCase
{
    private function getApp()
    {
        return require __DIR__ . '/../src/app.php';
    }
 
    // Função auxiliar para enviar as requisições
    private function enviarRequisicao($metodo, $rota, $dados = null)
    {
        $app = $this->getApp();
 
        $request = (new ServerRequestFactory())
            ->createServerRequest($metodo, $rota)
            ->withHeader('Content-Type', 'application/json');
 
        if ($dados !== null) {
            $request->getBody()->write(json_encode($dados));
            $request->getBody()->rewind();
 
            $request = $request->withParsedBody($dados);
        }
 
        return $app->handle($request);
    }
 
    // 1. GET - Listar todas as categorias
    public function test_deve_listar_todas_as_categorias(): void
    {
        $response = $this->enviarRequisicao('GET', '/categorias');
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertIsArray($corpo);
        $this->assertEquals('Salario', $corpo[0]['nome']);
    }
 
    // 2. GET - Buscar categoria por ID
    public function test_deve_buscar_categoria_por_id(): void
    {
        $response = $this->enviarRequisicao('GET', '/categorias/1');
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals(1, $corpo['id']);
        $this->assertEquals('Salario', $corpo['nome']);
    }
 
    // 3. POST - Criar nova categoria
    public function test_deve_criar_nova_categoria(): void
    {
        $dados = [
            'nome' => 'Transporte',
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('POST', '/categorias', $dados);
 
        $this->assertEquals(201, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Transporte', $corpo['nome']);
        $this->assertArrayHasKey('id', $corpo);
    }
 
    // 4. PUT - Atualizar categoria existente
    public function test_deve_atualizar_categoria_existente(): void
    {
        $dados = [
            'nome' => 'Saude',
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('PUT', '/categorias/2', $dados);
 
        $this->assertEquals(200, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Saude', $corpo['nome']);
    }
 
    // 5. DELETE - Excluir categoria existente
    public function test_deve_deletar_categoria(): void
    {
        $response = $this->enviarRequisicao('DELETE', '/categorias/3');
 
        $this->assertEquals(204, $response->getStatusCode());
    }
 
    // 6. POST - Tentar cadastrar sem preencher os campos obrigatórios
    public function test_nao_deve_criar_categoria_sem_campos_obrigatorios(): void
    {
        $dados = [
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('POST', '/categorias', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Campos obrigatórios ausentes', $corpo['erro']);
    }
 
    // 7. POST - Tentar cadastrar com tipo inválido
    public function test_nao_deve_criar_categoria_com_tipo_invalido(): void
    {
        $dados = [
            'nome' => 'Investimentos',
            'tipo' => 'investimento'
        ];
 
        $response = $this->enviarRequisicao('POST', '/categorias', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Tipo inválido. Informe receita ou despesa', $corpo['erro']);
    }
 
    // 8. POST - Tentar cadastrar uma categoria com nome duplicado
    public function test_nao_deve_criar_categoria_com_nome_duplicado(): void
    {
        $dados = [
            'nome' => 'salario',
            'tipo' => 'receita'
        ];
 
        $response = $this->enviarRequisicao('POST', '/categorias', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Já existe uma categoria com esse nome', $corpo['erro']);
    }
 
    // 9. PUT - Tentar atualizar sem preencher os campos obrigatórios
    public function test_nao_deve_atualizar_categoria_sem_campos_obrigatorios(): void
    {
        $dados = [
            'nome' => '',
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('PUT', '/categorias/2', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Campos obrigatórios ausentes', $corpo['erro']);
    }
 
    // 10. PUT - Tentar atualizar com tipo inválido
    public function test_nao_deve_atualizar_categoria_com_tipo_invalido(): void
    {
        $dados = [
            'nome' => 'Saude',
            'tipo' => 'investimento'
        ];
 
        $response = $this->enviarRequisicao('PUT', '/categorias/2', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Tipo inválido. Informe receita ou despesa', $corpo['erro']);
    }
 
    // 11. PUT - Tentar atualizar com nome que já pertence a outra categoria
    public function test_nao_deve_atualizar_categoria_com_nome_duplicado(): void
    {
        $dados = [
            'nome' => 'Aluguel',
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('PUT', '/categorias/2', $dados);
 
        $this->assertEquals(400, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Já existe uma categoria com esse nome', $corpo['erro']);
    }
 
    // 12. GET - Tentar buscar uma categoria que não existe
    public function test_deve_retornar_erro_ao_buscar_categoria_inexistente(): void
    {
        $response = $this->enviarRequisicao('GET', '/categorias/999');
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Categoria não encontrada', $corpo['erro']);
    }
 
    // 13. PUT - Tentar atualizar uma categoria que não existe
    public function test_deve_retornar_erro_ao_atualizar_categoria_inexistente(): void
    {
        $dados = [
            'nome' => 'Transporte',
            'tipo' => 'despesa'
        ];
 
        $response = $this->enviarRequisicao('PUT', '/categorias/999', $dados);
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Categoria não encontrada', $corpo['erro']);
    }
 
    // 14. DELETE - Tentar excluir uma categoria que não existe
    public function test_deve_retornar_erro_ao_deletar_categoria_inexistente(): void
    {
        $response = $this->enviarRequisicao('DELETE', '/categorias/999');
 
        $this->assertEquals(404, $response->getStatusCode());
 
        $corpo = json_decode((string) $response->getBody(), true);
 
        $this->assertEquals('Categoria não encontrada', $corpo['erro']);
    }
}
 
