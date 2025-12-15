"""
SmartAgri IoT - Device Status Simulator (MQTT)
==============================================
Script Python untuk mensimulasikan device yang mengirim:
- Status output (on/off, nilai)
- Jadwal (time schedules dan sensor rules)

Cara pakai:
1. Install dependencies: pip install paho-mqtt
2. Ganti TOKEN sesuai device kamu
3. Jalankan: python dummy_device_status_mqtt.py
"""

import paho.mqtt.client as mqtt
import json
import random
import time
from datetime import datetime

# ============================================
# KONFIGURASI - GANTI SESUAI KEBUTUHAN
# ============================================

MQTT_BROKER = "broker.hivemq.com"
MQTT_PORT = 1883

# Token device (dapatkan dari admin panel)
TOKEN = "pmGQfWN4WyjK2eu1"

# MQTT Topic untuk sensor data (sesuai device di database)
MQTT_SENSOR_TOPIC = "ngangngong/bopal/aws1"

# Interval pengiriman status (dalam detik)
SEND_INTERVAL = 5

# ============================================
# SIMULASI DATA DEVICE
# ============================================

# State output yang tersimpan di device (sesuai screenshot)
output_states = {
    "pump_1": {"value": 0, "label": "Pompa Air", "type": "boolean"},
    "fan_1": {"value": 0, "label": "Kipas/Fan", "type": "boolean"},
    "valve_1": {"value": 0, "label": "Katup/Valve", "type": "boolean"},
    "led_1": {"value": 0, "label": "LED", "type": "boolean"},
}

# Jadwal yang tersimpan di device
device_schedules = [
    {
        "id": 1,
        "output": "fan_1",
        "type": "time",
        "days": [0, 1, 2, 3, 4, 5, 6],  # Setiap hari
        "on": "06:00",
        "off": "18:00",
        "value": 1,
        "enabled": True
    },
    {
        "id": 2,
        "output": "valve_1",
        "type": "sensor",
        "sensor": "temperature_1",
        "operator": ">",
        "threshold": 30.0,
        "hysteresis": 2.0,
        "action_value": 1,
        "enabled": True
    }
]

# Sensor values (sesuai konfigurasi device Smart GH)
sensor_values = {
    "temperature_1": 28.5,
    "temperature_2": 29.0,
    "humidity_1": 65.0,
    "humidity_2": 68.0,
    "soil_moisture": 45.0,
    "light_intensity": 800.0
}

# ============================================
# MQTT CALLBACKS
# ============================================

def on_connect(client, userdata, flags, rc, properties=None):
    """Callback saat terhubung ke broker"""
    if rc == 0:
        print("✅ Terhubung ke MQTT Broker!")
        
        # Subscribe untuk menerima commands dari web (format: {mqtt_topic}/control)
        command_topic = f"{MQTT_SENSOR_TOPIC}/control"
        client.subscribe(command_topic, qos=1)
        print(f"📡 Listening commands on: {command_topic}")
    else:
        print(f"❌ Gagal terhubung, kode: {rc}")

def on_message(client, userdata, msg):
    """Callback saat menerima pesan (command dari web)"""
    timestamp = datetime.now().strftime("%H:%M:%S")
    print(f"\n[{timestamp}] 📨 Command diterima!")
    print(f"   Topic: {msg.topic}")
    
    try:
        data = json.loads(msg.payload.decode())
        print(f"   Data: {json.dumps(data, indent=2)}")
        
        cmd_type = data.get("type", "")
        
        if cmd_type == "manual_control":
            output_name = data.get("output_name")
            value = data.get("value")
            if output_name in output_states:
                output_states[output_name]["value"] = value
                print(f"   ✅ Output {output_name} diubah ke: {value}")
        
        elif cmd_type == "automation_config":
            # Web mengirim konfigurasi automation baru
            configs = data.get("configs", [])
            print(f"   📋 Menerima {len(configs)} automation configs")
            # Simulasi: replace device schedules
            device_schedules.clear()
            device_schedules.extend(configs)
            print(f"   ✅ Schedules updated!")
        
        elif cmd_type == "automation_delete":
            config_id = data.get("config_id")
            for i, sched in enumerate(device_schedules):
                if sched.get("id") == config_id:
                    device_schedules.pop(i)
                    print(f"   ✅ Schedule {config_id} deleted!")
                    break
                    
    except Exception as e:
        print(f"   ❌ Error: {e}")

# ============================================
# FUNGSI UTAMA
# ============================================

