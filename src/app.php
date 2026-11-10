<?php
 
require __DIR__ . '/../vendor/autoload.php';
 
use Slim\Factory\AppFactory;
 
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
 
$categorias = [
    ['id' => 1, 'nome' => 'Salário', 'tipo' => 'receita'],
    ['id' => 2, 'nome' => 'Alimentação', 'tipo' => 'despesa'],
    ['id' => 3, 'nome' => 'Transporte', 'tipo' => 'despesa']
];
 
$app->get('/categorias', function ($request, $response) use ($categorias) {
 
    $response->getBody()->write(json_encode($categorias));
 
    return $response
 
        ->withHeader('Content-Type', 'application/json')
 
        ->withStatus(200);
 
});

$app->get('/categorias/{id}', function ($request, $response, $args) use ($categorias) {
 
    $id = $args['id'];
 
    foreach ($categorias as $categorias) {
        if ($categorias['id'] == $id) {
 
            $response->getBody()->write(json_encode($categorias));
 
            return $response
 
                ->withHeader('Content-Type', 'application/json')
 
                ->withStatus(200);
        }
    }
 
    $response->getBody()->write(json_encode([
        'erro' => 'Produto não encontrado'
    ]));
 
    return $response
 
        ->withHeader('Content-Type', 'application/json')
 
        ->withStatus(404);
 
});
 
$app->post('/categorias', function ($request, $response) use (&$categorias) {
    $dados = $request->getParsedBody();
    $novaCategoria = [
        'id' => count($categorias) + 1,
        'nome' => $dados['nome'],
        'pontos' => $dados['pontos']
    ];
    $categorias[] = $novaCategoria;
    $response->getBody()->write(json_encode($novaCategoria));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

$app->put('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $id = (int) $args['id'];
    $body = $request->getParsedBody();
    
    // Busca o índice do elemento dentro do array $categorias
    $indiceEncontrado = null;
    foreach ($categorias as $key => $categoria) {
        if ($categoria['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
    
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode(["erro" => "Categoria não encontrada"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Validação de campos obrigatórios para a atualização
    if (empty($body['nome']) || empty($body['tipo'])) {
        $response->getBody()->write(json_encode(["erro" => "Campos obrigatórios ausentes"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
 
    // Atualiza a missão na     posição encontrada
    $categorias[$indiceEncontrado] = [
        "id"      => $id,
        "nome"    => $body['nome'],
        "tipo"     => (int) $body['tipo'],
    ];
 
    $response->getBody()->write(json_encode($categorias[$indiceEncontrado]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->delete('/categorias/{id}', function ($request, $response, $args) use (&$categorias) {
    $id = (int) $args['id'];
    
    // Busca o índice do elemento para exclusão
    $indiceEncontrado = null;
    foreach ($categorias as $key => $categoria) {
        if ($categorias['id'] === $id) {
            $indiceEncontrado = $key;
            break;
        }
    }
    
    if ($indiceEncontrado === null) {
        $response->getBody()->write(json_encode(["erro" => "Categoria não encontrada"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
 
    // Remove do array na memória
    unset($categoria[$indiceEncontrado]);
 
    // Retorna status 204 No Content (padrão HTTP para deleções bem-sucedidas)
    return $response->withStatus(204);
 });
