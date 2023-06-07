<?php

class CurrencyTableGenerator {
  private PDO $db;

  public function __construct(PDO $db) {
    $this -> db = $db;
  }

  public function generateCurrencyTable(): string {
    try {
      $query = $this -> db -> query("SELECT currency_code, currency_name, exchange_rate
        FROM currency_rates");
      $currencyRates = $query -> fetchAll(PDO::FETCH_ASSOC);

      if (!empty($currencyRates)) {
        $html = "<table>";
        $html .= "
          <tr>
            <th>Currency Code</th>
            <th>Currency Name</th>
            <th>Exchange Rate</th>
          </tr>";

        foreach ($currencyRates as $rate) {
          $currencyCode = $rate["currency_code"];
          $currencyName = $rate["currency_name"];
          $exchangeRate = $rate["exchange_rate"];

          $html .= "
            <tr>
              <td>$currencyCode</td>
              <td>$currencyName</td>
              <td>$exchangeRate</td>
            </tr>";
        }

        $html .= "</table>";

        return $html;
      }

      return "";
    } catch (PDOException $e) {
      error_log("Database error: " . $e -> getMessage());
      return "";
    }
  }
}

?>