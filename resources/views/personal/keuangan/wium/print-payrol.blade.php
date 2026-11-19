<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji {{ \Illuminate\Support\Carbon::parse($profile->posted_payment->date_payment)->format('m-Y') }} · SIPekan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@600&display=swap">
</head>
<body>
@include('personal.keuangan.wium.slip')

<script>
    // Tunggu font dan logo selesai dimuat agar hasil cetak tidak memakai font cadangan
    window.addEventListener('load', function () {
        var go = function () {
            window.print();
            setTimeout(function () { window.close(); }, 300);
        };
        (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(go);
    });
</script>
</body>
</html>
