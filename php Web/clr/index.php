<?php
// Paths relative to this clr/index.php file
$rfidFile = __DIR__ . '/../signin/users.csv';
$clrFile = __DIR__ . '/users.csv';

// Delete attendance.csv in rfid folder if it exists
if (file_exists($rfidFile)) {
    unlink($rfidFile);
}

// Copy attendance.csv from clr folder to rfid folder
if (file_exists($clrFile)) {
    if (copy($clrFile, $rfidFile)) {
        echo "Session file successfully updated.";
    } else {
        echo "Failed to copy Session file.";
    }
} else {
    echo "Session file not found in clr folder.";
}
?>
