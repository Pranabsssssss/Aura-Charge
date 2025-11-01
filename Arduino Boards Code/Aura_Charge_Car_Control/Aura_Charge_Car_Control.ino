#include <ESP8266WiFi.h>
#include <ESP8266WebServer.h>
#include <ESP8266mDNS.h>

const char* ssid = "Your_Wifi_Name";
const char* password = "Wifi_Password";

// Motor pins
#define IN1 D5
#define IN2 D6
#define IN3 D7
#define IN4 D8

// Light pins
#define FRONT_L D0
#define FRONT_R D3
#define REAR_L  D1
#define REAR_R  D2

ESP8266WebServer server(80);

// Light state tracking
bool fl_on=false, fr_on=false, rl_on=false, rr_on=false, all_on=false;

// ===== HTML =====
const char webpage[] PROGMEM = R"====(
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Aura_Charge Car</title>
<style>
body {
  background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  color:#fff;font-family:Arial;text-align:center;margin:0;padding:0;
  user-select:none;-webkit-user-select:none;-webkit-touch-callout:none;
}
h1{margin-top:20px;font-size:26px;}
.grid{display:grid;grid-template-columns:repeat(3,100px);gap:18px;
      justify-content:center;margin-top:30px;}
button{width:100px;height:100px;border-radius:14px;border:none;
       background:#00bcd4;color:#fff;font-size:20px;font-weight:700;
       box-shadow:0 4px 10px rgba(0,0,0,.3);}
