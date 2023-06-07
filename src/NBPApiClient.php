<?php

class NBPApiClient {
  private string $apiUrl = "http://api.nbp.pl/api/exchangerates/tables/a";
  private PDO $db;

  public function __construct(PDO $db) {
    $this -> db = $db;
  }

  public function fetchAndSaveCurrencyRates(): bool {
    try {
      $url = $this -> apiUrl;
      $response = $this -> makeRequest($url);

      if ($response) {
        $rates = $this -> extractCurrencyRatesFromResponse($response);

        if (!empty($rates)) {
          $this -> saveCurrencyRates($rates);
          return true;
        }
      }
    } catch (Exception $e) {
      error_log("An error occurred: " . $e -> getMessage());
    }

    return false;
  }

  private function makeRequest(string $url): string {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);

    if ($response === false) {
      throw new Exception(curl_error($curl));
    }

    curl_close($curl);
    return $response;
  }

  private function extractCurrencyRatesFromResponse(string $response): array {
    $data = json_decode($response, true);

    if (json_last_error() === JSON_ERROR_NONE && !empty($data[0]['rates'])) {
      return $data[0]['rates'];
    }

    throw new Exception("Invalid API response");
  }

  private function saveCurrencyRates(array $rates): void {
    try {
      $query = $this -> db -> 
        prepare("INSERT INTO currency_rates (currency_code, currency_name, exchange_rate) 
          VALUES (:currency_code, :currency_name, :exchange_rate) 
          ON DUPLICATE KEY UPDATE exchange_rate = VALUES(exchange_rate)");

      foreach ($rates as $rate) {
        $query -> execute([
          'currency_code' => $rate['code'],
          'currency_name' => $rate['currency'],
          'exchange_rate' => $rate['mid'],
        ]);
      }
    } catch (PDOException $e) {
      throw new Exception("Database error: " . $e -> getMessage());
    }
  }
}

?>