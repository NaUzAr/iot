#include <Nextion.h>
#include <EEPROM.h>
#include <ModbusMaster.h>
#include <BH1750.h>
#include "DFRobot_SHT20.h"
#include "SparkFunCCS811.h"
#include <Wire.h>
BH1750 lightMeter;
DFRobot_SHT20 sht20(&Wire, SHT20_I2C_ADDR);
#define CCS811_ADDR 0x5A
CCS811 mySensor(CCS811_ADDR);
#define EEPROM_I2C_ADDRESS 0x57
const int I2C_ADDRESS = 0x68;
#include <korayEEPROM.h>
#define psoil1 A0
#define psoil2 A1
#define psoil3 A2
#define psoil4 A3
#define psoil5 A6
#define psoil6 A7
#define psoil7 A8
#define psoil8 A9
#define psoil9 A10
#define psoil10 A11
int psoil1_, psoil2_, psoil3_, psoil4_, psoil5_, psoil6_, psoil7_, psoil8_, psoil9_, psoil10_;
struct config
{
  float bak_aut;
  float nut_aut;
  float bak_man;
  float nut_man;
  float am3_;
  float ab3_;
  float am4_;
  float ab4_;
} config;
const char* days[] = {"Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"};
const char* months[] = {"Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Des"};
byte second = 0;
byte minute = 0;
byte hour = 0;
byte weekday = 0;
byte monthday = 0;
byte month = 0;
byte year = 0;
int input = 0, mixing = 0, pompa = 0, _delays = 0;
float lux, humd, temp, nico2;
int R1,  ERRORrel = 0, countsend = 0, inp, bntu, tim, inter, pengali = 235, pengurang = 1, MODEDOS, mmist;
String kunci = "<SW#1#>|", WIf2, WIf;
String time_now, time_now2;
String comad = "#", spc = " ", kom = ",";
int hou, mnit, SENSERR, logW = 2, Swif, sns_tanah, sns_climate, sns_water, a1 = 0, a1_ = 0, a2 = 0;
float TDS1 = 0.0, EC1 = 0.0, cussen, SUHU1 = 0.0, KELEM1 = 0.0, LUX1 = 0.0, ab0, au, am0, am1, am2, am3, am4, au1, au2;
char buffer[12];
char buff[12];
int info_ = 10, send_sens = 0, count_read, cou = 0, cou2 = 0, cou_2 = 0, cou2_2 = 0, cou_3 = 0, cou2_3 = 0;
String payload, payload1, readse, schedule, Rwi = "Rwi";
int jam1, menit1, detik1, har, tang1, bul1, tah1, pmpinput;
int jmman, mnman, jmaut, mnaut, ppman = 60, mq, nut, mmix, msts;
float bak_man1, nut_man1, bak_aut1, nut_aut1, ab = 0.0, airMX2 = 0.0, airA2_2;
int h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18, h19, h20, h21, h22, h23, h24, h25, h26, h27, h28, h29, h30,
    h31, h32, h33, h34, h35, h36, h37, h38, h39, h40, h41, h42, h43, h44, h45, h46, h47, h48, h49, h50;
int m1, m2, m3, m4, m5, m6, m7, m8, m9, m10, m11, m12, m13, m14, m15, m16, m17, m18, m19, m20, m21, m22, m23, m24, m25, m26, m27, m28, m29, m30,
    m31, m32, m33, m34, m35, m36, m37, m38, m39, m40, m41, m42, m43, m44, m45, m46, m47, m48, m49, m50;
String jadwal1, jadwal2, jadwal3, jadwal4, jadwal5, jadwal6;
int Index1, Index2, Index3, Index4, Index5, Index6, Index7, Index8, Index9;
String secondValue, thirdValue, fourthValue, fifthValue, firstValue, sixValue, eigValue, nineValue;
String hh1, hh2, hh3, hh4, hh5, hh6, hh7, hh8, hh9, hh10, hh11, hh12, hh13, hh14, hh15, hh16, hh17, hh18, hh19, hh20, hh21, hh22, hh23, hh24, hh25, hh26, hh27, hh28, hh29, hh30, hh31, hh32, hh33, hh34, hh35, hh36;
String mm1, mm2, mm3, mm4, mm5, mm6, mm7, mm8, mm9, mm10, mm11, mm12, mm13, mm14, mm15, mm16, mm17, mm18, mm19, mm20, mm21, mm22, mm23, mm24, mm25, mm26, mm27, mm28, mm29, mm30, mm31, mm32, mm33, mm34, mm35, mm36;
String jad_man, jad_aut, jmman_, mnman_, jmaut_, mnaut_;
const char* _SSID1; const char* _SSID2; const char* _SSID3; const char* st_wifi__;
char rec, rec2;
static boolean recei2 = false;
String sLine, sLine2;

//page===== HOME==========
NexButton HOME1 = NexButton(0, 10, "b0");
NexButton SCHEDULE1 = NexButton(0, 11, "b1");
NexButton SENSOR1 = NexButton(0, 12, "b2");
NexButton MOTOR1 = NexButton(0, 13, "b3");
NexButton WIFI1 = NexButton(0, 14, "b4");
NexButton SETTING1 = NexButton(0, 15, "b5");
NexText CO2 = NexText(0, 2, "co2_");
NexText SUHU = NexText(0, 8, "suhu_");
NexText HUMI = NexText(0, 9, "humi_");
NexText LUX = NexText(0, 3, "lux_");
NexText EC = NexText(0, 7, "ec_");
NexText TDS = NexText(0, 6, "tds_");
NexText PH = NexText(0, 5, "ph_");
NexText soil1 = NexText(0, 34, "soil1_");
NexText soil2 = NexText(0, 35, "soil2_");
NexText soil3 = NexText(0, 36, "soil3_");
NexText soil4 = NexText(0, 37, "soil4_");
NexText soil5 = NexText(0, 38, "soil5_");
NexText soil6 = NexText(0, 39, "soil6_");
NexText soil7 = NexText(0, 40, "soil7_");
NexText soil8 = NexText(0, 41, "soil8_");
NexText soil9 = NexText(0, 42, "soil9_");
NexText soil10 = NexText(0, 43, "soil10_");
NexText waktu = NexText(0, 45, "time");
NexText FA = NexText(0, 48, "FA");
NexText FD = NexText(0, 50, "FD");
NexText KA = NexText(0, 49, "KA");
NexText KD = NexText(0, 51, "KD");
NexText CM = NexText(0, 54, "CM");
NexText DM = NexText(0, 55, "DM");
NexText wifi = NexText(0, 47, "wifi");
//page===== SCHEDULE==========
NexButton HOME2 = NexButton(1, 1, "b0");
NexButton SCHEDULE2 = NexButton(1, 2, "b1");
NexButton SENSOR2 = NexButton(1, 3, "b2");
NexButton MOTOR2 = NexButton(1, 4, "b3");
NexButton WIFI2 = NexButton(1, 5, "b4");
NexButton SETTING2 = NexButton(1, 6, "b5");
NexText jdh1 = NexText(1, 7, "t0");
NexText jdh2 = NexText(1, 8, "t1");
NexText jdh3 = NexText(1, 9, "t2");
NexText jdh4 = NexText(1, 10, "t3");
NexText jdh5 = NexText(1, 11, "t4");
NexText jdh6 = NexText(1, 12, "t5");
NexText waktu2 = NexText(1, 25, "time");
NexText jdman = NexText(1, 16, "t9");
NexText jdaut = NexText(1, 18, "t11");
NexText hps1 = NexText(1, 27, "b6");
NexText hps2 = NexText(1, 28, "b7");
NexText hps3 = NexText(1, 29, "b8");
NexText hps4 = NexText(1, 30, "b9");
NexText hps5 = NexText(1, 31, "b10");
NexText hps6 = NexText(1, 32, "b11");
NexText hpsman = NexText(1, 33, "b12");
NexText hpsaut = NexText(1, 34, "b13");
//page===== SENSOR==========
NexButton HOME3 = NexButton(2, 1, "b0");
NexButton SCHEDULE3 = NexButton(2, 2, "b1");
NexButton SENSOR3 = NexButton(2, 3, "b2");
NexButton MOTOR3 = NexButton(2, 4, "b3");
NexButton WIFI3 = NexButton(2, 5, "b4");
NexButton SETTING3 = NexButton(2, 6, "b5");
NexVariable memsetsens = NexVariable(2, 16, "page2.sens");
NexButton DIUDARA = NexButton(2, 7, "b6");
NexButton ENUDARA = NexButton(2, 8, "b7");
NexButton DITANAH = NexButton(2, 10, "b8");
NexButton ENTANAH = NexButton(2, 11, "b9");
NexButton DIAIR = NexButton(2, 13, "b10");
NexButton ENAIR = NexButton(2, 14, "b11");
NexButton tempe = NexButton(2, 26, "b16");
NexButton humid = NexButton(2, 33, "b21");
NexButton fanon = NexButton(2, 42, "b22");
NexButton fanoff = NexButton(2, 43, "b23");
NexButton mison = NexButton(2, 44, "b24");
NexButton misoff = NexButton(2, 45, "b25");
//page===== MOTOR==========
NexButton HOME4 = NexButton(3, 1, "b0");
NexButton SCHEDULE4 = NexButton(3, 2, "b1");
NexButton SENSOR4 = NexButton(3, 3, "b2");
NexButton MOTOR4 = NexButton(3, 4, "b3");
NexButton WIFI4 = NexButton(3, 5, "b4");
NexButton SETTING4 = NexButton(3, 6, "b5");
NexButton inpON = NexButton(3, 9, "b7");
NexButton inpOFF = NexButton(3, 8, "b6");
NexButton mixON = NexButton(3, 13, "b9");
NexButton mixOFF = NexButton(3, 12, "b8");
NexButton pmpON = NexButton(3, 17, "b11");
NexButton pmpOFF = NexButton(3, 16, "b10");
NexVariable memsetmtr = NexVariable(3, 20, "page3.mtr");
NexButton DOSMAN = NexButton(3, 25, "b12");
NexButton DOSAUT = NexButton(3, 31, "b13");
//page===== WIFI==========
NexButton HOME5 = NexButton(4, 1, "b0");
NexButton SCHEDULE5 = NexButton(4, 2, "b1");
NexButton SENSOR5 = NexButton(4, 3, "b2");
NexButton MOTOR5 = NexButton(4, 4, "b3");
NexButton WIFI5 = NexButton(4, 5, "b4");
NexButton SETTING5 = NexButton(4, 6, "b5");
NexButton scan_wifi = NexButton(4, 16, "b6");
NexButton setWifi = NexButton(4, 17, "b7");
NexVariable memsetwifi = NexVariable(4, 19, "page4.setWI");
NexText SSID1 = NexText(4, 13, "scn1");
NexText SSID2 = NexText(4, 14, "scn2");
NexText SSID3 = NexText(4, 15, "scn3");
//page===== SETTING==========
NexButton HOME6 = NexButton(5, 1, "b0");
NexButton SCHEDULE6 = NexButton(5, 2, "b1");
NexButton SENSOR6 = NexButton(5, 3, "b2");
NexButton MOTOR6 = NexButton(5, 4, "b3");
NexButton WIFI6 = NexButton(5, 5, "b4");
NexButton SETTING6 = NexButton(5, 6, "b5");
NexVariable memsetWkt = NexVariable(10, 9, "page10.waktu");
NexButton setwkt = NexButton(10, 8, "b5");
NexButton back_setwkt = NexButton(10, 10, "b1");

