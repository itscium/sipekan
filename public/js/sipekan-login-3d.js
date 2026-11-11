/*
 * SIPekan - animasi 3D halaman login.
 * Pita mengilap yang meliuk pelan dan mengikuti gerak kursor.
 * Jika WebGL tidak tersedia, panel tetap tampil dengan latar gradasi (tanpa 3D).
 */
import * as THREE from 'three';

const host = document.getElementById('spAuth3d');
if (host) start(host);

/**
 * Lingkungan pantulan: langit gradasi biru tua -> lavender -> putih + beberapa
 * panel cahaya. Permukaan krom memantulkan ini sehingga tampak kontras di latar terang.
 */
function makeEnvironment() {
    const env = new THREE.Scene();
    const sky = new THREE.SphereGeometry(20, 48, 24);
    const top = new THREE.Color(0xffffff), mid = new THREE.Color(0xb7c4ff), low = new THREE.Color(0x0b2350);
    const colors = [];
    const p = sky.attributes.position;
    for (let i = 0; i < p.count; i++) {
        const h = p.getY(i) / 20; // -1 (bawah) .. 1 (atas)
        const c = h > 0 ? mid.clone().lerp(top, h) : mid.clone().lerp(low, Math.min(1, -h * 1.6));
        colors.push(c.r, c.g, c.b);
    }
    sky.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    env.add(new THREE.Mesh(sky, new THREE.MeshBasicMaterial({ vertexColors: true, side: THREE.BackSide })));

    // Panel cahaya untuk kilau tajam pada tepi pita
    const panel = (w, h, color, x, y, z, ry) => {
        const m = new THREE.Mesh(new THREE.PlaneGeometry(w, h), new THREE.MeshBasicMaterial({ color, side: THREE.DoubleSide }));
        m.position.set(x, y, z);
        m.lookAt(0, 0, 0);
        m.rotation.z += ry;
        env.add(m);
    };
    panel(14, 2.2, 0xffffff, 0, 9, 6, 0);
    panel(6, 10, 0xe9e4ff, -12, 2, 4, 0);
    panel(5, 8, 0xffe9dc, 12, 1, -3, 0);
    panel(10, 1.2, 0x7aa7ff, 0, -6, 10, 0.3);
    return env;
}