.status{margin-top:25px;color:#ffd700;font-size:18px;}
.lights{margin-top:25px;display:flex;flex-wrap:wrap;gap:10px;
        justify-content:center;}
.lights button{width:140px;height:45px;border-radius:10px;font-size:15px;
               background:#ff9800;}
</style>
<script>
['contextmenu','selectstart','copy','cut','dragstart','gesturestart'].forEach(ev=>{
 document.addEventListener(ev,e=>e.preventDefault());
});

function send(cmd){
 fetch('/control?cmd='+cmd)
 .then(r=>r.text())
 .then(t=>document.getElementById('status').innerText=t)
 .catch(()=>document.getElementById('status').innerText='Error');
}

function setupButton(id,cmd){
 const b=document.getElementById(id);
 b.addEventListener('touchstart',e=>{e.preventDefault();send(cmd);});
 b.addEventListener('mousedown',e=>{e.preventDefault();send(cmd);});
 ['touchend','touchcancel','mouseup','mouseleave'].forEach(ev=>
   b.addEventListener(ev,e=>{e.preventDefault();send('S');}));
}

function toggleLight(id,cmd){
 const b=document.getElementById(id);
 b.addEventListener('click',()=>{
   let state=b.getAttribute('data-state')==='on'?'off':'on';
   b.setAttribute('data-state',state);
   b.style.background=(state==='on')?'#ff5722':'#ff9800';
   send(cmd+'_'+state.toUpperCase());
 });
}

window.onload=function(){
 setupButton('forward','F');
 setupButton('backward','B');
 setupButton('left','L');
 setupButton('right','R');
 toggleLight('fl','FL');
 toggleLight('fr','FR');
 toggleLight('rl','RL');
 toggleLight('rr','RR');
 toggleLight('all','ALL');
};
</script>
</head>
<body oncontextmenu="return false;">
<h1>Aura_Charge Car</h1>
<div class="grid">
  <div></div><button id="forward">▲</button><div></div>
  <button id="left">◄</button><div></div><button id="right">►</button>
  <div></div><button id="backward">▼</button><div></div>
</div>
<div class="lights">
  <button id="fl">Front Left</button>
  <button id="fr">Front Right</button>
  <button id="rl">Rear Left</button>
  <button id="rr">Rear Right</button>
  <button id="all" style="background:#2196f3;">All Lights</button>
</div>
<div class="status" id="status">Ready</div>
</body>
</html>
)====";

// ===== SETUP =====
void setup(){
  Serial.begin(74880);
  delay(100);
  Serial.println("[Aura_Charge] Booting...");

  pinMode(IN1,OUTPUT); pinMode(IN2,OUTPUT);
  pinMode(IN3,OUTPUT); pinMode(IN4,OUTPUT);
  pinMode(FRONT_L,OUTPUT); pinMode(FRONT_R,OUTPUT);
  pinMode(REAR_L,OUTPUT); pinMode(REAR_R,OUTPUT);
  allLightsOff(); stopMotors();

  WiFi.mode(WIFI_AP);
  WiFi.softAPConfig(IPAddress(192,168,1,1),
                    IPAddress(192,168,1,1),
                    IPAddress(255,255,255,0));
  WiFi.softAP(ssid,password);
  MDNS.begin("aura");
  Serial.println("mDNS responder started: http://aura.local");

  server.on("/", handleRoot);
  server.on("/control", handleControl);
  server.begin();

  Serial.println("Connect to SSID: Aura_Charge");
  Serial.println("Open http://192.168.1.1 or http://aura.local");
}

void loop(){
  server.handleClient();
  MDNS.update();
}

// ===== HANDLERS =====
void handleRoot(){ server.send(200,"text/html",webpage); }

void handleControl(){
  if(!server.hasArg("cmd")){server.send(400,"text/plain","No cmd");return;}
  String c=server.arg("cmd");
  Serial.println("CMD: "+c);

  if(c=="F"){ allLightsOff(); moveForward(); }
  else if(c=="B"){ allLightsOff(); moveBackward(); }
  else if(c=="L"){ allLightsOff(); turnLeft(); }
  else if(c=="R"){ allLightsOff(); turnRight(); }
  else if(c=="S"){ stopMotors(); allLightsOff(); }

  else if(c=="FL_ON"){ allLightsOff(); digitalWrite(FRONT_L,LOW); fl_on=true; }
  else if(c=="FL_OFF"){ digitalWrite(FRONT_L,HIGH); fl_on=false; }

  else if(c=="FR_ON"){ allLightsOff(); digitalWrite(FRONT_R,LOW); fr_on=true; }
  else if(c=="FR_OFF"){ digitalWrite(FRONT_R,HIGH); fr_on=false; }

  else if(c=="RL_ON"){ allLightsOff(); digitalWrite(REAR_L,LOW); rl_on=true; }
  else if(c=="RL_OFF"){ digitalWrite(REAR_L,HIGH); rl_on=false; }

  else if(c=="RR_ON"){ allLightsOff(); digitalWrite(REAR_R,LOW); rr_on=true; }
  else if(c=="RR_OFF"){ digitalWrite(REAR_R,HIGH); rr_on=false; }

  else if(c=="ALL_ON"){
    allLightsOn();
    all_on=true;
  }
  else if(c=="ALL_OFF"){
    allLightsOff();
    all_on=false;
  }

  server.send(200,"text/plain","Cmd: "+c);
}

// ===== MOTOR + LIGHT LOGIC =====
void stopMotors(){
  digitalWrite(IN1,LOW);digitalWrite(IN2,LOW);
  digitalWrite(IN3,LOW);digitalWrite(IN4,LOW);
  Serial.println("STOP");
}

void moveForward(){
  digitalWrite(IN1,HIGH);digitalWrite(IN2,LOW);
  digitalWrite(IN3,HIGH);digitalWrite(IN4,LOW);
  digitalWrite(FRONT_L,LOW);digitalWrite(FRONT_R,LOW);
  Serial.println("FORWARD");
}

void moveBackward(){
  digitalWrite(IN1,LOW);digitalWrite(IN2,HIGH);
  digitalWrite(IN3,LOW);digitalWrite(IN4,HIGH);
  digitalWrite(REAR_L,LOW);digitalWrite(REAR_R,LOW);
  Serial.println("BACKWARD");
}

void turnLeft(){
  digitalWrite(IN1,HIGH);digitalWrite(IN2,LOW);
  digitalWrite(IN3,LOW);digitalWrite(IN4,LOW);
  digitalWrite(FRONT_L,LOW);digitalWrite(REAR_L,LOW);
  Serial.println("LEFT");
}

void turnRight(){
  digitalWrite(IN1,LOW);digitalWrite(IN2,LOW);
  digitalWrite(IN3,HIGH);digitalWrite(IN4,LOW);
  digitalWrite(FRONT_R,LOW);digitalWrite(REAR_R,LOW);
  Serial.println("RIGHT");
}

void allLightsOff(){
  digitalWrite(FRONT_L,HIGH);
  digitalWrite(FRONT_R,HIGH);
  digitalWrite(REAR_L,HIGH);
  digitalWrite(REAR_R,HIGH);
  fl_on=fr_on=rl_on=rr_on=all_on=false;
}

void allLightsOn(){
  digitalWrite(FRONT_L,LOW);
  digitalWrite(FRONT_R,LOW);
  digitalWrite(REAR_L,LOW);
  digitalWrite(REAR_R,LOW);
  fl_on=fr_on=rl_on=rr_on=all_on=true;
}
