<?php
  require_once "config/database.php";
  require_once "NBPApiClient.php";
  require_once "CurrencyConverter.php";
  require_once "CurrencyTableGenerator.php";
  require_once "RecentConversionsGenerator.php";

  try {
    $dbHost = $configDbHost;
    $dbName = $configDbName;
    $dbUser = $configDbUser;
    $dbPass = $configDbPass;

    $db = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $apiClient = new NBPApiClient($db);

    $currencyConverter = new CurrencyConverter($apiClient, $db);
    $currencyTableGenerator = new CurrencyTableGenerator($db);
    $recentConversionsGenerator = new RecentConversionsGenerator($db);

    $currencyOptions = $currencyConverter -> getCurrencyOptions();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $amount = $_POST['amount'];
      $sourceCurrency = $_POST['source_currency'];
      $targetCurrency = $_POST['target_currency'];

      $convertedAmount = $currencyConverter -> convertCurrency(
        (float) $amount,
        (string) $sourceCurrency,
        (string) $targetCurrency
      );

      if ($convertedAmount !== false) {
          $currencyConverter -> saveCurrencyConversion(
            (float) $amount,
            (string) $sourceCurrency,
            (string) $targetCurrency,
            (float) $convertedAmount
          );
      }

      header("Location: index.php");
      exit();
  } else {
    $apiClient -> fetchAndSaveCurrencyRates();
  }

    $currencyTable = $currencyTableGenerator -> generateCurrencyTable();
    $recentConversions = $recentConversionsGenerator -> generateRecentConversions();

  } catch (PDOException $e) {
    echo "Dababase connection failed: " . $e -> getMessage();
  }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Currency Converter</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>

    </style>
  </head>

  <body>
    <div class="container">
      <div class="calculator">
        <form method="POST" action="index.php">
          <div class="form-row">
            <label for="amount">Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount" required>
          </div>
          <div class="form-row">
            <label for="source_currency">Source Currency:</label>
            <select name="source_currency" id="source_currency" required>
              <?php foreach ($currencyOptions as $option) : ?>
                <option value="<?php echo $option['currency_code']; ?>">
                <?php echo $option['currency_code']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <label for="target_currency">Target Currency:</label>
            <select name="target_currency" id="target_currency" required>
              <?php foreach ($currencyOptions as $option) : ?>
                <option value="<?php echo $option['currency_code']; ?>">
                <?php echo $option['currency_code']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <button type="submit">Convert</button>
          </div>
        </form>
      </div>

      <div class="flex-container">

        <div class="table flex-item">
          <h2>Currency Table</h2>
          <?php echo $currencyTable; ?>
        </div>

        <div class="table flex-item">
          <h2>Recent Currency Conversions</h2>
          <?php echo $recentConversions; ?>
        </div>

      </div>
    </div>
  </body>
</html>