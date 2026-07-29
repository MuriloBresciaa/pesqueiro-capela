<?php
/**
 * gerar_pix.php — Pesqueiro Capela
 * Gera um pagamento PIX via Mercado Pago e retorna o QR Code.
 *
 * INSTRUÇÃO DE CONFIGURAÇÃO:
 *   Substitua o valor de $access_token pelo seu Access Token de PRODUÇÃO
 *   obtido em: https://www.mercadopago.com.br/settings/account/credentials
 */

// ─────────────────────────────────────────────────────────────
// 1. CONFIGURAÇÃO — lê token de produção via variável de ambiente
// ─────────────────────────────────────────────────────────────
$access_token = getenv('MERCADO_PAGO_ACCESS_TOKEN') ?: ($_ENV['MERCADO_PAGO_ACCESS_TOKEN'] ?? '');

// ─────────────────────────────────────────────────────────────
// 2. HEADERS & CORS
// ─────────────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responde ao preflight OPTIONS do browser sem processar nada
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─────────────────────────────────────────────────────────────
// 3. VALIDAÇÃO DO MÉTODO HTTP
// ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido. Use POST.']);
    exit;
}

// ─────────────────────────────────────────────────────────────
// 4. LEITURA E VALIDAÇÃO DO PAYLOAD JSON
// ─────────────────────────────────────────────────────────────
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload inválido. Envie um JSON válido.']);
    exit;
}

// Campos obrigatórios
$required = ['name', 'phone', 'cpf', 'amount', 'description'];
foreach ($required as $field) {
    if (!isset($body[$field]) || (is_string($body[$field]) && trim($body[$field]) === '')) {
        http_response_code(400);
        echo json_encode(['error' => "Campo obrigatório ausente: {$field}"]);
        exit;
    }
}

$name = trim($body['name']);
$phone = trim($body['phone']);
$cpf = preg_replace('/\D/', '', trim($body['cpf'])); // remove pontuação
$amount = (float) $body['amount'];
$description = trim($body['description']);

// Validação do valor (mínimo R$ 1,00 e máximo R$ 10.000,00 como guarda)
if ($amount < 1.00 || $amount > 10000.00) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor inválido. Deve ser entre R$ 1,00 e R$ 10.000,00.']);
    exit;
}

// Validação da descrição (entre 3 e 200 chars)
if (mb_strlen($description) < 3 || mb_strlen($description) > 200) {
    http_response_code(400);
    echo json_encode(['error' => 'Descrição inválida. Use entre 3 e 200 caracteres.']);
    exit;
}

// Validação do telefone (mínimo 10 dígitos: DDD + número)
$clean_phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($clean_phone) < 10 || strlen($clean_phone) > 11) {
    http_response_code(400);
    echo json_encode(['error' => 'Telefone inválido. Informe DDD + número (mínimo 10 dígitos).']);
    exit;
}

// Geração do e-mail sintético para satisfazer a API do Mercado Pago
$synthetic_email = $clean_phone . '@pesqueirocapela.com.br';

// Validação de CPF (11 dígitos numéricos)
if (!preg_match('/^\d{11}$/', $cpf)) {
    http_response_code(400);
    echo json_encode(['error' => 'CPF inválido. Informe 11 dígitos numéricos.']);
    exit;
}

// Validação do nome (mínimo 2 palavras)
if (str_word_count($name) < 2) {
    http_response_code(400);
    echo json_encode(['error' => 'Informe o nome completo (nome e sobrenome).']);
    exit;
}

// ─────────────────────────────────────────────────────────────
// 5. MONTAGEM DO PAYLOAD MERCADO PAGO
// ─────────────────────────────────────────────────────────────

// Gera um idempotency key único para evitar cobranças duplicadas
$idempotency_key = 'pesqueiro-' . md5($cpf . $clean_phone . time());

$payment_data = [
    'transaction_amount' => $amount,
    'description' => $description,
    'payment_method_id' => 'pix',
    'payer' => [
        'email' => $synthetic_email,
        'first_name' => explode(' ', $name)[0],
        'last_name' => implode(' ', array_slice(explode(' ', $name), 1)),
        'identification' => [
            'type' => 'CPF',
            'number' => $cpf,
        ],
    ],
    // Expiração em 30 minutos
    'date_of_expiration' => date('Y-m-d\TH:i:s.000O', strtotime('+30 minutes')),
];

// ─────────────────────────────────────────────────────────────
// 6. CHAMADA CURL À API DO MERCADO PAGO
// ─────────────────────────────────────────────────────────────
$ch = curl_init('https://api.mercadopago.com/v1/payments');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payment_data),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
        'X-Idempotency-Key: ' . $idempotency_key,
    ],
]);

$response_body = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// ─────────────────────────────────────────────────────────────
// 7. TRATAMENTO DE ERROS DE REDE / cURL
// ─────────────────────────────────────────────────────────────
if ($curl_error) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Falha de comunicação com o Mercado Pago.',
        'message' => $curl_error,
    ]);
    exit;
}

$mp_response = json_decode($response_body, true);

// ─────────────────────────────────────────────────────────────
// 8. TRATAMENTO DE ERROS HTTP DO MERCADO PAGO
// ─────────────────────────────────────────────────────────────
if ($http_code < 200 || $http_code >= 300) {
    $mp_message = $mp_response['message']
        ?? ($mp_response['cause'][0]['description'] ?? 'Erro desconhecido do Mercado Pago.');

    http_response_code($http_code >= 400 && $http_code < 500 ? 400 : 502);
    echo json_encode([
        'error' => 'Erro ao criar pagamento PIX.',
        'message' => $mp_message,
        'mp_status' => $http_code,
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────
// 9. EXTRAÇÃO DOS DADOS DO QR CODE
// ─────────────────────────────────────────────────────────────
$qr_code = $mp_response['point_of_interaction']['transaction_data']['qr_code'] ?? null;
$qr_code_base64 = $mp_response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;

if (!$qr_code || !$qr_code_base64) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Resposta do Mercado Pago não contém os dados do PIX.',
        'message' => 'Verifique se o pagamento foi criado corretamente no painel do Mercado Pago.',
    ]);
    exit;
}

// ─────────────────────────────────────────────────────────────
// 10. RESPOSTA DE SUCESSO
// ─────────────────────────────────────────────────────────────
http_response_code(200);
echo json_encode([
    'success' => true,
    'payment_id' => $mp_response['id'],
    'status' => $mp_response['status'],
    'qr_code' => $qr_code,
    'qr_code_base64' => $qr_code_base64,
    'expires_at' => $mp_response['date_of_expiration'] ?? null,
]);
exit;
