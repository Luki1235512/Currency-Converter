<?php

class CurrencyConverter {
  private NBPApiClient $apiClient;
  private PDO $db;

  public function __construct(NBPApiClient $apiClient, PDO $db) {
    $this -> apiClient = $apiClient;
    $this -> db = $db;
  }

  public function getCurrencyOptions(): array {
    try {
      $query = $this -> db -> query("SELECT DISTINCT currency_code
        FROM currency_rates");
      $currencyOptions = $query -> fetchAll(PDO::FETCH_ASSOC);
      return $currencyOptions;
    } catch (PDOException $e) {
      error_log("Database error: " . $e -> getMessage());
      return [];
    }
  }

  public function getExchangeRate(string $currencyCode): ?float {
    try {
      $query = $this -> db -> prepare("SELECT exchange_rate
        FROM currency_rates WHERE currency_code = :currency_code");
      $query -> execute(["currency_code" => $currencyCode]);
      $result = $query -> fetch(PDO::FETCH_ASSOC);

      if ($result) {
        return (float) $result["exchange_rate"];
      }

      return null;
    } catch (PDOException $e) {
      error_log("Database error: " . $e -> getMessage());
      return null;
    }
  }

  public function convertCurrency(
    float $amount,
    string $sourceCurrency,
    string $targetCurrency): ?float {
      try {
        $sourceRate = $this -> getExchangeRate($sourceCurrency);
        $targetRate = $this -> getExchangeRate($targetCurrency);

        if ($sourceRate && $targetRate) {
          $convertedAmount = $amount * ($sourceRate / $targetRate);
          return $convertedAmount;
        }

        return null;
      } catch (PDOException $e) {
        error_log("Conversion error: " . $e -> getMessage());
        return null;
      }
  }

  public function saveCurrencyConversion(
    float $amount,
    string $sourceCurrency,
    string $targetCurrency,
    float $convertedAmount): bool {
      try {
        $query = $this -> db -> 
          prepare("INSERT INTO currency_conversion (amount, from_currency, to_currency, converted_amount)
            VALUES (:amount, :from_currency, :to_currency, :converted_amount)");
              
        $query -> execute([
          'amount' => $amount,
          'from_currency' => $sourceCurrency,
          'to_currency' => $targetCurrency,
          'converted_amount' => $convertedAmount
        ]);

        return true;
      } catch (PDOException $e) {
        error_log("Database error: " . $e  -> getMessage());
        return false;
      }
  }
}

?>