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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    window.addEventListener('load', function () {
        var make = function () {
            html2pdf().set({
                margin: 0,
                filename: 'Payroll#0{{ $profile->posted_payment->eft_number }}.pdf',
                image: { type: 'jpeg', quality: 0.95 },
                html2canvas: { scale: 2, useCORS: true, backgroundColor: '#f4f6fa' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            }).from(document.getElementById('slip')).save().then(function () {
                setTimeout(function () { window.close(); }, 1500);
            });
        };
        (document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve()).then(make);
    });
</script>
</body>
</html>