NexPage pgHOME = NexPage(0, 0, "page0");
NexPage pgSCHEDULE = NexPage(1, 0, "page1");
NexPage pgSENSOR = NexPage(2, 0, "page2");
NexPage pgMOTOR = NexPage(3, 0, "page3");
NexPage pgWIFI = NexPage(4, 0, "page4");
NexPage pgSETTING = NexPage(5, 0, "page5");
NexPage pgsetwkt = NexPage(10, 0, "page10");

NexPage pgSTS = NexPage(6, 0, "page6");
NexPage pgsetdos = NexPage(11, 0, "page11");
NexPage pgJADWAL = NexPage(8, 0, "page8");
NexVariable memsetjadwal = NexVariable(8, 9, "page8.va0");
NexButton setjad = NexButton(8, 8, "b0");
NexButton back_from_jad = NexButton(8, 12, "b1");
NexButton set_dos = NexButton(11, 9, "b0");
NexButton back_from_dos = NexButton(11, 10, "b1");
NexVariable memsetdos = NexVariable(11, 12, "page11.do");

NexTouch *nex_listen_list[] =
{
  //======= HOME =====
  &HOME1,
  &SCHEDULE1,
  &SENSOR1,
  &MOTOR1,
  &WIFI1,
  &SETTING1,
  &CO2,
  &SUHU,
  &HUMI,
  &LUX,
  &EC,
  &TDS,
  &PH,
  &soil1,
  &soil2,
  &soil3,
  &soil4,
  &soil5,
  &soil6,
  &soil7,
  &soil8,
  &soil9,
  &soil10,
  &waktu,
  &FA,
  &KA,
  &FD,
  &KD,
  &CM,
  &DM,
  &wifi,
  //============ SCHEDULE ====
  &HOME2,
  &SCHEDULE2,
  &SENSOR2,
  &MOTOR2,
  &WIFI2,
  &SETTING2,
  &jdh1,
  &jdh2,
  &jdh3,
  &jdh4,
  &jdh5,
  &jdh6,
  &waktu2,
  &jdman,
  &jdaut,
  &hps1,
  &hps2,
  &hps3,
  &hps4,
  &hps5,
  &hps6,
  &hpsman,
  &hpsaut,
  //============ SENSOR ===
  &HOME3,
  &SCHEDULE3,
  &SENSOR3,
  &MOTOR3,
  &WIFI3,
  &SETTING3,
  &memsetsens,
  &DIUDARA,
  &ENUDARA,
  &DITANAH,
  &ENTANAH,
  &DIAIR,
  &ENAIR,
  &tempe,
  &humid,
  &fanon,
  &fanoff,
  &mison,
  &misoff,
  //============ MOTOR ===
  &HOME4,
  &SCHEDULE4,
  &SENSOR4,
  &MOTOR4,
  &WIFI4,
  &SETTING4,
  &inpON,
  &inpOFF,
  &mixON,
  &mixOFF,
  &pmpON,
  &pmpOFF,
  &memsetmtr,
  &DOSMAN,
  &DOSAUT,
  //============ WIFI ===
  &HOME5,
  &SCHEDULE5,
  &SENSOR5,
  &MOTOR5,
  &WIFI5,
  &SETTING5,
  &scan_wifi,
  &memsetwifi,
  &SSID1,
  &SSID2,
  &SSID3,
  &setWifi,
  //============ SETTING ===
  &HOME6,
  &SCHEDULE6,
  &SENSOR6,
  &MOTOR6,
  &WIFI6,
  &SETTING6,
  &memsetWkt,
  &setwkt,

  &pgHOME,
  &pgSCHEDULE,
  &pgSENSOR,
  &pgMOTOR,
  &pgWIFI,
  &pgSETTING,
  &pgJADWAL,
  &memsetjadwal,
  &setjad,
  &pgSTS,
  &back_from_jad,
  &pgsetwkt,
  &back_setwkt,
  &pgsetdos,
  &set_dos,
  &memsetdos,
  &back_from_dos,
  NULL
};
void writeEEPROM(int address, byte val, int i2c_address) {
  Wire.beginTransmission(i2c_address);
  Wire.write((int)(address >> 8));
  Wire.write((int)(address & 0xFF));
  Wire.write(val);
  Wire.endTransmission();
  delay(5);
}
byte readEEPROM(int address, int i2c_address) {
  byte rcvData = 0xFF;
  Wire.beginTransmission(i2c_address);
  Wire.write((int)(address >> 8));
  Wire.write((int)(address & 0xFF));
  Wire.endTransmission();
  Wire.requestFrom(i2c_address, 1);
  rcvData = Wire.read();
  return rcvData;
}
void _pgHOME(void *ptr) {
  info_ = 10;
  pgHOME.show();
  _delays = 100;
}
void _pgSCHEDULE(void *ptr) {
  info_ = 20;
  pgSCHEDULE.show();
  _delays = 100;
}
void _pgSENSOR(void *ptr) {
  info_ = 30;
  pgSENSOR.show();
  _delays = 100;
}
void _pgMOTOR(void *ptr) {
  info_ = 40;
  pgMOTOR.show();
  _delays = 100;
}
void _pgWIFI(void *ptr) {
  info_ = 50;
  pgWIFI.show();
  _delays = 1000;
}
void _pgSETTING(void *ptr) {
  info_ = 60;
  pgSETTING.show();
  _delays = 100;
}
void _pgJADWAL(void *ptr) {
  info_ = 70;
  pgJADWAL.show();
}
void send_page_HOME() {
  String co2_ = String(nico2); String suhu_ = String(temp);
  String humi_ = String(humd); String lux_ = String(lux); String ec_ = String(random(0, 100));
  String tds_ = String(random(0, 100)); String ph_ = String(random(0, 100));
  String soil1_ = String(psoil1_); String soil2_ = String(psoil2_);
  String soil3_ = String(psoil3_); String soil4_ = String(psoil4_);
  String soil5_ = String(psoil5_); String soil6_ = String(psoil6_);
  String soil7_ = String(psoil7_); String soil8_ = String(psoil8_);
  String soil9_ = String(psoil9_); String soil10_ = String(psoil10_);
  String FA_ = String(m43); String FD_ = String(h44); String KA_ = String(h42); String KD_ = String(h43);
  String CM_; String DM_; String WIFI_;
  if (m44 == 1) {
    CM_ = "SENSOR";
  } else if (m44 == 0) {
    CM_ = "MANUAL";
  }
  if (MODEDOS == 1) {
    DM_ = "SENSOR";
  } else if (MODEDOS == 0) {
    DM_ = "MANUAL";
  }
  if (h37 == 1) {
    WIFI_ = "Online";
  } else if (h37 == 0) {
    WIFI_ = "Offline";
  }
  const char *co2__; const char *suhu__; const char *humi__; const char *lux__;
  const char *ec__; const char *tds__; const char *ph__;
  const char *soil1__; const char *soil2__; const char *soil3__; const char *soil4__;
  const char *soil5__; const char *soil6__; const char *soil7__; const char *soil8__;
  const char *soil9__; const char *soil10__; const char *waktu__;
  const char *FA__; const char *FD__; const char *KA__; const char *KD__; const char *CM__;
  const char *DM__; const char *WIFI__;
  co2__ = co2_.c_str(); suhu__ = suhu_.c_str(); humi__ = humi_.c_str(); lux__ = lux_.c_str();
  ec__ = ec_.c_str(); tds__ = tds_.c_str(); ph__ = ph_.c_str();
  soil1__ = soil1_.c_str(); soil2__ = soil2_.c_str(); soil3__ = soil3_.c_str(); soil4__ = soil4_.c_str();
  soil5__ = soil5_.c_str(); soil6__ = soil6_.c_str(); soil7__ = soil7_.c_str(); soil8__ = soil8_.c_str();
  soil9__ = soil9_.c_str(); soil10__ = soil10_.c_str(); waktu__ = time_now2.c_str();
  FA__ = FA_.c_str(); FD__ = FD_.c_str(); KA__ = KA_.c_str(); KD__ = KD_.c_str();
  CM__ = CM_.c_str(); DM__ = DM_.c_str(); WIFI__ = WIFI_.c_str();
  CO2.setText(co2__); SUHU.setText(suhu__); HUMI.setText(humi__); LUX.setText(lux__); EC.setText(ec__);
  TDS.setText(tds__); PH.setText(ph__);
  soil1.setText(soil1__); soil2.setText(soil2__); soil3.setText(soil3__); soil4.setText(soil4__);
  soil5.setText(soil5__); soil6.setText(soil6__); soil7.setText(soil7__); soil8.setText(soil8__);
  soil9.setText(soil9__); soil10.setText(soil10__); waktu.setText(waktu__);
  FA.setText(FA__); FD.setText(FD__); KA.setText(KA__); KD.setText(KD__); CM.setText(CM__);
  DM.setText(DM__); wifi.setText(WIFI__);
}
void send_page_schedule() {
  readeep();
  eeRead(260, config);
  jadwal1 = hh1 + ":" + mm1 + "-" + hh2 + ":" + mm2;
  jadwal2 = hh3 + ":" + mm3 + "-" + hh4 + ":" + mm4;
  jadwal3 = hh5 + ":" + mm5 + "-" + hh6 + ":" + mm6;
  jadwal4 = hh7 + ":" + mm7 + "-" + hh8 + ":" + mm8;
  jadwal5 = hh9 + ":" + mm9 + "-" + hh10 + ":" + mm10;
  jadwal6 = hh11 + ":" + mm11 + "-" + hh12 + ":" + mm12;
  jad_man = String(jmman_ + ":" + mnman_ + "-" + String(config.bak_man) + "-" + String(config.nut_man));
  jad_aut = String(jmaut_ + ":" + mnaut_ + "-" + String(config.bak_aut) + "-" + String(config.nut_aut));
  const char *jadwal1_; const char *jadwal2_; const char *jadwal3_;
  const char *jadwal4_; const char *jadwal5_; const char *jadwal6_;
  const char *waktu2__; const char *jad_man_; const char *jad_aut_;
  jadwal1_ = jadwal1.c_str(); jadwal2_ = jadwal2.c_str(); jadwal3_ = jadwal3.c_str();
  jadwal4_ = jadwal4.c_str(); jadwal5_ = jadwal5.c_str(); jadwal6_ = jadwal6.c_str();
  waktu2__ = time_now2.c_str(); jad_man_ = jad_man.c_str(); jad_aut_ = jad_aut.c_str();
  jdh1.setText(jadwal1_); jdh2.setText(jadwal2_); jdh3.setText(jadwal3_);
  jdh4.setText(jadwal4_); jdh5.setText(jadwal5_); jdh6.setText(jadwal6_);
  waktu2.setText(waktu2__); jdman.setText(jad_man_); jdaut.setText(jad_aut_);
}
void _setsch (void *ptr) {
  char _sch[100];
  memsetjadwal.getText(_sch, 100);
  sLine += _sch;
  delay(10);
  recei2 = true;
  Serial.println(_sch);
  pgSTS.show();
  delay(1000);
  pgJADWAL.show();
  delay(1000);
}
void _setsch2 (void *ptr) {
  char _sch[100];
  memsetjadwal.getText(_sch, 100);
  sLine += _sch;
  delay(10);
  recei2 = true;
  Serial.println(_sch);
  pgSTS.show();
  delay(1000);
  pgSCHEDULE.show();
  delay(1000);
}
void _sensor(void *ptr) {
  char _sensors[100];
  memsetsens.getText(_sensors, 100);
  sLine += _sensors;
  delay(10);
  recei2 = true;
  Serial.println(_sensors);
  pgSTS.show();
  delay(1000);
  pgSENSOR.show();
  delay(1000);
}
void _motors(void *ptr) {
  char _memsetmtr[100];
  memsetmtr.getText(_memsetmtr, 100);
  sLine += _memsetmtr;
  delay(10);
  recei2 = true;
  Serial.println(_memsetmtr);
  pgSTS.show();
  delay(1000);
  pgMOTOR.show();
  delay(1000);
}
void _wifiscan(void *ptr) {
  logW = 3;
  delay(1000);
}
void pgsetWifi(void *ptr) {
  //WIf2 = "";
  char _Wifi[200];
  memsetwifi.getText(_Wifi, 200);
  WIf += _Wifi;
  WIf2 = WIf;
  delay(10);
  pgSTS.show();
  delay(1000);
  pgWIFI.show();
  delay(1000);
  logW = 1;
}
void SET_WKT(void *ptr)
{
  char _setWkt[40];
  memsetWkt.getText(_setWkt, 60);
  sLine += _setWkt;
  delay(10);
  recei2 = true;
  Serial.println(sLine);
  pgSTS.show();
  delay(1000);
  pgsetwkt.show();
  delay(1000);
  //sLine="";
}
void _pgsetdos2(void *ptr)
{
  info_ = 70;
  pgsetdos.show();
  //delay(1000);
}
void _pgsetdos(void *ptr)
{
  char _memsetdos[80];
  memsetdos.getText(_memsetdos, 80);
  sLine += _memsetdos;
  delay(10);
  recei2 = true;
  Serial.println(sLine);
  pgSTS.show();
  delay(1000);
  pgsetdos.show();
  delay(1000);
}
void _pgsetdos3(void *ptr)
{
  char _memsetdos[80];
  memsetdos.getText(_memsetdos, 80);
  sLine += _memsetdos;
  delay(10);
  recei2 = true;
  Serial.println(sLine);
  pgSTS.show();
  delay(1000);
  pgSCHEDULE.show();
  delay(1000);
}
void setup() {
  Serial.begin(9600);
  Wire.begin();
  lightMeter.begin();
  sht20.initSHT20();
  delay(100);
  sht20.checkSHT20();
  mySensor.begin();
  nexInit();
  nexSerial.begin(9600);
  Serial1.begin(9600);
  pinMode(psoil1, INPUT); pinMode(psoil2, INPUT); pinMode(psoil3, INPUT);
  pinMode(psoil4, INPUT); pinMode(psoil5, INPUT); pinMode(psoil6, INPUT);
  pinMode(psoil7, INPUT); pinMode(psoil8, INPUT); pinMode(psoil9, INPUT);
  pinMode(psoil10, INPUT);
  //========== isi page home =======
  HOME1.attachPop(_pgHOME);
  SCHEDULE1.attachPop(_pgSCHEDULE);
  SENSOR1.attachPop(_pgSENSOR);
  MOTOR1.attachPop(_pgMOTOR);
  WIFI1.attachPop(_pgWIFI);
  SETTING1.attachPop(_pgSETTING);
  //========== isi page schedule =======
  HOME2.attachPop(_pgHOME);
  SCHEDULE2.attachPop(_pgSCHEDULE);
  SENSOR2.attachPop(_pgSENSOR);
  MOTOR2.attachPop(_pgMOTOR);
  WIFI2.attachPop(_pgWIFI);
  SETTING2.attachPop(_pgSETTING);
  jdh1.attachPop(_pgJADWAL);
  jdh2.attachPop(_pgJADWAL);
  jdh3.attachPop(_pgJADWAL);
  jdh4.attachPop(_pgJADWAL);
  jdh5.attachPop(_pgJADWAL);
  jdh6.attachPop(_pgJADWAL);
  hps1.attachPop(_setsch2);
  hps2.attachPop(_setsch2);
  hps3.attachPop(_setsch2);
  hps4.attachPop(_setsch2);
  hps5.attachPop(_setsch2);
  hps6.attachPop(_setsch2);
  hpsman.attachPop(_pgsetdos3);
  hpsaut.attachPop(_pgsetdos3);
  jdman.attachPop(_pgsetdos2);
  jdaut.attachPop(_pgsetdos2);
  //========== isi page sensor =======
  HOME3.attachPop(_pgHOME);
  SCHEDULE3.attachPop(_pgSCHEDULE);
  SENSOR3.attachPop(_pgSENSOR);
  MOTOR3.attachPop(_pgMOTOR);
  WIFI3.attachPop(_pgWIFI);
  SETTING3.attachPop(_pgSETTING);
  DIUDARA.attachPop(_sensor);
  ENUDARA.attachPop(_sensor);
  DITANAH.attachPop(_sensor);
  ENTANAH.attachPop(_sensor);
  DIAIR.attachPop(_sensor);
  ENAIR.attachPop(_sensor);
  tempe.attachPop(_sensor);
  humid.attachPop(_sensor);
  fanon.attachPop(_sensor);
  fanoff.attachPop(_sensor);
  mison.attachPop(_sensor);
  misoff.attachPop(_sensor);
  //========== isi page motor =======
  HOME4.attachPop(_pgHOME);
  SCHEDULE4.attachPop(_pgSCHEDULE);
  SENSOR4.attachPop(_pgSENSOR);
  MOTOR4.attachPop(_pgMOTOR);
  WIFI4.attachPop(_pgWIFI);
  SETTING4.attachPop(_pgSETTING);
  inpON.attachPop(_motors);
  inpOFF.attachPop(_motors);
  mixON.attachPop(_motors);
  mixOFF.attachPop(_motors);
  pmpON.attachPop(_motors);
  pmpOFF.attachPop(_motors);
  DOSMAN.attachPop(_motors);
  DOSAUT.attachPop(_motors);
  //========== isi page wifi =======
  HOME5.attachPop(_pgHOME);
  SCHEDULE5.attachPop(_pgSCHEDULE);
  SENSOR5.attachPop(_pgSENSOR);
  MOTOR5.attachPop(_pgMOTOR);
  WIFI5.attachPop(_pgWIFI);
  SETTING5.attachPop(_pgSETTING);
  scan_wifi.attachPop(_wifiscan);
  setWifi.attachPop(pgsetWifi);
  //========== isi page setting =======
  HOME6.attachPop(_pgHOME);
  SCHEDULE6.attachPop(_pgSCHEDULE);
  SENSOR6.attachPop(_pgSENSOR);
  MOTOR6.attachPop(_pgMOTOR);
  WIFI6.attachPop(_pgWIFI);
  SETTING6.attachPop(_pgSETTING);
  setjad.attachPop(_setsch);
  back_from_jad.attachPop(_pgSCHEDULE);
  setwkt.attachPop(SET_WKT);
  back_setwkt.attachPop(_pgSETTING);
  set_dos.attachPop(_pgsetdos);
  back_from_dos.attachPop(_pgSCHEDULE);
}

