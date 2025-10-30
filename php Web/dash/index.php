<?php
session_start();


error_reporting(E_ALL);
ini_set('display_errors', 1);


if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    ?>
    <script>

        localStorage.clear();
        sessionStorage.clear();

        document.cookie.split(";").forEach(function (c) {
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });
        window.location.href = "/signin";
    </script>
    <?php
    exit();
}

$userEmail = $_SESSION['user'];
$userName = '';
$userPhone = '';
$userPicture = '';
$userSerialNo = '';
$carName = '';
$carImage = '';
$carNumber = '';
$userSessions = array();


function parseSessionData($cellData, $sessionCounter, &$userSessions)
{

    $cleanData = str_replace(['(', ')', '"', "'"], '', $cellData);
    $cleanData = preg_replace('/\s+/', ' ', trim($cleanData));

    error_log("Parsing: " . $cleanData);


    if (preg_match('/^(.+?)\s+(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})\s+(\d{1,2}:\d{2}(?::\d{2})?)\s+(\d{1,2}:\d{2}(?::\d{2})?)/', $cleanData, $matches)) {
        $stationName = trim($matches[1]);
        $date = trim($matches[2]);
        $startTime = trim($matches[3]);
        $endTime = trim($matches[4]);

        error_log("Pattern 1 matched: Station={$stationName}, Date={$date}, Start={$startTime}, End={$endTime}");

        return addSessionToArray($stationName, $date, $startTime, $endTime, $sessionCounter, $userSessions);
    }


    if (preg_match('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $cleanData, $dateMatch)) {
        $parts = preg_split('/\s+/', $cleanData);
        $dateIndex = -1;


        for ($i = 0; $i < count($parts); $i++) {
            if (preg_match('/\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}/', $parts[$i])) {
                $dateIndex = $i;
                break;
            }
        }

        if ($dateIndex >= 0 && count($parts) >= $dateIndex + 3) {
            $stationName = implode(' ', array_slice($parts, 0, $dateIndex));
            $date = $parts[$dateIndex];
            $startTime = isset($parts[$dateIndex + 1]) ? $parts[$dateIndex + 1] : '';
            $endTime = isset($parts[$dateIndex + 2]) ? $parts[$dateIndex + 2] : '';

            if (!empty($stationName) && !empty($startTime) && !empty($endTime)) {
                error_log("Pattern 2 matched: Station={$stationName}, Date={$date}, Start={$startTime}, End={$endTime}");
                return addSessionToArray($stationName, $date, $startTime, $endTime, $sessionCounter, $userSessions);
            }
        }
    }


    if (strpos($cleanData, ',') !== false || strpos($cleanData, ';') !== false) {
        $separator = strpos($cleanData, ',') !== false ? ',' : ';';
        $parts = array_map('trim', explode($separator, $cleanData));

        if (count($parts) >= 4) {
            $stationName = $parts[0];
            $date = $parts[1];
            $startTime = $parts[2];
            $endTime = $parts[3];


            if (
                preg_match('/\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}/', $date) &&
                preg_match('/\d{1,2}:\d{2}/', $startTime) &&
                preg_match('/\d{1,2}:\d{2}/', $endTime)
            ) {

                error_log("Pattern 3 matched: Station={$stationName}, Date={$date}, Start={$startTime}, End={$endTime}");
                return addSessionToArray($stationName, $date, $startTime, $endTime, $sessionCounter, $userSessions);
            }
        }
    }

    return false;
}

function addSessionToArray($stationName, $date, $startTime, $endTime, $sessionCounter, &$userSessions)
{

    if (empty($stationName) || empty($date) || empty($startTime) || empty($endTime)) {
        return false;
    }


    $date = str_replace('/', '-', $date);
    if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{2,4})/', $date, $dateMatches)) {
        $day = str_pad($dateMatches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($dateMatches[2], 2, '0', STR_PAD_LEFT);
        $year = strlen($dateMatches[3]) == 2 ? '20' . $dateMatches[3] : $dateMatches[3];
        $formattedDate = $day . '/' . $month . '/' . $year;
    } else {
        $formattedDate = $date;
    }


    $duration = 'N/A';
    try {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $interval = $start->diff($end);

        $hours = $interval->h;
        $minutes = $interval->i;

        if ($hours > 0) {
            $duration = $hours . 'h ' . $minutes . 'm';
        } else {
            $duration = $minutes . 'm';
        }
    } catch (Exception $e) {
        error_log("Duration calculation failed: " . $e->getMessage());
    }

    $userSessions[] = array(
        'sno' => $sessionCounter,
        'station' => $stationName,
        'date' => $formattedDate,
        'duration' => $duration,
        'startTime' => $startTime,
        'endTime' => $endTime
    );

    error_log("Successfully added session: {$stationName} on {$formattedDate}");
    return true;
}


