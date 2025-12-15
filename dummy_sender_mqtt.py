"""
SmartAgri IoT - MQTT Dummy Data Sender (AWS Device)
====================================================
Script Python untuk mengirim data dummy sensor ke MQTT broker.
Mensimulasikan device AWS (Automatic Weather Station).

Cara pakai:
1. Install dependencies: pip install paho-mqtt
2. Ganti TOKEN dan MQTT_TOPIC sesuai device kamu
3. Jalankan: python dummy_sender_mqtt.py
"""

import paho.mqtt.client as mqtt
import json
import random
import time
from datetime import datetime

# ============================================
# KONFIGURASI - GANTI SESUAI KEBUTUHAN
# ============================================

# HiveMQ Public Broker (gratis untuk testing)
MQTT_BROKER = "broker.hivemq.com"
MQTT_PORT = 1883

# Topic MQTT (harus sama dengan mqtt_topic device di database)
# GANTI dengan topic device kamu!
MQTT_TOPIC = "ngangngong/bopal/aws1"

# Token device AWS (dapatkan dari admin panel)
# GANTI INI dengan token device kamu!
TOKEN = "pmGQfWN4WyjK2eu1"

# Interval pengiriman data (dalam detik)
SEND_INTERVAL = 5

# ============================================
# SENSOR DATA RANGES (untuk generate random)
# ============================================

# Sesuai default sensor AWS:
SENSOR_RANGES = {
    "temperature": (20.0, 35.0),      # Suhu: 20-35°C
    "humidity": (40.0, 90.0),          # Kelembaban: 40-90%
    "rainfall": (0.0, 50.0),           # Curah hujan: 0-50 mm
    "wind_speed": (0.0, 20.0),         # Kecepatan angin: 0-20 m/s
    "wind_direction": (0.0, 360.0),    # Arah angin: 0-360°
}

# ============================================
# MQTT CALLBACKS
# ============================================

def on_connect(client, userdata, flags, rc, properties=None):
    """Callback saat terhubung ke broker"""
    if rc == 0:
        print("✅ Terhubung ke MQTT Broker!")
    else:
        print(f"❌ Gagal terhubung, kode: {rc}")

def on_publish(client, userdata, mid, rc=None, properties=None):
    """Callback saat pesan berhasil dipublish"""
    pass  # Bisa ditambah logging jika perlu

# ============================================
# FUNGSI UTAMA
# ============================================

def generate_sensor_data():
    """Generate data sensor random dalam range yang realistis"""
    data = {"token": TOKEN}
    
    for sensor, (min_val, max_val) in SENSOR_RANGES.items():
        value = round(random.uniform(min_val, max_val), 2)
        data[sensor] = value
    
    return data

def print_header():
    """Print header program"""
    print("=" * 60)
    print("🌱 SmartAgri IoT - MQTT Dummy Sender (AWS Device)")
    print("=" * 60)
    print(f"📡 Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"📮 Topic: {MQTT_TOPIC}")
    print(f"🔑 Token: {TOKEN[:4]}...{TOKEN[-4:]}" if len(TOKEN) == 16 else f"⚠️  Token: {TOKEN}")
    print(f"⏱️  Interval: {SEND_INTERVAL} detik")
    print("-" * 60)
    print("Tekan Ctrl+C untuk berhenti\n")

def main():
    """Fungsi utama - koneksi MQTT dan loop pengiriman data"""
    print_header()
    
    if TOKEN == "XXXXXXXXXXXXXXXX":
        print("⚠️  WARNING: Token masih default!")
        print("   Ganti variabel TOKEN dengan token device kamu.")
        print("   Dapatkan token dari Admin Panel > Device Management\n")
    
    # Setup MQTT client
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
    client.on_connect = on_connect
    client.on_publish = on_publish
    
    print("🔌 Menghubungkan ke MQTT broker...")
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()  # Non-blocking loop
    except Exception as e:
        print(f"❌ Gagal terhubung: {e}")
        return
    
    # Tunggu koneksi
    time.sleep(2)
    
    counter = 0
    
    try:
        while True:
            counter += 1
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            
            # Generate data dummy
            data = generate_sensor_data()
            json_data = json.dumps(data)
            
            print(f"[{timestamp}] Mengirim data #{counter}...")
            print(f"   Topic: {MQTT_TOPIC}")
            print(f"   Data: {json_data}")
            
            # Publish ke MQTT
            result = client.publish(MQTT_TOPIC, json_data, qos=1)
            
            if result.rc == mqtt.MQTT_ERR_SUCCESS:
                print(f"   ✅ Sukses dipublish!")
            else:
                print(f"   ❌ Gagal publish, kode: {result.rc}")
            
            print()
            
            # Tunggu sebelum kirim lagi
            time.sleep(SEND_INTERVAL)
            
    except KeyboardInterrupt:
        print("\n\n👋 Program dihentikan oleh user.")
        print(f"   Total data terkirim: {counter}")
    finally:
        client.loop_stop()
        client.disconnect()
        print("🔌 Koneksi MQTT ditutup.")

if __name__ == "__main__":
    main()
