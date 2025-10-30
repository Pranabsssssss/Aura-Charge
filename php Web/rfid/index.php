<?php
date_default_timezone_set('Asia/Kolkata');

if (!isset($_GET['rfidkey'])) {
    die('rfidkey parameter missing');
}
$rfidkeyParam = $_GET['rfidkey'];

$dashPos = strrpos($rfidkeyParam, '-');
if ($dashPos === false) {
    die('Invalid rfidkey format');
}
$rfidkey = substr($rfidkeyParam, 0, $dashPos);
$stationName = substr($rfidkeyParam, $dashPos + 1);

$csvFile = __DIR__ . '/../signin/users.csv';

$rows = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    while (($data = fgetcsv($handle)) !== false) {
        $rows[] = $data;
    }
    fclose($handle);
} else {
    die('Failed to open CSV file');
}

if (count($rows) < 1) {
    die('CSV file is empty');
}

$rfidCol = 4;
$sessionsStartCol = 5;

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

$found = false;
for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (count($row) <= $rfidCol) continue;

    if ($row[$rfidCol] === $rfidkey) {
        $found = true;
        $sessions = array_slice($row, $sessionsStartCol);
        $sessions = array_map('trim', $sessions);
        $lastSessionIndex = array_key_last($sessions);
        $lastSession = $lastSessionIndex !== null ? $sessions[$lastSessionIndex] : null;

        if ($lastSession) {
            $parts = preg_split('/\s+/', $lastSession);
            if (count($parts) === 4) {
                list($sStation, $sDate, $sStart, $sEnd) = $parts;

                if ($sDate === $currentDate) {
                    if ($sStart === $sEnd) {
                        $sessions[$lastSessionIndex] = "$sStation $sDate $sStart $currentTime";
                    } else {
                        $sessions[] = "$stationName $currentDate $currentTime $currentTime";
                    }
                } else {
                    $sessions[$lastSessionIndex] = "$sStation $sDate $sStart 23:59:59";
                    $sessions[] = "$stationName $currentDate 00:00:00 $currentTime";
                }
            } else {
                $sessions[] = "$stationName $currentDate $currentTime $currentTime";
            }
        } else {
            $sessions[] = "$stationName $currentDate $currentTime $currentTime";
        }

        $row = array_slice($row, 0, $sessionsStartCol);
        foreach ($sessions as $session) {
            $session = str_replace(',', '', $session);
            $row[] = $session;
        }
        $rows[$i] = $row;
        break;
    }
}

if (!$found) {
    die('rfidkey not found in users.csv');
}

$tempFile = $csvFile . '.tmp';
$fileHandle = fopen($tempFile, 'w');
if (!$fileHandle) {
    die('Failed to open temp file for writing');
}

foreach ($rows as $r) {
    $escaped = array_map(function ($field) {
        return str_replace('"', '""', $field);
    }, $r);
    fwrite($fileHandle, implode(',', $escaped) . "\n");
}
fclose($fileHandle);

rename($tempFile, $csvFile);

echo "Session updated successfully";
?>
