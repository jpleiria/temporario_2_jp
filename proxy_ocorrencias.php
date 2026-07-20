<?php
/**
 * Proxy para as ocorrências ativas do SIGMOS (Leiria).
 *
 * O browser dos visitantes do dashboard não consegue chamar diretamente
 * https://leiria.sigmos.pt (bloqueio CORS). Este script corre no TEU servidor,
 * faz o pedido "servidor a servidor" (sem restrição CORS) e devolve o
 * resultado já em JSON para o dashboard consultar.
 *
 * Coloca este ficheiro na raiz do site (junto ao index.html do dashboard) e,
 * no ocorrencias.html, o PROXY_ENDPOINT já aponta para 'proxy_ocorrencias.php'.
 */

header('Content-Type: application/json; charset=utf-8');
// Se quiseres restringir a quem pode chamar este proxy, troca o '*' pelo teu domínio:
// header('Access-Control-Allow-Origin: https://leiria.sigmos.pt');
header('Access-Control-Allow-Origin: *');

$url = 'https://leiria.sigmos.pt/app/sigmos/rpc_ocorrencias_table_fvd.php';

$params = [
    'op'              => 'fvd_ocorrencias',
    'sigmos'          => '5fcdfe5046f452b7df1cbeecb9b9b071',
    'oco_lista_local' => 'localidade',
    'key'             => '4b203bfac32b55de9401bc28d8d1bc85',
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Dashboard Ocorrencias Leiria)');

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($html === false || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        'error'  => 'Falha ao contactar o SIGMOS',
        'detail' => $curlErr ?: "HTTP $httpCode",
    ]);
    exit;
}

echo json_encode(['html' => $html]);
