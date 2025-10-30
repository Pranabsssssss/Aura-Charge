<?php
session_start();


if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: /dash');
    exit();
}


$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $csvFile = __DIR__ . '/users.csv';
    if (!file_exists($csvFile)) {
        $loginError = 'User database not found!';
    } else {
        $file = fopen($csvFile, 'r');
        fgetcsv($file); 
        while (($data = fgetcsv($file)) !== false) {
            if (strcasecmp($data[1], $email) === 0 && $data[2] === $password) {
                $_SESSION['user'] = $data[1];
                fclose($file);
                header('Location: /dash');
                exit();
            }
        }
        fclose($file);
        $loginError = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aura Charge — Sign In</title>
    <link rel="icon" href="https://auraof.pranab.tech/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Orbitron:wght@600;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glow-text { background: linear-gradient(90deg, #00FFFF, #1E90FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .login-card { backdrop-filter: blur(16px); background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(56, 189, 248, 0.3); box-shadow: 0 0 30px rgba(0,191,255,0.1); border-radius: 1rem; transition: all 0.3s ease; }
        .login-card:hover { border-color: rgba(0,191,255,0.6); box-shadow: 0 0 35px rgba(0,191,255,0.3); transform: translateY(-3px); }
        .btn { background: linear-gradient(135deg, #00bfff, #00ffff); border: none; transition: all 0.3s ease; color: #000; font-weight: 600; border-radius: .5rem; }
        .btn:hover { transform: scale(1.03); box-shadow: 0 0 25px rgba(0,191,255,.4); }
        .input-box { background: rgba(255,255,255,0.08); border: 1px solid rgba(0,191,255,0.3); color: white; border-radius: .5rem; padding: .75rem; width: 100%; }
        .input-box:focus { outline: none; border-color: rgba(0,191,255,0.6); box-shadow: 0 0 10px rgba(0,191,255,0.3); }
        .error { color: #f87171; text-align: center; font-size: 0.9rem; margin-top: 1rem; }
    </style>
</head>
<body class="bg-gradient-to-b from-gray-950 via-gray-900 to-black min-h-screen flex items-center justify-center text-white">
    <div class="max-w-md w-full p-6">
        <div class="login-card p-8 text-center">
            <img src="https://auraof.pranab.tech/logo.png" alt="Aura Charge" class="w-20 h-20 mx-auto rounded-full border-2 border-cyan-400 shadow-md mb-6">
            <h1 class="text-3xl font-orbitron glow-text mb-2">Sign In</h1>
            <p class="text-slate-400 mb-6">Enter your credentials to access Aura Charge</p>

            <form method="POST" class="space-y-4 text-left">
                <label class="block text-slate-300">Email Address</label>
                <input type="email" name="email" required placeholder="smg@pranab.tech" class="input-box">
                
                <label class="block text-slate-300">Password</label>
                <input type="password" name="password" required placeholder="Your password" class="input-box">

                <button type="submit" class="btn w-full py-3 mt-4">Login</button>
            </form>

            <?php if (!empty($loginError)) : ?>
                <div class="error"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>

            <p class="text-xs text-gray-500 mt-6">Unauthorized access is prohibited. All login attempts are logged.</p>
        </div>
    </div>

</body>

</html>