function start(host) {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true,
            powerPreference: 'high-performance',
            // Mode tanpa animasi hanya menggambar satu frame: simpan agar tidak hilang saat layar disusun ulang
            preserveDrawingBuffer: reduceMotion,
        });
    } catch (e) {
        return; // tanpa WebGL: cukup latar gradasi CSS
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    host.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(makeEnvironment(), 0.03).texture;

    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
    camera.position.set(0, 0.2, 11);

    // Cahaya berwarna untuk pantulan biru dan lavender pada pita
    const key = new THREE.DirectionalLight(0xffffff, 1.4);
    key.position.set(3, 5, 6);
    const blue = new THREE.PointLight(0x3b7be0, 30, 30);
    blue.position.set(-5, -2, 4);
    const lilac = new THREE.PointLight(0xb9a6ff, 25, 30);
    lilac.position.set(5, 3, 2);
    scene.add(key, blue, lilac);

    // ---------- Pita ----------
    const LEN = 16, WIDTH = 1.9, SEG_X = 360, SEG_Y = 10;
    const geo = new THREE.PlaneGeometry(LEN, WIDTH, SEG_X, SEG_Y);
    const base = geo.attributes.position.array.slice(); // posisi awal (x, y)
    const pos = geo.attributes.position;

    const mat = new THREE.MeshPhysicalMaterial({
        color: 0xe6ecff,
        metalness: 1,
        roughness: 0.1,
        clearcoat: 1,
        clearcoatRoughness: 0.05,
        iridescence: 0.55,
        iridescenceIOR: 1.4,
        iridescenceThicknessRange: [200, 700],
        envMapIntensity: 1.4,
        side: THREE.DoubleSide,
    });

    const ribbon = new THREE.Mesh(geo, mat);
    ribbon.position.y = 1.5;
    ribbon.rotation.z = -0.32;
    scene.add(ribbon);

    // Pita kedua yang lebih tipis dan transparan sebagai lapisan belakang
    const geo2 = geo.clone();
    const pos2 = geo2.attributes.position;
    const mat2 = mat.clone();
    mat2.transparent = true;
    mat2.opacity = 0.45;
    mat2.color = new THREE.Color(0xdfe8ff);
    const ribbon2 = new THREE.Mesh(geo2, mat2);
    ribbon2.position.set(0.6, 0.2, -2.2);
    ribbon2.rotation.z = -0.22;
    ribbon2.scale.set(1, 0.55, 1);
    scene.add(ribbon2);

    function deform(attr, t, phase) {
        const a = attr.array;
        for (let i = 0; i < a.length; i += 3) {
            const x = base[i];
            const v = base[i + 1];                      // posisi melintang pita
            const u = x / LEN;                          // -0.5 .. 0.5
            const twist = x * 0.32 + t * 0.45 + phase;  // puntiran sepanjang pita
            const cy = Math.sin(x * 0.42 + t * 0.6 + phase) * 1.15 + Math.sin(x * 0.17 - t * 0.3) * 0.6;
            const cz = Math.cos(x * 0.33 + t * 0.5 + phase) * 1.6;
            a[i] = x + Math.sin(t * 0.35 + u * 3) * 0.25;
            a[i + 1] = cy + v * Math.cos(twist);
            a[i + 2] = cz + v * Math.sin(twist);
        }
        attr.needsUpdate = true;
    }

    let started = false; // frame() baru boleh dipanggil setelah semua variabel siap

    // ---------- Ukuran & interaksi ----------
    function resize() {
        const w = host.clientWidth, h = host.clientHeight;
        if (!w || !h) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        // Panel sempit: mundurkan kamera agar pita tetap terlihat utuh
        camera.position.z = w / h < 0.9 ? 15 : 11;
        camera.updateProjectionMatrix();
        // setSize mengosongkan kanvas; gambar ulang agar frame diam (reduce motion) tidak hilang
        if (started) frame();
    }
    new ResizeObserver(resize).observe(host);
    resize();

    const pointer = { x: 0, y: 0 }, smooth = { x: 0, y: 0 };
    window.addEventListener('pointermove', (e) => {
        const r = host.getBoundingClientRect();
        pointer.x = ((e.clientX - r.left) / r.width - 0.5) * 2;
        pointer.y = ((e.clientY - r.top) / r.height - 0.5) * 2;
    }, { passive: true });


    // ---------- Animasi ----------
    const clock = new THREE.Clock();
    let visible = true;
    new IntersectionObserver((en) => { visible = en[0].isIntersecting; }).observe(host);

    function frame() {
        const t = reduceMotion ? 1.5 : clock.getElapsedTime();
        deform(pos, t, 0);
        geo.computeVertexNormals();
        deform(pos2, t * 0.8, 1.7);
        geo2.computeVertexNormals();

        smooth.x += (pointer.x - smooth.x) * 0.04;
        smooth.y += (pointer.y - smooth.y) * 0.04;
        scene.rotation.y = smooth.x * 0.18;
        scene.rotation.x = smooth.y * 0.1;

        renderer.render(scene, camera);
    }

    // Mode malam: pita sedikit diredupkan agar tidak menyilaukan di latar gelap
    const applyTheme = () => {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        renderer.toneMappingExposure = dark ? 0.82 : 1.05;
        if (started) frame();
    };
    window.addEventListener('sp-theme', applyTheme);
    applyTheme();

    started = true;

    if (reduceMotion) {
        frame(); // satu gambar diam bagi pengguna yang mematikan animasi
        host.classList.add('is-ready');
        return;
    }

    renderer.setAnimationLoop(() => { if (visible && !document.hidden) frame(); });
    host.classList.add('is-ready');
}
