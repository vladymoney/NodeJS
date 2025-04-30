<?php
$response = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inputHash = $_POST["hash"];

    $data = json_encode(["hex" => $inputHash]);

    $options = [
        "http" => [
            "header"  => "Content-type: application/json\r\n",
            "method"  => "POST",
            "content" => $data,
        ],
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents("http://localhost:3000/check-password-hash", false, $context);

    $httpCode = null;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('#HTTP/\d+\.\d+ (\d+)#', $header, $matches)) {
                $httpCode = intval($matches[1]);
                break;
            }
        }
    }

    if ($httpCode === 204) {
        $response = "✅ Hash matches!";
    } elseif ($httpCode === 401) {
        $response = "❌ Hash does not match.";
    } elseif ($httpCode === 400 || $httpCode === 500) {
        $response = "⚠️ Error: Invalid input or server problem.";
    } else {
        $response = "⚠️ Unexpected response.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Password Hash</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>SHA-256 Hash Verification</h1>
        <form method="POST">
            <label>Enter Hash:</label><br>
            <input type="text" name="hash" required><br><br>
            <button type="submit">Check</button>
        </form>

        <?php if ($response): ?>
            <p><strong>Result:</strong> <?= htmlspecialchars($response) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
