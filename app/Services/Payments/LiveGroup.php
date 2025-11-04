<?php
namespace App\Services\Payments;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class LiveGroup {
  private Client $http; private string $merchant; private string $apiKey;
  public function __construct() {
    $this->merchant = Config::get('livegroup.merchant_id');
    $this->apiKey   = Config::get('livegroup.api_key');
    $this->http     = new Client([
      'base_uri' => Config::get('livegroup.base_url'),
      'timeout'  => Config::get('livegroup.timeout'),
    ]);
  }
  public function createIntent(array $payload): array {
    return $this->req('POST', '/v1/payments/intents', $payload);
  }
  public function getIntent(string $intentId): array {
    return $this->req('GET', "/v1/payments/intents/{$intentId}");
  }
  private function req(string $m, string $path, array $json=[]): array {
    $res = $this->http->request($m, $path, [
      'headers' => [
        'Authorization' => "Bearer {$this->apiKey}",
        'X-Merchant-Id' => $this->merchant,
        'Accept' => 'application/json',
      ],
      'json' => $json ?: null,
    ]);
    return json_decode((string)$res->getBody(), true);
  }
}
