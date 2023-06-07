<?php

class RecentConversionsGenerator {
  private PDO $db;

  public function __construct(PDO $db) {
    $this -> db = $db;
  }

  public function generateRecentConversions(): string {
    try {
      $query = $this -> db -> query("SELECT amount, from_currency, to_currency, converted_amount 
        FROM currency_conversion ORDER BY created_at DESC LIMIT 5");
      $recentConversions = $query -> fetchAll(PDO::FETCH_ASSOC);

      if (!empty($recentConversions)) {
        $html = "<table>";
        $html .= "
          <tr>
            <th>Base Amount</th>
            <th>From</th>
            <th>To</th>
            <th>Converted amount</th>
          </tr>";

        foreach ($recentConversions as $recent) {
          $baseAmount = $recent["amount"];
          $fromCurrency = $recent["from_currency"];
          $toCurrency = $recent["to_currency"];
          $convertedAmount = $recent["converted_amount"];

          $html .= "
            <tr>
              <td>$baseAmount</td>
              <td>$fromCurrency</td>
              <td>$toCurrency</td>
              <td>$convertedAmount</td>
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