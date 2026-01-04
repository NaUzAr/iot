"""
Script to generate PWA icons in multiple sizes from source images.
Run this script to create all required icon sizes for Android APK.
"""

from PIL import Image
import os

# Icon sizes required for PWA and Android
ICON_SIZES = [72, 96, 128, 144, 152, 192, 384, 512]

# Source images - use the new logo
SOURCE_ICON = r"d:\01. Program\00. OTW JADI WEB DEV\Beljar\laravel\belajar\public\images\logo.png"
SOURCE_MASKABLE = r"d:\01. Program\00. OTW JADI WEB DEV\Beljar\laravel\belajar\public\images\logo.png"

# Output directory
OUTPUT_DIR = r"d:\01. Program\00. OTW JADI WEB DEV\Beljar\laravel\belajar\public\icons"

def generate_icons():
    """Generate icons in all required sizes."""
    
    # Ensure output directory exists
    os.makedirs(OUTPUT_DIR, exist_ok=True)
    
    # Generate regular icons
    print("Generating regular icons...")
    with Image.open(SOURCE_ICON) as img:
        # Convert to RGBA if needed
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        for size in ICON_SIZES:
            output_path = os.path.join(OUTPUT_DIR, f"icon-{size}.png")
            resized = img.resize((size, size), Image.Resampling.LANCZOS)
            resized.save(output_path, "PNG", optimize=True)
            print(f"  ✓ icon-{size}.png created")
    
    # Generate maskable icons (192 and 512)
    print("\nGenerating maskable icons...")
    with Image.open(SOURCE_MASKABLE) as img:
        if img.mode != 'RGBA':
            img = img.convert('RGBA')
        
        for size in [192, 512]:
            output_path = os.path.join(OUTPUT_DIR, f"maskable-{size}.png")
            resized = img.resize((size, size), Image.Resampling.LANCZOS)
            resized.save(output_path, "PNG", optimize=True)
            print(f"  ✓ maskable-{size}.png created")
    
    print("\n✅ All icons generated successfully!")
    print(f"📁 Output directory: {OUTPUT_DIR}")

if __name__ == "__main__":
    generate_icons()
