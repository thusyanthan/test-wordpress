<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client();

$plugins = [
  "10275010" => "Ads-pro",
  "10707562" => "Chimpmate-pro"
];

foreach ($plugins as $plugin_id => $plugin_name) {
  $response = $client->request('GET',
    'https://api.envato.com/v3/market/buyer/download',
    [
      'query' => [
        'item_id' => $plugin_id,
        'shorten_url' => 'true'
      ],
      'headers' => [
        'Authorization' => 'Bearer IgrWeggIqwoEkz6hweptw6i7MJhZKa9v'
      ]
    ]
  );

  $statusCode = $response->getStatusCode();

  if ($statusCode ==  200) {
    $body = \json_decode($response->getBody()->getContents(), TRUE);

    $pluginDownloadUrl = $body['download_url'];
    $path = __DIR__ .'/../site/wp-content/plugins/tmp';

    if ($plugin_name == 'Ads-pro') {
      $path = __DIR__ .'/../site/wp-content/plugins/ads-pro/ads-pro-update.zip';
    } elseif ($plugin_name == 'Chimpmate-pro') {
      $path = __DIR__ .'/../site/wp-content/plugins/chimpmate-pro/chimpmate-pro-update.zip';
    }

    $resource = fopen($path, 'w');
    $response = $client->request('GET', $pluginDownloadUrl, ['sink' => $resource]);
  } else {
    echo "Error: Unable to download Envato plugin " . $plugin_name . ".\n";
  }
}
