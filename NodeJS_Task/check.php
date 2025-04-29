<?php
$result = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hash = $_POST["hash"] ?? '';

    if ($hash) {
        $payload = json_encode(["hex" => $hash]);
        $ch = curl_init('http://localhost:3000/check-password-hash');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = match ($status) {
            204 => "✅ Hash is valid.",
            401 => "❌ Invalid hash.",
            400 => "⚠️ Missing or bad request.",
            500 => "🚫 Server error.",
            default => "❓ Unknown response ($status)."
        };
    } else {
        $result = "⚠️ Please enter a hash value.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Password Hash</title>
</head>
<body>
    <h1>Check Password Hash</h1>
    <form method="post">
        <input type="text" name="hash" placeholder="Enter SHA256 hash" required>
        <button type="submit">Check</button>
    </form>
    <?php if ($result): ?>
        <p><strong>Result:</strong> <?= htmlspecialchars($result) ?></p>
    <?php endif; ?>
</body>
</html>
