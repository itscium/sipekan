<?php

return [
    /*
    | Wilayah bawaan untuk tampilan admin (mis. Tunjangan departemen).
    | 20003 = Uni Indonesia Kawasan Barat (WIUM).
    */
    'default_wilayah_id' => 20003,

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