$usersUrl = 'https://auraof.pranab.tech/signin/users.csv';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $usersUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$usersContent = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && $usersContent !== false) {
    $usersContent = str_replace(["\r\n", "\r"], "\n", $usersContent);
    $lines = explode("\n", $usersContent);

    error_log("Total lines in users.csv: " . count($lines));


    if (count($lines) > 0) {
        $headerData = str_getcsv(trim($lines[0]));
        error_log("Users.csv header: " . print_r($headerData, true));
    }


    $userColumnIndex = -1;
    foreach ($lines as $lineIndex => $line) {
        if ($lineIndex == 0)
            continue;

        $line = trim($line);
        if (empty($line))
            continue;

        $data = str_getcsv($line);
        $data = array_map('trim', $data);


        if (isset($data[1]) && strcasecmp($data[1], $userEmail) === 0) {
            $userSerialNo = isset($data[0]) ? $data[0] : '';
            $userName = isset($data[3]) ? $data[3] : '';
            $userPhone = isset($data[2]) ? $data[2] : '';
            $userColumnIndex = $lineIndex;

            error_log("Found user {$userEmail} at row index {$userColumnIndex}");
            error_log("User details - Serial: {$userSerialNo}, Name: {$userName}, Phone: {$userPhone}");
            break;
        }
    }


    if ($userColumnIndex >= 0) {
        error_log("User found at row {$userColumnIndex}. Extracting sessions for this specific user only...");

        $sessionCounter = 1;
        $userSerialNumber = $userSerialNo;

        error_log("Looking for sessions for user with Serial No: {$userSerialNumber}");


        $userRowLine = trim($lines[$userColumnIndex]);
        if (!empty($userRowLine)) {
            $userRowData = str_getcsv($userRowLine);
            $userRowData = array_map('trim', $userRowData);

            error_log("User row data: " . print_r($userRowData, true));
            error_log("Total columns in user row: " . count($userRowData));


            for ($columnIndex = 5; $columnIndex < count($userRowData); $columnIndex++) {
                if (isset($userRowData[$columnIndex]) && !empty(trim($userRowData[$columnIndex]))) {
                    $sessionsData = trim($userRowData[$columnIndex]);
                    error_log("Found sessions data in column " . ($columnIndex + 1) . " for user {$userSerialNumber}: " . $sessionsData);

                    if (parseSessionData($sessionsData, $sessionCounter, $userSessions)) {
                        $sessionCounter++;
                    }
                }
            }
        }

    } else {
        error_log("User not found in users.csv: " . $userEmail);


        $sampleSessions = [
            ['station' => 'Demo Station A', 'date' => '15/12/2024', 'start' => '10:00', 'end' => '11:30', 'duration' => '1h 30m'],
            ['station' => 'Demo Station B', 'date' => '12/12/2024', 'start' => '15:00', 'end' => '16:15', 'duration' => '1h 15m']
        ];

        foreach ($sampleSessions as $index => $session) {
            $userSessions[] = array(
                'sno' => $index + 1,
                'station' => $session['station'],
                'date' => $session['date'],
                'duration' => $session['duration'],
                'startTime' => $session['start'],
                'endTime' => $session['end']
            );
        }
    }

} else {
    error_log("Failed to fetch users.csv. HTTP Code: " . $httpCode);
}

if (empty($userName)) {
    $userName = explode('@', $userEmail)[0];
    $userName = ucfirst($userName);
}

$userPicture = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=00bfff&color=fff&size=128&bold=true";