def simulate_automation():
    """Simulasi logika automation di device"""
    global output_states, sensor_values
    
    # Generate sensor values random (sesuai konfigurasi device)
    sensor_values["temperature_1"] = round(random.uniform(25.0, 35.0), 1)
    sensor_values["temperature_2"] = round(random.uniform(25.0, 35.0), 1)
    sensor_values["humidity_1"] = round(random.uniform(50.0, 80.0), 1)
    sensor_values["humidity_2"] = round(random.uniform(50.0, 80.0), 1)
    sensor_values["soil_moisture"] = round(random.uniform(30.0, 70.0), 1)
    sensor_values["light_intensity"] = round(random.uniform(500.0, 1200.0), 1)
    
    current_time = datetime.now().strftime("%H:%M")
    current_day = datetime.now().weekday()
    
    for schedule in device_schedules:
        if not schedule.get("enabled", True):
            continue
            
        output_name = schedule.get("output")
        if output_name not in output_states:
            continue
        
        if schedule["type"] == "time":
            on_time = schedule.get("on", "00:00")
            off_time = schedule.get("off", "23:59")
            days = schedule.get("days", [0,1,2,3,4,5,6])
            
            if current_day in days:
                if on_time <= current_time < off_time:
                    output_states[output_name]["value"] = schedule.get("value", 1)
                else:
                    output_states[output_name]["value"] = 0
        
        elif schedule["type"] == "sensor":
            sensor_name = schedule.get("sensor")
            operator = schedule.get("operator", ">")
            threshold = schedule.get("threshold", 0)
            action_value = schedule.get("action_value", 1)
            
            sensor_val = sensor_values.get(sensor_name, 0)
            
            condition_met = False
            if operator == ">" and sensor_val > threshold:
                condition_met = True
            elif operator == "<" and sensor_val < threshold:
                condition_met = True
            elif operator == ">=" and sensor_val >= threshold:
                condition_met = True
            elif operator == "<=" and sensor_val <= threshold:
                condition_met = True
            elif operator == "==" and sensor_val == threshold:
                condition_met = True
            
            output_states[output_name]["value"] = action_value if condition_met else 0

def generate_status_payload():
    """Generate payload status untuk dikirim ke web"""
    return {
        "token": TOKEN,
        "outputs": output_states,
        "schedules": device_schedules,
        "sensors": sensor_values,
        "timestamp": datetime.now().isoformat()
    }

def print_header():
    """Print header program"""
    print("=" * 60)
    print("🌱 SmartAgri IoT - Device Status Simulator")
    print("=" * 60)
    print(f"📡 Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"🔑 Token: {TOKEN}")
    print(f"⏱️  Interval: {SEND_INTERVAL} detik")
    print("-" * 60)
    print("Device akan mengirim status output + jadwal secara berkala")
    print("Tekan Ctrl+C untuk berhenti\n")

def print_status():
    """Print current status"""
    print("\n📊 Current Output States:")
    for name, data in output_states.items():
        status = "ON 🟢" if data["value"] else "OFF 🔴"
        print(f"   • {data['label']}: {status}")
    
    print(f"\n🌡️ Sensor Values:")
    for name, value in sensor_values.items():
        print(f"   • {name}: {value}")

def main():
    """Fungsi utama"""
    print_header()
    
    # Setup MQTT client
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
    client.on_connect = on_connect
    client.on_message = on_message
    
    print("🔌 Menghubungkan ke MQTT broker...")
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
    except Exception as e:
        print(f"❌ Gagal terhubung: {e}")
        return
    
    time.sleep(2)
    
    counter = 0
    status_topic = MQTT_SENSOR_TOPIC  # Gunakan topic yang sama dengan device di database
    
    try:
        while True:
            counter += 1
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            
            # Simulasi automation logic
            simulate_automation()
            
            # Generate payload
            payload = generate_status_payload()
            json_data = json.dumps(payload)
            
            print(f"\n[{timestamp}] 📤 Mengirim status #{counter}...")
            print(f"   Topic: {status_topic}")
            
            # Publish status
            result = client.publish(status_topic, json_data, qos=1)
            
            if result.rc == mqtt.MQTT_ERR_SUCCESS:
                print(f"   ✅ Status terkirim!")
            else:
                print(f"   ❌ Gagal publish")
            
            print_status()
            
            time.sleep(SEND_INTERVAL)
            
    except KeyboardInterrupt:
        print("\n\n👋 Program dihentikan.")
        print(f"   Total status terkirim: {counter}")
    finally:
        client.loop_stop()
        client.disconnect()
        print("🔌 Koneksi MQTT ditutup.")

if __name__ == "__main__":
    main()
