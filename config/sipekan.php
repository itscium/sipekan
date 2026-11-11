<?php

return [
    /*
    | Wilayah bawaan untuk tampilan admin (mis. Tunjangan departemen).
    | 20003 = Uni Indonesia Kawasan Barat (WIUM).
    */
    'default_wilayah_id' => 20003,

    /*
    | Wilayah yang anggaran departemennya TIDAK ditampilkan di halaman
    | Keuangan/Tunjangan departemen (pilihan admin). Hapus ID dari daftar ini
    | untuk menampilkannya kembali.
    */
    'hidden_budget_wilayah_ids' => [
        2,      // Konfrens DKI Jakarta
        6,      // Konfrens Jawa Barat
        7,      // Daerah Jawa Kawasan Tengah
        8,      // Konfrens Jawa Kawasan Timur
        9,      // Wilayah Kalimantan Barat
        10,     // Daerah Kalimantan Kawasan Timur
        11,     // Daerah Nusa Tenggara
        10002,  // Konfrens Jakarta-Banten
    ],

    /*
    | Aplikasi Android.
    | Untuk merilis versi baru: ganti file di storage/app/android/SIPekan.apk
    | lalu ubah 'version' di bawah.
    */
    'android' => [
        'file' => 'android/SIPekan.apk',   // relatif terhadap storage/app
        'version' => '1.0.0',
        'min_android' => '6.0',
    ],
];