if (!empty($userSerialNo)) {
    $dataUrl = 'https://auraof.pranab.tech/dash/data.csv';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $dataUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $dataContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $dataContent !== false) {

        $dataContent = str_replace(["\r\n", "\r"], "\n", $dataContent);
        $lines = explode("\n", $dataContent);

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex == 0)
                continue;

            $line = trim($line);
            if (empty($line))
                continue;


            $data = str_getcsv($line);


            $data = array_map(function ($field) {
                return trim($field);
            }, $data);


            if (isset($data[0]) && $data[0] === $userSerialNo) {
                $carName = isset($data[1]) ? $data[1] : '';
                $carImage = isset($data[2]) ? $data[2] : '';
                $carNumber = isset($data[3]) ? $data[3] : '';
                break;
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aura Charge</title>
    <link rel="icon" href="https://auraof.pranab.tech/logo.png" type="image/png">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@400;700;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <style>
        body {
            box-sizing: border-box;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 10px;
            background: #101820;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #00cfff 30%, #015eab 70%);
            border-radius: 8px;
            box-shadow: 0 0 10px #00cfff, 0 0 5px #0197f6;
            border: 2px solid #16232f;
            transition: background 0.3s;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #0ff 20%, #017aff 80%);
            box-shadow: 0 0 14px #0ff;
        }

        ::-webkit-scrollbar-track {
            background: #0d1622;
            border-radius: 10px;
            box-shadow: inset 0 0 5px #015eab;
        }

        scrollbar-color: #00cfff #0d1622;
        scrollbar-width: thin;


        .gradient-text {
            background: linear-gradient(135deg, #00bfff, #1e90ff, #00ffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dashboard-bg {
            background: radial-gradient(ellipse at center, rgba(0, 191, 255, 0.05) 0%, rgba(0, 0, 0, 0.9) 70%),
                linear-gradient(135deg, rgba(0, 0, 0, 0.95) 0%, rgba(0, 20, 40, 0.95) 100%);
        }


        .main-header {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            z-index: 50;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(16px);
            border: 2px solid rgba(107, 114, 128, 0.7);
            border-radius: 1rem;
            width: calc(100% - 2rem);
            max-width: 70%;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            opacity: 1;
        }

        @media (max-width: 768px) {
            .main-header {
                max-width: 95%;
                top: 0.5rem;
                border-radius: 0.75rem;
            }
        }

        .main-header:hover {
            border-color: rgba(0, 191, 255, 0.5);
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 30px rgba(0, 191, 255, 0.2);
        }


        .logo-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
        }

        .logo-circle:hover {
            border-color: rgba(0, 191, 255, 0.8);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.4);
        }


        .profile-container {
            display: flex;
            align-items: center;
            space-x: 12px;
            padding: 8px 16px;
            background: rgba(0, 191, 255, 0.1);
            border: 1px solid rgba(0, 191, 255, 0.3);
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-container:hover {
            background: rgba(0, 191, 255, 0.15);
            border-color: rgba(0, 191, 255, 0.5);
            box-shadow: 0 0 20px rgba(0, 191, 255, 0.2);
        }

        .profile-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 191, 255, 0.5);
        }


        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #00bfff, #1e90ff);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: #00bfff;
            text-shadow: 0 0 10px rgba(0, 191, 255, 0.5);
        }

        .dashboard-card {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 191, 255, 0.2);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            border-color: rgba(0, 191, 255, 0.4);
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.1), 0 0 30px rgba(0, 191, 255, 0.2);
            transform: translateY(-5px);
        }


        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 191, 255, 0.3);
            border-top: 3px solid #00bfff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }


        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }

        .delay-800 {
            animation-delay: 0.8s;
        }


        .btn-primary {
            background: linear-gradient(135deg, rgba(0, 191, 255, 0.9) 0%, rgba(30, 144, 255, 0.9) 100%);
            border: 2px solid rgba(0, 191, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, rgba(0, 191, 255, 1) 0%, rgba(30, 144, 255, 1) 100%);
            border-color: rgba(0, 191, 255, 0.8);
            box-shadow: 0 15px 35px rgba(0, 191, 255, 0.4), 0 0 30px rgba(0, 191, 255, 0.3);
            transform: translateY(-3px) scale(1.02);
        }


        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }


        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }


        @media (max-width: 768px) {
            .logo-circle {
                width: 40px;
                height: 40px;
            }

            .profile-picture {
                width: 32px;
                height: 32px;
            }

            .profile-container {
                padding: 6px 12px;
            }

            .dashboard-card {
                margin: 0 1rem;
                padding: 1.5rem;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-responsive table {
                min-width: 600px;
            }

            .mobile-stack {
                display: block !important;
            }

            .mobile-stack>div {
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 640px) {
            .main-header nav {
                padding: 0.75rem 1rem;
            }

            .dashboard-card {
                margin: 0 0.5rem;
                padding: 1rem;
            }

            .grid-cols-1.md\\:grid-cols-3 {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>

<body class="bg-black text-white font-inter">



    <header class="main-header">
        <nav class="flex items-center justify-between px-6 py-4">

            <div class="flex items-center space-x-3">
                <img src="https://auraof.pranab.tech/logo.png" alt="Aura Charge Logo" class="logo-circle">
                <div>
                    <h1 class="text-xl font-orbitron font-bold gradient-text">Aura Charge</h1>
                </div>
            </div>


            <div id="userProfile" class="profile-container">
                <img id="profilePicture" src="<?php echo htmlspecialchars($userPicture); ?>" alt="Profile"
                    class="profile-picture">
                <div class="flex flex-col">
                    <span id="userName"
                        class="text-sm font-semibold text-white truncate max-w-24 sm:max-w-none"><?php echo htmlspecialchars($userName); ?></span>
                    <span id="userEmail"
                        class="text-xs text-gray-400 truncate max-w-24 sm:max-w-none"><?php echo htmlspecialchars($userEmail); ?></span>
                </div>
                <svg class="w-4 h-4 text-gray-400 ml-2 hidden sm:block" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </nav>
    </header>


    <main class="dashboard-bg min-h-full pt-32 pb-20">
        <div class="container mx-auto px-6
            <div class=" max-w-4xl mx-auto">
            <?php if (!empty($carName) || !empty($carNumber) || !empty($carImage)): ?>

                <div id="carDetails" class="dashboard-card rounded-2xl p-8 fade-in-up">

                    <div class="text-center mb-8">
                        <div id="carImageContainer" class="relative inline-block">
                            <?php if (!empty($carImage)): ?>
                                <img id="carImage" src="<?php echo htmlspecialchars($carImage); ?>" alt="Car Image"
                                    class="max-w-full sm:max-w-80 max-h-48 sm:max-h-60 object-contain rounded-xl border-2 border-cyan-400/30 shadow-lg"
                                    onerror="this.style.display='none'; document.getElementById('carImagePlaceholder').style.display='flex';">
                            <?php endif; ?>
                            <div id="carImagePlaceholder"
                                class="w-full sm:w-80 h-48 sm:h-60 max-w-80 bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl border-2 border-cyan-400/30 flex items-center justify-center mx-auto"
                                <?php echo !empty($carImage) ? 'style="display: none;"' : ''; ?>>
                                <svg class="w-16 sm:w-20 h-16 sm:h-20 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div
                            class="text-center p-6 bg-gradient-to-br from-cyan-400/10 to-blue-500/10 rounded-xl border border-cyan-400/20">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-400 mb-2">Car Model</h3>
                            <p id="carName" class="text-2xl font-orbitron font-bold gradient-text">
                                <?php echo !empty($carName) ? htmlspecialchars($carName) : 'Not Available'; ?>
                            </p>
                        </div>


                        <div
                            class="text-center p-6 bg-gradient-to-br from-green-400/10 to-emerald-500/10 rounded-xl border border-green-400/20">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-400 mb-2">License Plate</h3>
                            <p id="carNumber" class="text-2xl font-orbitron font-bold gradient-text">
                                <?php echo !empty($carNumber) ? htmlspecialchars($carNumber) : 'Not Available'; ?>
                            </p>
                        </div>


                        <div
                            class="text-center p-6 bg-gradient-to-br from-purple-400/10 to-pink-500/10 rounded-xl border border-purple-400/20">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-400 mb-2">Owner</h3>
                            <p id="ownerName" class="text-2xl font-orbitron font-bold gradient-text">
                                <?php echo htmlspecialchars($userName); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>

                <div id="errorMessage" class="error-message rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    <p class="text-lg font-semibold mb-2">No vehicle data found</p>
                    <p class="text-sm">Your account (Serial: <?php echo htmlspecialchars($userSerialNo); ?>) is not
                        linked to any vehicle. Please contact support.</p>
                </div>
            <?php endif; ?>


            <div class="dashboard-card rounded-2xl p-6 sm:p-8 mt-8 fade-in-up delay-200">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 space-y-4 sm:space-y-0">
                    <div>
                        <h2 class="text-2xl font-orbitron font-bold gradient-text">Charging Sessions</h2>
                        <p class="text-sm text-gray-400 mt-1">Track your charging history and activity</p>
                    </div>
                    <div class="flex items-center space-x-2 text-gray-400 bg-gray-800/50 px-3 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-xs font-medium">Recent Activity</span>
                    </div>
                </div>

                <?php if (!empty($userSessions)): ?>

                    <div class="mb-4">
                        <span class="text-sm text-gray-400">Total Sessions: </span>
                        <span class="text-sm font-semibold text-cyan-400"><?php echo count($userSessions); ?></span>
                    </div>


                    <div class="overflow-hidden rounded-lg border border-gray-700/50">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">

                                <thead class="bg-gray-800/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700">
                                            #
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700 min-w-[200px]">
                                            Station Details
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700 min-w-[120px]">
                                            Date
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700 min-w-[100px]">
                                            Duration
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700 min-w-[100px]">
                                            Start Time
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-semibold text-gray-300 uppercase tracking-wider border-b border-gray-700 min-w-[100px]">
                                            End Time
                                        </th>
                                    </tr>
                                </thead>


                                <tbody class="bg-gray-900/20 divide-y divide-gray-700/50">
                                    <?php foreach ($userSessions as $index => $session): ?>
                                        <tr class="hover:bg-gray-800/30 transition-all duration-200 group">

                                            <td class="px-4 py-4">
                                                <div
                                                    class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-full border border-cyan-500/30">
                                                    <span
                                                        class="text-sm font-bold text-cyan-300"><?php echo htmlspecialchars($session['sno']); ?></span>
                                                </div>
                                            </td>


                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center shadow-lg">
                                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                                </path>
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p
                                                            class="text-sm font-semibold text-white group-hover:text-cyan-300 transition-colors">
                                                            <?php echo htmlspecialchars($session['station']); ?>
                                                        </p>
                                                        <p class="text-xs text-gray-400">Charging Station</p>
                                                    </div>
                                                </div>
                                            </td>


                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-300 font-medium">
                                                    <?php echo htmlspecialchars($session['date']); ?>
                                                </div>
                                            </td>


                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-cyan-400/20 to-blue-500/20 border border-cyan-400/30 text-cyan-300">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <?php echo htmlspecialchars($session['duration']); ?>
                                                </span>
                                            </td>


                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-300 font-mono">
                                                    <?php echo htmlspecialchars($session['startTime']); ?>
                                                </div>
                                            </td>


                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-300 font-mono">
                                                    <?php echo htmlspecialchars($session['endTime']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="text-center py-16">
                        <div
                            class="mx-auto w-24 h-24 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="max-w-md mx-auto">
                            <h3 class="text-xl font-semibold text-gray-300 mb-2">No Charging Sessions Yet</h3>
                            <p class="text-gray-500 mb-6">Your charging history will appear here once you start using
                                our charging stations.</p>
                            <div
                                class="bg-gradient-to-r from-cyan-400/10 to-blue-500/10 border border-cyan-400/20 rounded-lg p-4">
                                <p class="text-sm text-cyan-300">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Find nearby charging stations and start your first session to see data here.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </main>


    <footer class="relative bg-black border-t border-cyan-400/20 overflow-hidden">

        <div class="absolute inset-0 bg-gradient-to-t from-black via-gray-900/50 to-transparent"></div>
        <div class="absolute inset-0 opacity-30">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan-400/10 rounded-full blur-3xl animate-pulse"></div>
            <div
                class="absolute bottom-0 right-1/4 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse delay-1000">
            </div>
        </div>


        <div class="absolute inset-0 opacity-20"
            style="background-image: linear-gradient(rgba(0,191,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(0,191,255,0.1) 1px, transparent 1px); background-size: 50px 50px;">
        </div>

        <div class="relative container mx-auto px-6 py-16">

            <div class="flex justify-center mb-12">

                <div class="text-center max-w-2xl">
                    <div class="flex items-center justify-center space-x-4 mb-6">
                        <div class="relative">
                            <img src="https://auraof.pranab.tech/logo.png" alt="Aura Charge"
                                class="w-16 h-16 rounded-full border-2 border-cyan-400/50 shadow-lg"
                                onerror="this.src=''; this.alt='Logo failed to load'; this.style.display='none';">
                            <div class="absolute inset-0 rounded-full bg-cyan-400/20 animate-ping"></div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold gradient-text"
                                style="font-family: 'Orbitron', monospace; background: linear-gradient(135deg, #00bfff, #1e90ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                Aura Charge</h3>
                            <p class="text-gray-400 text-sm">Powering the Future</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-6 leading-relaxed">
                        Experience the next generation of electric vehicle charging with our advanced network of smart
                        charging stations. Clean energy, intelligent technology, seamless experience.
                    </p>



                </div>
            </div>


            <div class="border-t border-gray-800 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-6">
                        <div class="flex flex-col items-center md:items-start space-y-1">
                            <a href="https://github.com/pranabsssssss" target="_blank" rel="noopener noreferrer"
                                class="text-cyan-400 hover:text-cyan-300 transition-colors font-medium">
                                <p class="text-gray-400 text-sm">
                                    © 2024 Aura Charge. All rights reserved.
                                </p>
                                <p class="text-gray-500 text-xs">
                                    Website Developed by

                                    Pranab Saini
                            </a>
                            </p>
                        </div>
                    </div>


                    <button onclick="scrollToTop()"
                        class="group relative w-12 h-12 bg-gradient-to-br from-cyan-400/20 to-blue-500/20 border border-cyan-400/30 rounded-xl flex items-center justify-center hover:border-cyan-400/60 transition-all duration-300 hover:scale-110">
                        <svg class="w-5 h-5 text-cyan-400 group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        <div
                            class="absolute inset-0 rounded-xl bg-gradient-to-br from-cyan-400/0 to-blue-500/0 group-hover:from-cyan-400/20 group-hover:to-blue-500/20 transition-all duration-300">
                        </div>
                    </button>
                </div>
            </div>
        </div>


        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-cyan-400/30 rounded-full animate-bounce delay-0"></div>
            <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-blue-400/40 rounded-full animate-bounce delay-500"></div>
            <div class="absolute bottom-1/4 left-1/3 w-1.5 h-1.5 bg-cyan-300/20 rounded-full animate-bounce delay-1000">
            </div>
            <div class="absolute bottom-1/3 right-1/4 w-1 h-1 bg-blue-300/30 rounded-full animate-bounce delay-1500">
            </div>
        </div>

        <script>
            function scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        </script>
    </footer>


    <script>

        const lenis = new Lenis();


        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }

        requestAnimationFrame(raf);

        document.addEventListener('click', function (e) {
            if (e.target.closest('#userProfile')) {

                const existingDropdown = document.querySelector('.profile-dropdown');
                if (existingDropdown) {
                    existingDropdown.remove();
                    return;
                }

                const dropdown = document.createElement('div');
                dropdown.className = 'profile-dropdown absolute top-full right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-lg z-50';
                dropdown.innerHTML = `
                    <div class="py-2">
                        <button onclick="logout()" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-700">Sign Out</button>
                    </div>
                `;

                const profileContainer = document.getElementById('userProfile');
                profileContainer.style.position = 'relative';
                profileContainer.appendChild(dropdown);
            } else {

                const dropdown = document.querySelector('.profile-dropdown');
                if (dropdown) {
                    dropdown.remove();
                }
            }
        });


        function logout() {

            localStorage.clear();
            sessionStorage.clear();


            document.cookie.split(";").forEach(function (c) {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });


            window.location.href = "/signin";
        }
        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }


        window.addEventListener('scroll', () => {
            const header = document.querySelector('.main-header');
            const currentScrollY = window.scrollY;

            if (currentScrollY > 100) {
                header.style.background = 'rgba(0, 0, 0, 0.95)';
                header.style.backdropFilter = 'blur(20px)';
            } else {
                header.style.background = 'rgba(0, 0, 0, 0.9)';
                header.style.backdropFilter = 'blur(16px)';
            }
        });


    </script>
</body>

</html>