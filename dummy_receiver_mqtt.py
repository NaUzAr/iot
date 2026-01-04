"""
SmartAgri IoT - MQTT Dummy Receiver (Output Control Simulator)
===============================================================
Script Python untuk menerima perintah kontrol output dari server via MQTT.
Mensimulasikan device menerima perintah ON/OFF untuk relay, pump, dll.

Cara pakai:
1. Install dependencies: pip install paho-mqtt
2. Ganti TOKEN dan MQTT_TOPIC sesuai device kamu
3. Jalankan: python dummy_receiver_mqtt.py
"""

import paho.mqtt.client as mqtt
import json
import time
from datetime import datetime

# ============================================
# KONFIGURASI - GANTI SESUAI KEBUTUHAN
# ============================================

# HiveMQ Public Broker (gratis untuk testing)
MQTT_BROKER = "smartagri.web.id"
MQTT_PORT = 1883

# Topic MQTT untuk menerima perintah output
# Format: [mqtt_topic]/control
MQTT_TOPIC = "Aws1/control"

# Token device (untuk validasi perintah)
TOKEN = "khtYF34EWlq0K2li"

# ============================================
# STATE SIMULASI OUTPUT
# ============================================
output_states = {
    "relay_1": False,
    "pump": False,
    "fan": False,
    "valve": False,
    "motor": 0,
    "led": False,
}

# ============================================
# MQTT CALLBACKS
# ============================================

def on_connect(client, userdata, flags, rc, properties=None):
    """Callback saat terhubung ke broker"""
    if rc == 0:
        print("✅ Terhubung ke MQTT Broker!")
        print(f"📡 Subscribing ke: {MQTT_TOPIC}")
        client.subscribe(MQTT_TOPIC, qos=1)
    else:
        print(f"❌ Gagal terhubung, kode: {rc}")

def on_message(client, userdata, msg):
    """Callback saat menerima pesan"""
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"\n{'='*60}")
    print(f"[{timestamp}] 📨 Perintah Diterima!")
    print(f"Topic: {msg.topic}")
    print(f"Payload: {msg.payload.decode()}")
    
    try:
        data = json.loads(msg.payload.decode())
        
        # Validasi token
        if data.get('token') != TOKEN and TOKEN != "YOUR_DEVICE_TOKEN":
            print("⚠️  Token tidak valid! Perintah diabaikan.")
            return
        
        # Proses perintah output
        action = data.get('action', '')
        
        if action == 'set_output':
            outputs = data.get('outputs', {})
            print(f"\n🎛️  Memproses output commands...")
            
            for output_name, value in outputs.items():
                old_state = output_states.get(output_name, None)
                output_states[output_name] = value
                
                if isinstance(value, bool):
                    status = "ON 🟢" if value else "OFF 🔴"
                else:
                    status = f"{value}"
                
                print(f"   • {output_name}: {status}")
            
            # Kirim response sukses
            response = {
                "token": TOKEN,
                "status": "success",
                "outputs": output_states,
                "timestamp": timestamp
            }
            
            # Publish response ke topic status
            response_topic = MQTT_TOPIC.replace('/control', '/status')
            client.publish(response_topic, json.dumps(response), qos=1)
            print(f"\n✅ Response dikirim ke: {response_topic}")
            
        elif action == 'get_status':
            # Kirim status semua output
            response = {
                "token": TOKEN,
                "status": "success",
                "outputs": output_states,
                "timestamp": timestamp
            }
            response_topic = MQTT_TOPIC.replace('/control', '/status')
            client.publish(response_topic, json.dumps(response), qos=1)
            print(f"📤 Status dikirim ke: {response_topic}")
            
        else:
            print(f"⚠️  Action tidak dikenal: {action}")
        
        print_current_state()
        
    except json.JSONDecodeError:
        print("❌ Format JSON tidak valid!")
    except Exception as e:
        print(f"❌ Error: {e}")
    
    print(f"{'='*60}")

def print_current_state():
    """Print status semua output saat ini"""
    print(f"\n📊 Status Output Saat Ini:")
    for name, value in output_states.items():
        if isinstance(value, bool):
            status = "ON 🟢" if value else "OFF 🔴"
        else:
            status = f"{value}"
        print(f"   • {name}: {status}")

def print_header():
    """Print header program"""
    print("=" * 60)
    print("🌱 SmartAgri IoT - MQTT Dummy Receiver (Output Simulator)")
    print("=" * 60)
    print(f"📡 Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"📮 Subscribed Topic: {MQTT_TOPIC}")
    print(f"🔑 Token: {TOKEN[:4]}...{TOKEN[-4:]}" if len(TOKEN) >= 8 else f"🔑 Token: {TOKEN}")
    print("-" * 60)
    print("Menunggu perintah output... (Tekan Ctrl+C untuk berhenti)")
    print()
    print("📝 Format perintah yang diterima:")
    print('''
{
    "token": "YOUR_DEVICE_TOKEN",
    "action": "set_output",
    "outputs": {
        "relay_1": true,
        "pump": false,
        "motor": 75
    }
}
''')
    print_current_state()
    print()

def main():
    """Fungsi utama - koneksi MQTT dan listen untuk perintah"""
    print_header()
    
    if TOKEN == "YOUR_DEVICE_TOKEN":
        print("⚠️  WARNING: Token masih default!")
        print("   Ganti variabel TOKEN dengan token device kamu.\n")
    
    # Setup MQTT client
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
    client.on_connect = on_connect
    client.on_message = on_message
    
    print("🔌 Menghubungkan ke MQTT broker...")
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_forever()
        
    except KeyboardInterrupt:
        print("\n\n👋 Program dihentikan oleh user.")
    except Exception as e:
        print(f"❌ Gagal terhubung: {e}")
    finally:
        client.disconnect()
        print("🔌 Koneksi MQTT ditutup.")

if __name__ == "__main__":
    main()