void loop() {
  Serial.print("sensor air=");
  Serial.println(ab);
  countsend++;
  nexLoop(nex_listen_list);
  count_read++;
  if (count_read == 40) {
    readeep();
  }
  if (sns_tanah == 0) {
    //nothing
  } else if (sns_tanah == 1) {
    //Serial.println("tanah aktif");
    baca_tanah();
  }
  if (sns_climate == 0) {
    //nothing
  } else if (sns_climate == 1) {
    baca_climate();
  }
  updat();
  printTime();
  //=========== auto logic =============
  if (ppman == 50) {
    nexLoop(nex_listen_list);
    nut = 0;
    if (ab > 0) {
      pmpinput = 20;
      nexLoop(nex_listen_list);
    } else if (ab <= 0.0) {
      ab = 0.0;
      pmpinput = 30;
      // motor_input_off();
    }
    if (pmpinput == 20) {
      nexLoop(nex_listen_list);
      //      motor_input_on();
      inp = 50;
      Serial.println("pompa air baku on");
      nexLoop(nex_listen_list);
    } else if (pmpinput == 30 && bntu == 20) {
      // motor_input_off();
      inp = 50;
      nut = 20;
      nexLoop(nex_listen_list);
      Serial.println("pompa air baku off");
    }
    if (nut == 20 && bntu == 20) {
      nexLoop(nex_listen_list);
      cou += 1;
      nexLoop(nex_listen_list);
      Serial.print("mulai mixing mode sensor ");
      nexLoop(nex_listen_list);
      Serial.println(cou);
      if (cou >= 30)
      {
        if (TDS1 >= au)
        {
          nexLoop(nex_listen_list);
          //          motor_mixer_off();
          //          motor_feris_off();
          nexLoop(nex_listen_list);
          nut = 0;
          bntu = 0;
          Serial.println("Dosing auto selesai");
          nexLoop(nex_listen_list);
        }
        else
        {
          nexLoop(nex_listen_list);
          // motor_mixer_on();
          // motor_feris_on();
          Serial.println("nutrisi sedang di tambah");
          nexLoop(nex_listen_list);
        }
        cou2 += 1;
        if (cou2 >= 20)
        {
          nexLoop(nex_listen_list);
          Serial.println("Reset keatas");
          cou = 0;
          nexLoop(nex_listen_list);
          cou2 = 0;
        }
      }
      else
      {
        nexLoop(nex_listen_list);
        //motor_mixer_on();
        //motor_feris_off();
        nexLoop(nex_listen_list);
        Serial.println("Sedang mixing");
      }
    }
    else
    {
      nexLoop(nex_listen_list);
      //motor_mixer_off();
      //motor_feris_off();
      nexLoop(nex_listen_list);
      cou = 0;
      cou2 = 0;
      nexLoop(nex_listen_list);
    }
  }
  //=========================== manual logic =======
  else if (ppman == 60) {
    nut = 0;
    nexLoop(nex_listen_list);
    if (ab > 0) {
      pmpinput = 20;
      nexLoop(nex_listen_list);
    } else if (ab <= 0.0) {
      nexLoop(nex_listen_list);
      ab = 0.0;
      pmpinput = 30;
      //motor_input_off();
      nexLoop(nex_listen_list);
    }
    if (pmpinput == 20) {
      //motor_input_on();
      nexLoop(nex_listen_list);
      Serial.println("pompa air baku on");
    } else if (pmpinput == 30 && bntu == 20) {
      //motor_input_off();
      nut = 20;
      Serial.println("pompa air baku off");
      nexLoop(nex_listen_list);
    }
    if (nut == 20) {
      nexLoop(nex_listen_list);
      tim = tim - pengurang;
      Serial.print("nilai=");
      Serial.println(tim);
      nexLoop(nex_listen_list);
      if (tim <= 0) {
        //  motor_mixer_off();
        //  motor_feris_off();
        nut = 0;
        m36 = 0;
        bntu = 30;
        nexLoop(nex_listen_list);
        Serial.println("pompa nutrisi off");
        if (tim == 0) {
          mmix = 30;
        }
        tim = -1;
      } else if (tim > 0) {
        //        motor_feris_on();
        mmix = 20;
        m36 = 1;
        Serial.println("pompa nutrisi on");
      }
    }
    if (mmix == 20 || msts == 20) {
      // motor_mixer_on();
      msts = 0;
      mmix = 0;
      Serial.println("pompa mixer on");
    } else if (mmix == 30 || msts == 30) {
      //motor_mixer_off();
      //selenoid_off();
      msts = 0;
      mmix = 0;
      Serial.println("pompa mixer off");
    }
    //============== end logic ============
  } else if (ppman == 70) {
    if (input == 1) {
      Serial.println("air input ON");
    } else if (input == 0) {
      Serial.println("air input OFF");
    }
    if (mixing == 1) {
      Serial.println("air mixing ON");
    } else if (mixing == 0) {
      Serial.println("air mixing OFF");
    }
    if (pompa == 1) {
      Serial.println("air pompa ON");
    } else if (pompa == 0) {
      Serial.println("air pompa OFF");
    }
  }
  int hms = sprintf(buffer, "%02d:%02d", hour, minute);
  time_now2 = ((days[weekday - 1]) + spc + (monthday) + spc + (months[month - 1]) + spc + (year) + "-" + buffer);
  nexLoop(nex_listen_list);
  while (Serial1.available() > 0)
  {
    nexLoop(nex_listen_list);
    rec2 = Serial1.read();
    sLine += rec2;
    delay(10);
    recei2 = true;
    nexLoop(nex_listen_list);
  }
  if (recei2 == true)
  {
    nexLoop(nex_listen_list);
    Serial.println(sLine);
    parsi2();
    delay(30);
    logi();
    delay(100);
    sLine = "";
    recei2 = false;
    nexLoop(nex_listen_list);
  }
  Serial.println(time_now2);
  if (info_ == 10) {
    nexLoop(nex_listen_list);
    send_sens++;
    if (send_sens >= 10) {
      nexLoop(nex_listen_list);
      send_page_HOME();
      send_sens = 0;
    }
    delay(100);
  }
  else if (info_ == 20) {
    nexLoop(nex_listen_list);
    send_sens++;
    if (send_sens >= 10) {
      send_page_schedule();
      send_sens = 0;
    }
    delay(100);
  }
  else if (info_ == 30) {

  }
  else if (info_ == 40) {

  }
  else if (info_ == 50) {

  }
  else if (info_ == 60) {

  }
  else if (info_ == 70) {

  }
  if ((hou == h1 && mnit == h2) || (hou == h3 && mnit == h4) || (hou == h5 && mnit == h6) || (hou == h7 && mnit == h8) || (hou == h9 && mnit == h10) || (hou == h11 && mnit == h12))
  {
    Serial.print("motor hidup");
  } else if ((hou == m1 && mnit == m2) || (hou == m3 && mnit == m4) || (hou == m5 && mnit == m6) || (hou == m7 && mnit == m8) || (hou == m9 && mnit == m10) || (hou == m11 && mnit == m12))
  {
    Serial.print("motor mati");
  }
  if (m44 == 0) {
    nexLoop(nex_listen_list);
    Serial.println("climate fan mode manual");
    if (h45 == 1) {
      Serial.println("fan hidup");
      //RELAY(3, true);
      // FAN = "1";
      nexLoop(nex_listen_list);
    } else if (h45 == 0) {
      Serial.println("fan mati");
      //       RELAY(3, false);
      //  FAN = "0";
      nexLoop(nex_listen_list);
    }
  }
  else if (m44 == 1) {
    Serial.println("climate fan mode auto");
    if (SUHU1 >= h42) {
      nexLoop(nex_listen_list);
      Serial.println("fan hidup");
      //RELAY(3, true);
      // FAN = "1";
      nexLoop(nex_listen_list);
    } else if (SUHU1 <= h43) {
      nexLoop(nex_listen_list);
      Serial.println("fan mati");
      //RELAY(3, false);
      //       FAN = "0";
      nexLoop(nex_listen_list);
    }
  }
  if (mmist == 0) {
    nexLoop(nex_listen_list);
    Serial.println("climate misting mode manual");
    if (m45 == 1) {
      nexLoop(nex_listen_list);
      Serial.println("misting hidup");
      //RELAY(4, true);
      // MIST = "1";
    } else if (m45 == 0) {
      nexLoop(nex_listen_list);
      Serial.println("misting mati");
      //RELAY(4, false);
      // MIST = "0";
      nexLoop(nex_listen_list);
    }
  }
  else if (mmist == 1) {
    Serial.println("climate misting mode auto");
    if (KELEM1 >= m43) {
      nexLoop(nex_listen_list);
      Serial.println("misting mati");
      //RELAY(4, false);
      //       MIST = "0";
    } else if (KELEM1 <= h44) {
      Serial.println("misting hidup");
      //RELAY(4, true);
      //       MIST = "1";
      nexLoop(nex_listen_list);
    }
  }
  // put your main code here, to run repeatedly:
  Serial.print("log W=");
  Serial.println(logW);
  if (logW == 1) {
    Swif++;
    if (Swif == 2) {
      payload1 = "";
      payload1 = ("<" + Rwi + "|" + WIf2 + "");
      Swif = 0;
    }
    payload1 = WIf2;
    WIf = "";
  } else if (logW == 2) {
    Serial.println("sudah masuk");
    jtson();
    if (countsend > 4) {
      countsend = 0;
    }
  } else if (logW == 3) {
    Swif++;
    if (Swif == 2) {
      payload1 = ("<SW#1#>|");
      delay(100);
      Swif = 0;
    }
  }
  Serial1.print(payload1);
  Serial.println(payload1);
  if (MODEDOS == 0) {
    nexLoop(nex_listen_list);
    if (hou == jmman && mnit == mnman)
    {
      a2++;
      Serial.println(a2);
      if (a2 == 2) {
        eeRead(260, config);
        ab = (config.bak_man); bntu = 20;
        ppman = 60;
        delay(30);
        tim = (config.nut_man) * pengali;
        airA2_2 = (config.bak_man);
      }
      Serial.print("nilai nutrisi= ");
      Serial.println(config.nut_man);
      Serial.print("nilai air baku= ");
      Serial.println(config.bak_man);
    }
  }
  else if (MODEDOS == 1) {
    if (hou == jmaut && mnit == mnaut)
    {
      nexLoop(nex_listen_list);
      a2++;
      Serial.println(a2);
      if (a2 == 2) {
        eeRead(260, config);
        ab = (config.bak_aut); bntu = 20;
        ppman = 50;
        delay(30);
        au = (config.nut_aut);
        airA2_2 = (config.bak_aut);
        nexLoop(nex_listen_list);
      }
      Serial.print("nilai nutrisi= ");
      Serial.println(config.nut_aut);
      Serial.print("nilai air baku= ");
      Serial.println(config.bak_aut);
    }
  }
  delay(_delays);
}
void baca_climate() {
  lux = lightMeter.readLightLevel();
  Serial.print("Light: ");
  Serial.print(lux);
  Serial.println(" lx");
  delay(10);

  humd = sht20.readHumidity();
  temp = sht20.readTemperature();
  SUHU1 = temp;
  KELEM1 = humd;
  Serial.print(" Temperature:");
  Serial.print(temp, 1);   // Only print one decimal place
  Serial.print("C");
  Serial.print(" Humidity:");
  Serial.print(humd, 1);   // Only print one decimal place
  Serial.print("%");
  Serial.println();
  delay(10);

  if (mySensor.dataAvailable())
  {
    mySensor.readAlgorithmResults();
    Serial.print("CO2[");
    nico2 = mySensor.getCO2();
    //Returns calculated CO2 reading
    Serial.print(mySensor.getCO2());
    Serial.print("] tVOC[");
    //Returns calculated TVOC reading
    Serial.print(mySensor.getTVOC());
    Serial.print("] millis[");
    Serial.print("]");
    Serial.println();
  }

  delay(10); //Don't spam the I2C bus
}
void baca_tanah() {
  psoil1_ = map(analogRead(psoil1), 0, 1023, 0, 100); psoil2_ = map(analogRead(psoil2), 0, 1023, 0, 100);
  psoil3_ = map(analogRead(psoil3), 0, 1023, 0, 100); psoil4_ = map(analogRead(psoil4), 0, 1023, 0, 100);
  psoil5_ = map(analogRead(psoil5), 0, 1023, 0, 100); psoil6_ = map(analogRead(psoil6), 0, 1023, 0, 100);
  psoil7_ = map(analogRead(psoil7), 0, 1023, 0, 100); psoil8_ = map(analogRead(psoil8), 0, 1023, 0, 100);
  psoil9_ = map(analogRead(psoil9), 0, 1023, 0, 100); psoil10_ = map(analogRead(psoil10), 0, 1023, 0, 100);
}
void jtson() {
  nexLoop(nex_listen_list);
  // Prepare a JSON payload string
  //payload = "<";
  if (countsend == 1) {
    /*payload = "{";
      payload += "\"volt\":"; payload += V; payload += ",";
      payload += "\"Kilo\":"; payload += KW; payload += ",";
      payload += "\"frekuensi\":"; payload += F; payload += ",";
      payload += "\"cospi\":"; payload += C; payload += ",";
      payload += "\"watt\":"; payload += W; payload += ",";
      payload += "\"arus\":"; payload += CU; payload += ",";
      payload += "\"biaya\":"; payload += ru; payload += ",";
      payload += "}";
      payload1 = ("<dat|" + payload + "|");*/
    delay(200);
    Serial.println(payload1);
  }
  else if (countsend == 2) {
    payload = "{";
    payload += "\"sch1\":"; payload += hh1; payload += ":"; payload += mm1; payload += "-"; payload += hh2; payload += ":"; payload += mm2;
    payload += ",";
    payload += "\"sch2\":"; payload += hh3; payload += ":"; payload += mm3; payload += "-"; payload += hh4; payload += ":"; payload += mm4;
    payload += ",";
    payload += "\"sch3\":"; payload += hh5; payload += ":"; payload += mm5; payload += "-"; payload += hh6; payload += ":"; payload += mm6;
    payload += ",";
    payload += "\"sch4\":"; payload += hh7; payload += ":"; payload += mm7; payload += "-"; payload += hh8; payload += ":"; payload += mm8;
    payload += ",";
    payload += "\"sch5\":"; payload += hh9; payload += ":"; payload += mm9; payload += "-"; payload += hh10; payload += ":"; payload += mm10;
    payload += ",";
    payload += "\"sch6\":"; payload += hh11; payload += ":"; payload += mm11; payload += "-"; payload += hh12; payload += ":"; payload += mm12;
    payload += "}";
    payload1 = ("<dat|" + payload + "|");
    delay(200);
    Serial.println(payload1);
  }
  else if (countsend == 3) {
    payload = "{";
    payload += "\"waktu\":"; payload += time_now2;
    payload += "}";
    payload1 = ("<dat|" + payload + "|");
    delay(200);
    Serial.println(payload1);
    countsend = 0;
  }
  nexLoop(nex_listen_list);
}
byte decToBcd(byte val) {
  return ((val / 10 * 16) + (val % 10));
}
byte bcdToDec(byte val) {
  return ((val / 16 * 10) + (val % 16));
}
void printTime() {
  char buffer[3];
  const char* AMPM = 0;
  membacaWaktu();
  hou = hour;
  mnit = minute;
}
void membacaWaktu() {
  Wire.beginTransmission(I2C_ADDRESS);
  Wire.write(byte(0));
  Wire.endTransmission();
  Wire.requestFrom(I2C_ADDRESS, 7);
  second = bcdToDec(Wire.read());
  minute = bcdToDec(Wire.read());
  hour = bcdToDec(Wire.read());
  weekday = bcdToDec(Wire.read());
  monthday = bcdToDec(Wire.read());
  month = bcdToDec(Wire.read());
  year = bcdToDec(Wire.read());
  /*second = rtc.getSeconds();
    minute = rtc.getMinutes();
    hour = rtc.getHours();
    weekday = rtc.getWeekDay();
    monthday = rtc.getDay();
    month = rtc.getMonth();
    year = rtc.getYear();*/
}
void parsi2()
{
  if (sLine.length() > 0) //&&recei2==true)
  {
    //    dat=true;
    Index1 = sLine.indexOf('<');
    Index2 = sLine.indexOf('#', Index1 + 1);
    Index3 = sLine.indexOf('#', Index2 + 1);
    Index4 = sLine.indexOf('#', Index3 + 1);
    Index5 = sLine.indexOf('#', Index4 + 1);
    Index6 = sLine.indexOf('#', Index5 + 1);
    Index7 = sLine.indexOf('#', Index6 + 1);
    Index8 = sLine.indexOf('#', Index7 + 1);
    Index9 = sLine.indexOf('#', Index8 + 1);
    secondValue = sLine.substring(Index1 + 1, Index2);
    thirdValue = sLine.substring(Index2 + 1, Index3);
    fourthValue = sLine.substring(Index3 + 1, Index4);
    fifthValue = sLine.substring(Index4 + 1, Index5);
    firstValue = sLine.substring(Index5 + 1, Index6);
    sixValue = sLine.substring(Index6 + 1, Index7);
    eigValue = sLine.substring(Index7 + 1, Index8);
    nineValue = sLine.substring(Index8 + 1, Index9);
    Serial.print("data 1:"); Serial.println(secondValue);
    Serial.print("data 2:"); Serial.println(thirdValue);
    Serial.print("data 3:"); Serial.println(fourthValue);
    Serial.print("data 4:"); Serial.println(fifthValue);
    Serial.print("data 5:"); Serial.println(firstValue);
    Serial.print("data 6:"); Serial.println(sixValue);
    Serial.print("data 7:"); Serial.println(eigValue);
    Serial.print("data 8:"); Serial.println(nineValue);
  }
}
void logi()
{
  if (secondValue == "sch1")
  {
    h1 = thirdValue.toInt();
    h2 = fourthValue.toInt();
    m1 = fifthValue.toInt();
    m2 = firstValue.toInt();
    //   rel[73] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h1);
    Serial.println(h2);
    Serial.println(m1);
    Serial.println(m2);
    //  Serial.println(rel[73]);
    saveeep();
  }
  else if (secondValue == "sch2")
  {
    h3 = thirdValue.toInt();
    h4 = fourthValue.toInt();
    m3 = fifthValue.toInt();
    m4 = firstValue.toInt();
    //    rel[74] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h3);
    Serial.println(h4);
    Serial.println(m3);
    Serial.println(m4);
    //  Serial.println(rel[74]);
    saveeep();
  }
  else if (secondValue == "sch3")
  {
    h5 = thirdValue.toInt();
    h6 = fourthValue.toInt();
    m5 = fifthValue.toInt();
    m6 = firstValue.toInt();
    // rel[75] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h5);
    Serial.println(h6);
    Serial.println(m5);
    Serial.println(m6);
    //  Serial.println(rel[75]);
    saveeep();
  }
  else if (secondValue == "sch4")
  {
    h7 = thirdValue.toInt();
    h8 = fourthValue.toInt();
    m7 = fifthValue.toInt();
    m8 = firstValue.toInt();
    //   rel[76] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h7);
    Serial.println(h8);
    Serial.println(m7);
    Serial.println(m8);
    //   Serial.println(rel[76]);
    saveeep();
  }
  else if (secondValue == "sch5")
  {
    h9 = thirdValue.toInt();
    h10 = fourthValue.toInt();
    m9 = fifthValue.toInt();
    m10 = firstValue.toInt();
    //  rel[77] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h9);
    Serial.println(h10);
    Serial.println(m9);
    Serial.println(m10);
    //   Serial.println(rel[77]);
    saveeep();
  }
  else if (secondValue == "sch6")
  {
    h11 = thirdValue.toInt();
    h12 = fourthValue.toInt();
    m11 = fifthValue.toInt();
    m12 = firstValue.toInt();
    //    rel[78] = sixValue.toInt();
    Serial.println("============");
    Serial.println(h11);
    Serial.println(h12);
    Serial.println(m11);
    Serial.println(m12);
    //    Serial.println(rel[78]);
    saveeep();
  }
  else if (secondValue == "WI")
  {
    logW = 2;
    _SSID1 = thirdValue.c_str();
    _SSID2 = fourthValue.c_str();
    _SSID3 = fifthValue.c_str();
    //================
    SSID1.setText(_SSID1);
    SSID2.setText(_SSID2);
    SSID3.setText(_SSID3);
    delay(400);
  }
  else if (secondValue == "DMAN") {
    bak_man1 = thirdValue.toFloat();
    nut_man1 = fourthValue.toFloat();
    jmman = fifthValue.toInt();
    mnman = firstValue.toInt();
    config.nut_man = nut_man1;
    config.bak_man = bak_man1;
    eeWrite(260, config);
    Serial.print("jadwal manual berhasil");
    Serial.print("nutrisi=");
    Serial.println(nut_man1);
    Serial.print("air baku=");
    Serial.println(bak_man1);
    MODEDOS = 0;
    saveeep();
  }
  else if (secondValue == "DAUT") {
    bak_aut1 = thirdValue.toFloat();
    nut_aut1 = fourthValue.toFloat();
    jmaut = fifthValue.toInt();
    mnaut = firstValue.toInt();
    config.nut_aut = nut_aut1;
    config.bak_aut = bak_aut1;
    eeWrite(260, config);
    Serial.print("jadwal auto berhasil");
    Serial.print("nutrisi=");
    Serial.println(nut_aut1);
    Serial.print("air baku=");
    Serial.println(bak_aut1);
    MODEDOS = 1;
    saveeep();
  } else if (secondValue == "UD") {
    sns_climate = thirdValue.toInt();
    saveeep();
  } else if (secondValue == "TA") {
    sns_tanah = thirdValue.toInt();
    saveeep();
  } else if (secondValue == "AI") {
    sns_water = thirdValue.toInt();
    saveeep();
  } else if (secondValue == "man") {
    ab0 = thirdValue.toFloat();
    am0 = fourthValue.toFloat();
    bntu = 20;
    ppman = 60;
    delay(30);
    ab = ab0;
    tim = am0 * pengali;
    airA2_2 = ab0;
    Serial.print("manual 1:");
    Serial.print("Nutrisi=");
    Serial.println(am0);
    Serial.print("Air Baku=");
    Serial.println(ab0);
    //MODEDOS = 0;
  } else if (secondValue == "aut") {
    ab = thirdValue.toFloat();
    au = fourthValue.toFloat();
    //valMix = 0;
    bntu = 20;
    airA2_2 = ab;
    ppman = 50;
    Serial.print("auto:");
    Serial.print("nutrisi=");
    Serial.println(au);
    Serial.print("air baku=");
    Serial.println(ab);
    //MODEDOS = 1;
    //saveeep();
  } else if (secondValue == "kel") {
    m43 = thirdValue.toInt();
    h44 = fourthValue.toInt();
    mmist = 1;
    Serial.print("batas atas kel=");
    Serial.println(m43);
    Serial.print("batas bawah kel=");
    Serial.println(h44);
    Serial.print("Mode=");
    Serial.println(mmist);
    saveeep();
  } else if (secondValue == "suh") {
    h42 = thirdValue.toInt();
    h43 = fourthValue.toInt();
    m44 = 1;
    Serial.print("batas atas suhu=");
    Serial.println(h42);
    Serial.print("batas bawah suhu=");
    Serial.println(h43);
    Serial.print("Mode=");
    Serial.println(m44);
    saveeep();
  } else if (secondValue == "KIP") {
    h45 = thirdValue.toInt();
    m44 = 0;
    Serial.print("Status fan=");
    Serial.println(h45);
    Serial.print("Mode=");
    Serial.println(m44);
    saveeep();
  } else if (secondValue == "MIS") {
    m45 = thirdValue.toInt();
    mmist = 0;
    Serial.print("Status misting=");
    Serial.println(m45);
    Serial.print("Mode=");
    Serial.println(m44);
    saveeep();
  } else if (secondValue == "inp") {
    input = thirdValue.toInt();
    Serial.print("air input=");
    Serial.println(input);
    ppman = 70;
  } else if (secondValue == "mix") {
    mixing = thirdValue.toInt();
    Serial.print("mixing=");
    Serial.println(mixing);
    ppman = 70;
  } else if (secondValue == "pump") {
    pompa = thirdValue.toInt();
    Serial.print("pompa=");
    Serial.println(pompa);
    ppman = 70;
  }
  else if (secondValue == "mqtt") {
    mq = thirdValue.toInt();
    if (mq == 1) {
      logW = 2;
      //inter = 20;
      Serial.println("mqtt connect");
      h37 = 1;
      saveeep();
    } else if (mq == 0) {
      //logW = 1;
      //inter = 30;
      Serial.println("mqtt disconnect");
      h37 = 0;
      saveeep();
    }
  }
  else if (secondValue == "ws" || secondValue == "wkt")
  {
    if (thirdValue == "Minggu" || thirdValue == "0")
    {
      har = 1;
    }
    else if (thirdValue == "Senin" || thirdValue == "1")
    {
      har = 2;
    }
    else if (thirdValue == "Selasa" || thirdValue == "2")
    {
      har = 3;
    }
    else if (thirdValue == "Rabu" || thirdValue == "3")
    {
      har = 4;
    }
    else if (thirdValue == "Kamis" || thirdValue == "4")
    {
      har = 5;
    }
    else if (thirdValue == "Jumat" || thirdValue == "5")
    {
      har = 6;
    }
    else if (thirdValue == "Sabtu" || thirdValue == "6")
    {
      har = 7;
    }
    tang1 = fourthValue.toInt();
    bul1 = fifthValue.toInt() + 1;
    tah1 = firstValue.toInt();
    if (tah1 >= 2023)
    {
      tah1 = (firstValue.toInt() - 2000);
    }
    else if (tah1 >= 23)
    {
      tah1 = firstValue.toInt();
    }
    jam1 = sixValue.toInt();
    menit1 = eigValue.toInt();
    Serial.println("================");
    Serial.println(har);
    Serial.println(jam1);
    Serial.println(menit1);
    Serial.println(tang1);
    Serial.println(bul1);
    Serial.println(tah1);

    byte wak[7] = {jam1, menit1, har, tang1, bul1, tah1};
    /*rtc.setHours(wak[0]);
      rtc.setMinutes(wak[1]);
      rtc.setSeconds(0);

      // Set the date
      rtc.setWeekDay(wak[2]);
      rtc.setDay(wak[3]);
      rtc.setMonth(wak[4]);
      rtc.setYear(wak[5]);*/
    Wire.beginTransmission(I2C_ADDRESS);
    Wire.write(byte(0));
    Wire.write(decToBcd(0)); // second
    Wire.write(decToBcd(wak[1])); // minute
    Wire.write(decToBcd(wak[0])); // hour
    Wire.write(decToBcd(wak[2]));  // weekday
    Wire.write(decToBcd(wak[3])); // date
    Wire.write(decToBcd(wak[4]));  // month
    Wire.write(decToBcd(wak[5])); // year
    Wire.write(byte(0));
    Wire.endTransmission();
    delay(30);
  }
}
void updat() {
  hh1 = String(h1); if (hh1 == "70") {
    hh1 = "_";
  } else {
    hh1 = hh1;
  } mm1 = String(h2); if (mm1 == "70") {
    mm1 = "_";
  } else {
    mm1 = mm1;
  } hh2 = String(m1); if (hh2 == "70") {
    hh2 = "_";
  } else {
    hh2 = hh2;
  } mm2 = String(m2); if (mm2 == "70") {
    mm2 = "_";
  } else {
    mm2 = mm2;
  }
  hh3 = String(h3); if (hh3 == "70") {
    hh3 = "_";
  } else {
    hh3 = hh3;
  } mm3 = String(h4); if (mm3 == "70") {
    mm3 = "_";
  } else {
    mm3 = mm3;
  } hh4 = String(m3); if (hh4 == "70") {
    hh4 = "_";
  } else {
    hh4 = hh4;
  } mm4 = String(m4); if (mm4 == "70") {
    mm4 = "_";
  } else {
    mm4 = mm4;
  }
  hh5 = String(h5); if (hh5 == "70") {
    hh5 = "_";
  } else {
    hh5 = hh5;
  } mm5 = String(h6); if (mm5 == "70") {
    mm5 = "_";
  } else {
    mm5 = mm5;
  } hh6 = String(m5); if (hh6 == "70") {
    hh6 = "_";
  } else {
    hh6 = hh6;
  } mm6 = String(m6); if (mm6 == "70") {
    mm6 = "_";
  } else {
    mm6 = mm6;
  }
  hh7 = String(h7); if (hh7 == "70") {
    hh7 = "_";
  } else {
    hh7 = hh7;
  } mm7 = String(h8); if (mm7 == "70") {
    mm7 = "_";
  } else {
    mm7 = mm7;
  } hh8 = String(m7); if (hh8 == "70") {
    hh8 = "_";
  } else {
    hh8 = hh8;
  } mm8 = String(m8); if (mm8 == "70") {
    mm8 = "_";
  } else {
    mm8 = mm8;
  }
  hh9 = String(h9); if (hh9 == "70") {
    hh9 = "_";
  } else {
    hh9 = hh9;
  } mm9 = String(h10); if (mm9 == "70") {
    mm9 = "_";
  } else {
    mm9 = mm9;
  } hh10 = String(m9); if (hh10 == "70") {
    hh10 = "_";
  } else {
    hh10 = hh10;
  } mm10 = String(m10); if (mm10 == "70") {
    mm10 = "_";
  } else {
    mm10 = mm10;
  }
  hh11 = String(h11); if (hh11 == "70") {
    hh11 = "_";
  } else {
    hh11 = hh11;
  } mm11 = String(h12); if (mm11 == "70") {
    mm11 = "_";
  } else {
    mm11 = mm11;
  } hh12 = String(m11); if (hh12 == "70") {
    hh12 = "_";
  } else {
    hh12 = hh12;
  } mm12 = String(m12); if (mm12 == "70") {
    mm12 = "_";
  } else {
    mm12 = mm12;
  }
  jmman_ = String(jmman); if (jmman_ == "70") {
    jmman_ = "_";
  } else {
    jmman_ = jmman_;
  }
  mnman_ = String(mnman); if (mnman_ == "70") {
    mnman_ = "_";
  } else {
    mnman_ = mnman_;
  }
  jmaut_ = String(jmaut); if (jmaut_ == "70") {
    jmaut_ = "_";
  } else {
    jmaut_ = jmaut_;
  }
  mnaut_ = String(mnaut); if (mnaut_ == "70") {
    mnaut_ = "_";
  } else {
    mnaut_ = mnaut_;
  }
}
void readeep()
{
  h1 = readEEPROM(0, EEPROM_I2C_ADDRESS);
  h2 = readEEPROM(1, EEPROM_I2C_ADDRESS);
  h3 = readEEPROM(2, EEPROM_I2C_ADDRESS);
  h4 = readEEPROM(3, EEPROM_I2C_ADDRESS);
  h5 = readEEPROM(4, EEPROM_I2C_ADDRESS);
  h6 = readEEPROM(5, EEPROM_I2C_ADDRESS);
  h7 = readEEPROM(6, EEPROM_I2C_ADDRESS);
  h8 = readEEPROM(7, EEPROM_I2C_ADDRESS);
  h9 = readEEPROM(8, EEPROM_I2C_ADDRESS);
  h10 = readEEPROM(9, EEPROM_I2C_ADDRESS);
  h11 = readEEPROM(10, EEPROM_I2C_ADDRESS);
  h12 = readEEPROM(11, EEPROM_I2C_ADDRESS);
  h13 = readEEPROM(12, EEPROM_I2C_ADDRESS);
  h14 = readEEPROM(13, EEPROM_I2C_ADDRESS);
  h15 = readEEPROM(14, EEPROM_I2C_ADDRESS);
  h16 = readEEPROM(15, EEPROM_I2C_ADDRESS);
  h17 = readEEPROM(16, EEPROM_I2C_ADDRESS);
  h18 = readEEPROM(17, EEPROM_I2C_ADDRESS);
  h19 = readEEPROM(18, EEPROM_I2C_ADDRESS);
  h20 = readEEPROM(19, EEPROM_I2C_ADDRESS);
  h21 = readEEPROM(20, EEPROM_I2C_ADDRESS);
  h22 = readEEPROM(21, EEPROM_I2C_ADDRESS);
  h23 = readEEPROM(22, EEPROM_I2C_ADDRESS);
  h24 = readEEPROM(23, EEPROM_I2C_ADDRESS);
  h25 = readEEPROM(24, EEPROM_I2C_ADDRESS);
  h26 = readEEPROM(25, EEPROM_I2C_ADDRESS);
  h27 = readEEPROM(26, EEPROM_I2C_ADDRESS);
  h28 = readEEPROM(27, EEPROM_I2C_ADDRESS);
  h29 = readEEPROM(28, EEPROM_I2C_ADDRESS);
  h30 = readEEPROM(29, EEPROM_I2C_ADDRESS);
  h31 = readEEPROM(30, EEPROM_I2C_ADDRESS);
  h32 = readEEPROM(31, EEPROM_I2C_ADDRESS);
  //=======================================
  m1 = readEEPROM(32, EEPROM_I2C_ADDRESS);
  m2 = readEEPROM(33, EEPROM_I2C_ADDRESS);
  m3 = readEEPROM(34, EEPROM_I2C_ADDRESS);
  m4 = readEEPROM(35, EEPROM_I2C_ADDRESS);
  m5 = readEEPROM(36, EEPROM_I2C_ADDRESS);
  m6 = readEEPROM(37, EEPROM_I2C_ADDRESS);
  m7 = readEEPROM(38, EEPROM_I2C_ADDRESS);
  m8 = readEEPROM(39, EEPROM_I2C_ADDRESS);
  m9 = readEEPROM(40, EEPROM_I2C_ADDRESS);
  m10 = readEEPROM(41, EEPROM_I2C_ADDRESS);
  m11 = readEEPROM(42, EEPROM_I2C_ADDRESS);
  m12 = readEEPROM(43, EEPROM_I2C_ADDRESS);
  m13 = readEEPROM(44, EEPROM_I2C_ADDRESS);
  m14 = readEEPROM(45, EEPROM_I2C_ADDRESS);
  m15 = readEEPROM(46, EEPROM_I2C_ADDRESS);
  m16 = readEEPROM(47, EEPROM_I2C_ADDRESS);
  m17 = readEEPROM(48, EEPROM_I2C_ADDRESS);
  m18 = readEEPROM(49, EEPROM_I2C_ADDRESS);
  m19 = readEEPROM(50, EEPROM_I2C_ADDRESS);
  m20 = readEEPROM(51, EEPROM_I2C_ADDRESS);
  m21 = readEEPROM(52, EEPROM_I2C_ADDRESS);
  m22 = readEEPROM(53, EEPROM_I2C_ADDRESS);
  m23 = readEEPROM(54, EEPROM_I2C_ADDRESS);
  m24 = readEEPROM(55, EEPROM_I2C_ADDRESS);
  m25 = readEEPROM(56, EEPROM_I2C_ADDRESS);
  m26 = readEEPROM(57, EEPROM_I2C_ADDRESS);
  m27 = readEEPROM(58, EEPROM_I2C_ADDRESS);
  m28 = readEEPROM(59, EEPROM_I2C_ADDRESS);
  m29 = readEEPROM(60, EEPROM_I2C_ADDRESS);
  m30 = readEEPROM(61, EEPROM_I2C_ADDRESS);
  m31 = readEEPROM(62, EEPROM_I2C_ADDRESS);
  m32 = readEEPROM(63, EEPROM_I2C_ADDRESS);
  //=======================================
  //=======================================
  h33 = readEEPROM(64, EEPROM_I2C_ADDRESS);
  h34 = readEEPROM(65, EEPROM_I2C_ADDRESS);
  h35 = readEEPROM(66, EEPROM_I2C_ADDRESS);
  h36 = readEEPROM(67, EEPROM_I2C_ADDRESS);
  h37 = readEEPROM(68, EEPROM_I2C_ADDRESS);
  h38 = readEEPROM(69, EEPROM_I2C_ADDRESS);
  h39 = readEEPROM(70, EEPROM_I2C_ADDRESS);
  h40 = readEEPROM(71, EEPROM_I2C_ADDRESS);
  h41 = readEEPROM(72, EEPROM_I2C_ADDRESS);
  jmman = readEEPROM(73, EEPROM_I2C_ADDRESS);
  mnman = readEEPROM(74, EEPROM_I2C_ADDRESS);
  jmaut = readEEPROM(75, EEPROM_I2C_ADDRESS);
  mnaut = readEEPROM(76, EEPROM_I2C_ADDRESS);
  sns_tanah = readEEPROM(77, EEPROM_I2C_ADDRESS);
  sns_climate = readEEPROM(78, EEPROM_I2C_ADDRESS);
  sns_water = readEEPROM(79, EEPROM_I2C_ADDRESS);
  MODEDOS = readEEPROM(80, EEPROM_I2C_ADDRESS);
  mmist = readEEPROM(81, EEPROM_I2C_ADDRESS);
  m43 = readEEPROM(82, EEPROM_I2C_ADDRESS);
  h42 = readEEPROM(83, EEPROM_I2C_ADDRESS);
  h43 = readEEPROM(84, EEPROM_I2C_ADDRESS);
  h44 = readEEPROM(85, EEPROM_I2C_ADDRESS);
}
void saveeep()
{
  writeEEPROM(0, h1, EEPROM_I2C_ADDRESS);
  writeEEPROM(1, h2, EEPROM_I2C_ADDRESS);
  writeEEPROM(2, h3, EEPROM_I2C_ADDRESS);
  writeEEPROM(3, h4, EEPROM_I2C_ADDRESS);
  writeEEPROM(4, h5, EEPROM_I2C_ADDRESS);
  writeEEPROM(5, h6, EEPROM_I2C_ADDRESS);
  writeEEPROM(6, h7, EEPROM_I2C_ADDRESS);
  writeEEPROM(7, h8, EEPROM_I2C_ADDRESS);
  writeEEPROM(8, h9, EEPROM_I2C_ADDRESS);
  writeEEPROM(9, h10, EEPROM_I2C_ADDRESS);
  writeEEPROM(10, h11, EEPROM_I2C_ADDRESS);
  writeEEPROM(11, h12, EEPROM_I2C_ADDRESS);
  writeEEPROM(12, h13, EEPROM_I2C_ADDRESS);
  writeEEPROM(13, h14, EEPROM_I2C_ADDRESS);
  writeEEPROM(14, h15, EEPROM_I2C_ADDRESS);
  writeEEPROM(15, h16, EEPROM_I2C_ADDRESS);
  writeEEPROM(16, h17, EEPROM_I2C_ADDRESS);
  writeEEPROM(17, h18, EEPROM_I2C_ADDRESS);
  writeEEPROM(18, h19, EEPROM_I2C_ADDRESS);
  writeEEPROM(19, h20, EEPROM_I2C_ADDRESS);
  writeEEPROM(20, h21, EEPROM_I2C_ADDRESS);
  writeEEPROM(21, h22, EEPROM_I2C_ADDRESS);
  writeEEPROM(22, h23, EEPROM_I2C_ADDRESS);
  writeEEPROM(23, h24, EEPROM_I2C_ADDRESS);
  writeEEPROM(24, h25, EEPROM_I2C_ADDRESS);
  writeEEPROM(25, h26, EEPROM_I2C_ADDRESS);
  writeEEPROM(26, h27, EEPROM_I2C_ADDRESS);
  writeEEPROM(27, h28, EEPROM_I2C_ADDRESS);
  writeEEPROM(28, h29, EEPROM_I2C_ADDRESS);
  writeEEPROM(29, h30, EEPROM_I2C_ADDRESS);
  writeEEPROM(30, h31, EEPROM_I2C_ADDRESS);
  writeEEPROM(31, h32, EEPROM_I2C_ADDRESS);
  //======================================
  writeEEPROM(32, m1, EEPROM_I2C_ADDRESS);
  writeEEPROM(33, m2, EEPROM_I2C_ADDRESS);
  writeEEPROM(34, m3, EEPROM_I2C_ADDRESS);
  writeEEPROM(35, m4, EEPROM_I2C_ADDRESS);
  writeEEPROM(36, m5, EEPROM_I2C_ADDRESS);
  writeEEPROM(37, m6, EEPROM_I2C_ADDRESS);
  writeEEPROM(38, m7, EEPROM_I2C_ADDRESS);
  writeEEPROM(39, m8, EEPROM_I2C_ADDRESS);
  writeEEPROM(40, m9, EEPROM_I2C_ADDRESS);
  writeEEPROM(41, m10, EEPROM_I2C_ADDRESS);
  writeEEPROM(42, m11, EEPROM_I2C_ADDRESS);
  writeEEPROM(43, m12, EEPROM_I2C_ADDRESS);
  writeEEPROM(44, m13, EEPROM_I2C_ADDRESS);
  writeEEPROM(45, m14, EEPROM_I2C_ADDRESS);
  writeEEPROM(46, m15, EEPROM_I2C_ADDRESS);
  writeEEPROM(47, m16, EEPROM_I2C_ADDRESS);
  writeEEPROM(48, m17, EEPROM_I2C_ADDRESS);
  writeEEPROM(49, m18, EEPROM_I2C_ADDRESS);
  writeEEPROM(50, m19, EEPROM_I2C_ADDRESS);
  writeEEPROM(51, m20, EEPROM_I2C_ADDRESS);
  writeEEPROM(52, m21, EEPROM_I2C_ADDRESS);
  writeEEPROM(53, m22, EEPROM_I2C_ADDRESS);
  writeEEPROM(54, m23, EEPROM_I2C_ADDRESS);
  writeEEPROM(55, m24, EEPROM_I2C_ADDRESS);
  writeEEPROM(56, m25, EEPROM_I2C_ADDRESS);
  writeEEPROM(57, m26, EEPROM_I2C_ADDRESS);
  writeEEPROM(58, m27, EEPROM_I2C_ADDRESS);
  writeEEPROM(59, m28, EEPROM_I2C_ADDRESS);
  writeEEPROM(60, m29, EEPROM_I2C_ADDRESS);
  writeEEPROM(61, m30, EEPROM_I2C_ADDRESS);
  writeEEPROM(62, m31, EEPROM_I2C_ADDRESS);
  writeEEPROM(63, m32, EEPROM_I2C_ADDRESS);
  //=====================================
  writeEEPROM(64, h33, EEPROM_I2C_ADDRESS);
  writeEEPROM(65, h34, EEPROM_I2C_ADDRESS);
  writeEEPROM(66, h35, EEPROM_I2C_ADDRESS);
  writeEEPROM(67, h36, EEPROM_I2C_ADDRESS);
  writeEEPROM(68, h37, EEPROM_I2C_ADDRESS);
  writeEEPROM(69, h38, EEPROM_I2C_ADDRESS);
  writeEEPROM(70, h39, EEPROM_I2C_ADDRESS);
  writeEEPROM(71, h40, EEPROM_I2C_ADDRESS);
  writeEEPROM(72, h41, EEPROM_I2C_ADDRESS);
  writeEEPROM(73, jmman, EEPROM_I2C_ADDRESS);
  writeEEPROM(74, mnman, EEPROM_I2C_ADDRESS);
  writeEEPROM(75, jmaut, EEPROM_I2C_ADDRESS);
  writeEEPROM(76, mnaut, EEPROM_I2C_ADDRESS);
  writeEEPROM(77, sns_tanah, EEPROM_I2C_ADDRESS);
  writeEEPROM(78, sns_climate, EEPROM_I2C_ADDRESS);
  writeEEPROM(79, sns_water, EEPROM_I2C_ADDRESS);
  writeEEPROM(80, MODEDOS, EEPROM_I2C_ADDRESS);
  writeEEPROM(81, mmist, EEPROM_I2C_ADDRESS);
  writeEEPROM(82, m43, EEPROM_I2C_ADDRESS);
  writeEEPROM(83, h42, EEPROM_I2C_ADDRESS);
  writeEEPROM(84, h43, EEPROM_I2C_ADDRESS);
  writeEEPROM(85, h44, EEPROM_I2C_ADDRESS);
}
