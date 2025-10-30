#include <ESP8266WiFi.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP8266HTTPClient.h>

#define SS_PIN 15
#define RST_PIN 0

const char* ssid = "Saini's Tech 4G";
const char* password = "SAINISTANDARD";

const char* urlPrefix = "https://auraof.pranab.tech/rfid/";

WiFiClientSecure client;

MFRC522 mfrc522(SS_PIN, RST_PIN);

bool cardPresent = false;
String lastUID = "";

void setup() {
  Serial.begin(74880);
  delay(10);

  Serial.println();
  Serial.println("Starting NodeMCU RFID reader...");

  SPI.begin();

  mfrc522.PCD_Init();
  Serial.println("RFID reader initialized.");

  Serial.print("Connecting to WiFi SSID: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    attempts++;
    if (attempts > 40) {
      Serial.println();
      Serial.println("Failed to connect to WiFi, restarting...");
      ESP.restart();
    }
  }
  Serial.println();
  Serial.print("WiFi connected! IP address: ");
  Serial.println(WiFi.localIP());

  client.setInsecure();
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent()) {
    if (cardPresent) {
      Serial.println("Card removed");
      cardPresent = false;
      lastUID = "";
    }
    delay(100);
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    Serial.println("Failed to read card serial");
    delay(100);
    return;
  }

  String currentUID = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if(mfrc522.uid.uidByte[i] < 0x10) currentUID += "0";
    currentUID += String(mfrc522.uid.uidByte[i], HEX);
  }
  currentUID.toUpperCase();

  if (cardPresent && currentUID == lastUID) {
    delay(100);
    return;
  }

  cardPresent = true;
  lastUID = currentUID;

  Serial.print("Card detected with UID: ");
  Serial.println(currentUID);

  String url = String(urlPrefix) + "?rfidkey=" + currentUID + "-Dwarka_Sec_10";
  Serial.print("Sending HTTP GET request to: ");
  Serial.println(url);

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(client, url);

    int httpCode = http.GET();
    Serial.print("HTTP status code: ");
    Serial.println(httpCode);

    if (httpCode > 0) {
      String payload = http.getString();
      Serial.print("Server response: ");
      Serial.println(payload);
    } else {
      Serial.print("GET request failed, error: ");
      Serial.println(http.errorToString(httpCode).c_str());
    }
    http.end();
  } else {
    Serial.println("WiFi not connected - cannot send HTTP request.");
  }

  delay(500);
}
