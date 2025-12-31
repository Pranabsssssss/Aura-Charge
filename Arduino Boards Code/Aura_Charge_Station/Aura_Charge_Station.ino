#include <Adafruit_Fingerprint.h>
#include <SoftwareSerial.h>

// SoftwareSerial pins for NodeMCU D5 (TX), D6 (RX)
SoftwareSerial mySerial(D5, D6);

Adafruit_Fingerprint finger(&mySerial);

const int maxFingerprints = 5;
int enrolledCount = 0;

void setup() {
  Serial.begin(9600);
  while (!Serial);
  delay(100);
  Serial.println("\nFingerprint Sensor Auto Enrollment with Duplicate Check");

  mySerial.begin(57600);
  finger.begin(57600);

  if (!finger.verifyPassword()) {
    Serial.println("Fingerprint sensor not found!");
    while (1) delay(1);
  }
  Serial.println("Sensor found!");

  finger.getTemplateCount();
  enrolledCount = finger.templateCount;
  Serial.print("Currently stored fingerprints: ");
  Serial.println(enrolledCount);

  if (enrolledCount < maxFingerprints) {
    Serial.print("Enroll new fingerprints up to ID ");
    Serial.println(maxFingerprints);
  } else {
    Serial.println("Fingerprint database full. Starting identification.");
  }
}

void loop() {
  if (enrolledCount < maxFingerprints) {
    Serial.print("Place finger to enroll ID ");
    Serial.println(enrolledCount + 1);
    if (enrollFingerprintNoDuplicates(enrolledCount + 1) == FINGERPRINT_OK) {
      Serial.println("Fingerprint enrolled successfully!\n");
      enrolledCount++;
    } else {
      Serial.println("Enrollment failed or duplicate detected, try again.\n");
    }
  } else {
    Serial.println("Place finger to identify...");
    int id = getFingerprintID();
    if (id != 0) {
      Serial.print("Fingerprint matched! ID: ");
      Serial.println(id);
    } else {
      Serial.println("No match found.");
    }
    delay(2000); // Pause before next scan
  }
}

uint8_t enrollFingerprintNoDuplicates(uint8_t id) {
  int p;

  Serial.println("Waiting to detect finger...");

  // Capture first image
  while ((p = finger.getImage()) != FINGERPRINT_OK) {
    if (p == FINGERPRINT_NOFINGER) delay(100);
    else {
      Serial.println("Error: Unable to capture image.");
      return p;
    }
  }
  Serial.println("Image taken");

  // Convert image to characteristics (slot #1)
  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) {
    Serial.println("Error: Unable to convert image.");
    return p;
  }

  // Perform search to detect duplicate fingerprint
  p = finger.fingerFastSearch();
  if (p == FINGERPRINT_OK) {
    Serial.print("Duplicate detected! This fingerprint is already enrolled with ID: ");
    Serial.println(finger.fingerID);
    return p; // Abort duplicate enrollment
  }

  Serial.println("No duplicate found. Proceeding with enrollment...");
  delay(1000);

  // Capture image again for enrollment (slot #1)
  while ((p = finger.getImage()) != FINGERPRINT_OK) {
    if (p == FINGERPRINT_NOFINGER) delay(100);
    else {
      Serial.println("Error: Unable to capture image for enrollment.");
      return p;
    }
  }
  Serial.println("Image taken");
  p = finger.image2Tz(1);
  if (p != FINGERPRINT_OK) {
    Serial.println("Error: Unable to convert image for enrollment.");
    return p;
  }

  Serial.println("Remove finger");
  delay(2000);

  Serial.println("Place the same finger again");
  // Capture second image (slot #2)
  while ((p = finger.getImage()) != FINGERPRINT_OK) {
    if (p == FINGERPRINT_NOFINGER) delay(100);
    else {
      Serial.println("Error: Unable to capture second image.");
      return p;
    }
  }
  Serial.println("Image taken");
  p = finger.image2Tz(2);
  if (p != FINGERPRINT_OK) {
    Serial.println("Error: Unable to convert second image.");
    return p;
  }

  // Create fingerprint model from both images
  p = finger.createModel();
  if (p != FINGERPRINT_OK) {
    Serial.println("Error: Fingerprints did not match.");
    return p;
  }

  // Store model with given ID
  p = finger.storeModel(id);
  if (p == FINGERPRINT_OK) {
    Serial.println("Stored new fingerprint!");
    return FINGERPRINT_OK;
  } else {
    Serial.println("Failed to store fingerprint.");
    return p;
  }
}

uint8_t getFingerprintID() {
  uint8_t p = finger.getImage();
  if (p != FINGERPRINT_OK) return 0;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) return 0;

  p = finger.fingerFastSearch();
  if (p != FINGERPRINT_OK) return 0;

  return finger.fingerID;
}
