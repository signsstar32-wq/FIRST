<?php
$API_KEY = 'O5mTtC6sSxzsXNWf3x0mXFtbOUcvSZQm';

// Fetch Countries
$countryUrl = "https://api.sms-man.com/control/countries?token=${API_KEY}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $countryUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$countryResponse = curl_exec($ch);
curl_close($ch);

$countryData = json_decode($countryResponse, true);
$countries = isset($countryData['countries']) ? $countryData['countries'] : [];

// Fetch Service Types
$typeUrl = "https://api.sms-man.com/rent-api/types?token=${API_KEY}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $typeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$typeResponse = curl_exec($ch);
curl_close($ch);

$typeData = json_decode($typeResponse, true);
$types = isset($typeData['types']) ? $typeData['types'] : [];

// Fetch Time Options (static values as placeholder)
$timeOptions = [
    ["value" => "1_hour", "label" => "1 Hour"],
    ["value" => "6_hours", "label" => "6 Hours"],
    ["value" => "12_hours", "label" => "12 Hours"],
    ["value" => "24_hours", "label" => "1 Day"]
];

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phoneType = $_POST['phoneType'] ?? '';
    $country = $_POST['country'] ?? '';
    $duration = $_POST['duration'] ?? '';

    echo "<p style='color: lightgreen;'>✅ Form submitted successfully!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Number Application</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            width: 40%;
            margin: 50px auto;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 255, 255, 0.5);
        }
        h1 {
            color: cyan;
        }
        .form-group {
            text-align: left;
            margin: 10px 0;
        }
        label {
            font-size: 16px;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
        }
        select {
            background-color: #222;
            color: white;
        }
        button {
            background-color: cyan;
            color: black;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #00ffff;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Virtual Number Application</h1>
        <h2 style="color: white;">Apply for a Virtual Number</h2>

        <form method="POST">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Service Type:</label>
                <select name="phoneType" required>
                    <option value="">Loading...</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= htmlspecialchars($type['value']) ?>">
                            <?= htmlspecialchars($type['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Country:</label>
                <select name="country" required>
                    <option value="">Loading...</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country['code']) ?>">
                            <?= htmlspecialchars($country['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Time:</label>
                <select name="duration" required>
                    <option value="">Loading...</option>
                    <?php foreach ($timeOptions as $time): ?>
                        <option value="<?= htmlspecialchars($time['value']) ?>">
                            <?= htmlspecialchars($time['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Submit Application</button>
        </form>
    </div>

</body>
</html>
