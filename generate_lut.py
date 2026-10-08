import colorsys
import math

LUT_SIZE = 33
OUTPUT_FILENAME = "CapCut_Strong_Grade.cube"

def clamp(val, min_v=0.0, max_v=1.0):
    return max(min_v, min(val, max_v))

def s_curve(x):
    # Subtle S-curve for rich cinematic contrast
    return x * x * (3.0 - 2.0 * x)

def apply_grade(r, g, b):
    # 1. Stronger White Balance (Cool down yellow cast significantly)
    r *= 0.92
    g *= 0.95
    b *= 1.12

    # 2. Strong Highlight Roll-off (Fix forehead/nose shine)
    luma = 0.2126 * r + 0.7152 * g + 0.0722 * b
    
    if luma > 0.50:
        highlight_factor = clamp((luma - 0.50) / 0.50)
        pullback = (highlight_factor ** 1.3) * 0.18
        r -= pullback
        g -= pullback
        b -= pullback

    # 3. Gentle Contrast Blend (30% S-curve mix)
    r = clamp(r) * 0.70 + s_curve(clamp(r)) * 0.30
    g = clamp(g) * 0.70 + s_curve(clamp(g)) * 0.30
    b = clamp(b) * 0.70 + s_curve(clamp(b)) * 0.30

    # 4. Targeted HSL (Kill wall yellow, enrich melanin)
    h, s, v = colorsys.rgb_to_hsv(clamp(r), clamp(g), clamp(b))

    # Yellow range (~40° to 75° -> 0.11 to 0.21): Heavy desaturation for clean wall
    if 0.11 <= h <= 0.22:
        s *= 0.50
        v *= 0.96
    # Melanin Skin range (Red-Orange ~10° to 40° -> 0.02 to 0.11): Rich bronze boost
    elif 0.02 <= h < 0.11:
        s = clamp(s * 1.18)
        v = clamp(v * 0.95)  # Slightly deepen skin luminance for richness

    r_out, g_out, b_out = colorsys.hsv_to_rgb(h, s, v)
    return clamp(r_out), clamp(g_out), clamp(b_out)

def generate_lut():
    print(f"Generating {LUT_SIZE}x{LUT_SIZE}x{LUT_SIZE} 3D LUT...")
    with open(OUTPUT_FILENAME, "w") as f:
        f.write("TITLE \"CapCut Strong Grade\"\n")
        f.write(f"LUT_3D_SIZE {LUT_SIZE}\n")
        f.write("DOMAIN_MIN 0.0 0.0 0.0\n")
        f.write("DOMAIN_MAX 1.0 1.0 1.0\n\n")

        steps = [i / (LUT_SIZE - 1) for i in range(LUT_SIZE)]
        for b in steps:
            for g in steps:
                for r in steps:
                    r_mod, g_mod, b_mod = apply_grade(r, g, b)
                    f.write(f"{r_mod:.6f} {g_mod:.6f} {b_mod:.6f}\n")

    print(f"LUT successfully saved to: {OUTPUT_FILENAME}")

if __name__ == "__main__":
    generate_lut()