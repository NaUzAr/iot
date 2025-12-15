<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display MQTT protocol documentation
     */
    public function mqtt()
    {
        // Example payloads for documentation
        $examples = [
            'time_based' => [
                'type' => 'automation_config',
                'configs' => [
                    [
                        'id' => 1,
                        'name' => 'Weekday Watering',
                        'output_name' => 'pump',
                        'automation_type' => 'time',
                        'enabled' => true,
                        'schedule' => [
                            'days' => [1, 2, 3, 4, 5],
                            'start_time' => '06:00',
                            'end_time' => '18:00',
                            'value' => 1,
                        ],
                    ],
                ],
            ],
            'sensor_single' => [
                'type' => 'automation_config',
                'configs' => [
                    [
                        'id' => 2,
                        'name' => 'Temperature Control',
                        'output_name' => 'fan',
                        'automation_type' => 'sensor',
                        'enabled' => true,
                        'conditions' => [
                            'logic' => 'AND',
                            'sensors' => [
                                [
                                    'sensor_name' => 'temperature',
                                    'operator' => '>',
                                    'threshold' => 30,
                                    'hysteresis' => 2,
                                ],
                            ],
                            'action_value' => 1,
                        ],
                    ],
                ],
            ],
            'sensor_multi' => [
                'type' => 'automation_config',
                'configs' => [
                    [
                        'id' => 3,
                        'name' => 'Climate Control',
                        'output_name' => 'fan',
                        'automation_type' => 'sensor',
                        'enabled' => true,
                        'conditions' => [
                            'logic' => 'AND',
                            'sensors' => [
                                [
                                    'sensor_name' => 'temperature',
                                    'operator' => '>',
                                    'threshold' => 30,
                                    'hysteresis' => 2,
                                ],
                                [
                                    'sensor_name' => 'humidity',
                                    'operator' => '<',
                                    'threshold' => 60,
                                    'hysteresis' => 5,
                                ],
                            ],
                            'action_value' => 1,
                        ],
                    ],
                ],
            ],
            'delete_command' => [
                'type' => 'automation_delete',
                'config_id' => 1,
                'timestamp' => '2025-12-14T12:00:00Z',
            ],
            'status_request' => [
                'type' => 'status_request',
                'timestamp' => '2025-12-14T12:00:00Z',
            ],
            'device_status_response' => [
                'type' => 'automation_status',
                'timestamp' => '2025-12-14T12:00:00Z',
                'active_automations' => [
                    [
                        'config_id' => 1,
                        'output_name' => 'pump',
                        'status' => 'active',
                        'reason' => 'schedule_match',
                    ],
                ],
                'outputs' => [
                    'pump' => 1,
                    'fan' => 0,
                ],
            ],
        ];

        return view('documentation.mqtt', compact('examples'));
    }

    /**
     * Display ESP32 MQTT setup documentation
     */
    public function esp32()
    {
        // Example Arduino code for ESP32
        $sensorCode = <<<'CODE'
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

// MQTT Broker
const char* mqtt_server = "broker.hivemq.com";
const int mqtt_port = 1883;

// Device Configuration (dari Admin Panel)
const char* DEVICE_TOKEN = "pmGQfWN4WyjK2eu1";
const char* MQTT_TOPIC = "ngangngong/bopal/aws1";

WiFiClient espClient;
PubSubClient client(espClient);

// Sensor pins
#define DHT_PIN 4
#define SOIL_PIN 34
#define LDR_PIN 35

void setup() {
    Serial.begin(115200);
    setup_wifi();
    client.setServer(mqtt_server, mqtt_port);
}

void setup_wifi() {
    delay(10);
    Serial.println("Connecting to WiFi...");
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\nWiFi connected!");
}

void reconnect() {
    while (!client.connected()) {
        String clientId = "ESP32-" + String(random(0xffff), HEX);
        if (client.connect(clientId.c_str())) {
            Serial.println("MQTT Connected!");
        } else {
            delay(5000);
        }
    }
}

void publishSensorData() {
    StaticJsonDocument<256> doc;
    
    // Add token for identification
    doc["token"] = DEVICE_TOKEN;
    
    // Read sensors and add to JSON
    doc["temperature_1"] = readTemperature();
    doc["humidity_1"] = readHumidity();
    doc["soil_moisture"] = analogRead(SOIL_PIN) / 40.95;
    doc["light_intensity"] = analogRead(LDR_PIN);
    
    char jsonBuffer[256];
    serializeJson(doc, jsonBuffer);
    
    client.publish(MQTT_TOPIC, jsonBuffer);
    Serial.println("Data sent: " + String(jsonBuffer));
}

void loop() {
    if (!client.connected()) {
        reconnect();
    }
    client.loop();
    
    publishSensorData();
    delay(5000); // Send every 5 seconds
}
CODE;

        $outputCode = <<<'CODE'
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// Device Configuration
const char* DEVICE_TOKEN = "pmGQfWN4WyjK2eu1";
const char* MQTT_TOPIC = "ngangngong/bopal/aws1";

// Output pins
#define PUMP_PIN 25
#define FAN_PIN 26
#define VALVE_PIN 27
#define LED_PIN 32

// Output states
bool pump_state = false;
bool fan_state = false;
bool valve_state = false;
bool led_state = false;

// Schedule storage
struct Schedule {
    int id;
    String output;
    String type;
    String on_time;
    String off_time;
    bool enabled;
};
Schedule schedules[10];
int scheduleCount = 0;

WiFiClient espClient;
PubSubClient client(espClient);

void setup() {
    Serial.begin(115200);
    
    // Setup output pins
    pinMode(PUMP_PIN, OUTPUT);
    pinMode(FAN_PIN, OUTPUT);
    pinMode(VALVE_PIN, OUTPUT);
    pinMode(LED_PIN, OUTPUT);
    
    setup_wifi();
    client.setServer(mqtt_server, mqtt_port);
    client.setCallback(mqttCallback);
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
    String message;
    for (int i = 0; i < length; i++) {
        message += (char)payload[i];
    }
    
    StaticJsonDocument<512> doc;
    deserializeJson(doc, message);
    
    String type = doc["type"];
    
    if (type == "manual_control") {
        String output = doc["output_name"];
        int value = doc["value"];
        controlOutput(output, value);
    }
    else if (type == "automation_config") {
        // Store schedules in device memory
        JsonArray configs = doc["configs"];
        scheduleCount = 0;
        for (JsonObject config : configs) {
            schedules[scheduleCount].id = config["id"];
            schedules[scheduleCount].output = config["output_name"].as<String>();
            schedules[scheduleCount].enabled = config["enabled"];
            scheduleCount++;
        }
        Serial.println("Schedules updated!");
    }
}

void controlOutput(String output, int value) {
    if (output == "pump_1") {
        pump_state = value;
        digitalWrite(PUMP_PIN, value);
    } else if (output == "fan_1") {
        fan_state = value;
        digitalWrite(FAN_PIN, value);
    } else if (output == "valve_1") {
        valve_state = value;
        digitalWrite(VALVE_PIN, value);
    } else if (output == "led_1") {
        led_state = value;
        digitalWrite(LED_PIN, value);
    }
}

void publishStatus() {
    StaticJsonDocument<512> doc;
    doc["token"] = DEVICE_TOKEN;
    
    JsonObject outputs = doc.createNestedObject("outputs");
    outputs["pump_1"]["value"] = pump_state ? 1 : 0;
    outputs["fan_1"]["value"] = fan_state ? 1 : 0;
    outputs["valve_1"]["value"] = valve_state ? 1 : 0;
    outputs["led_1"]["value"] = led_state ? 1 : 0;
    
    char buffer[512];
    serializeJson(doc, buffer);
    client.publish(MQTT_TOPIC, buffer);
}

void loop() {
    if (!client.connected()) reconnect();
    client.loop();
    
    checkSchedules();  // Check time-based schedules
    publishStatus();   // Send status to web
    delay(5000);
}
CODE;

        return view('documentation.esp32', compact('sensorCode', 'outputCode'));
    }
}
