<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Statement of Account</title>

    <style>
        .header {
            text-align: center;
        }
        .header img {
            width: 50px;
            height: 50px;
        }

        .header h4 {
            position: relative;
            top: -10px;
            left: 10px;
        }
        .header h5 {
            position: relative;
            top: -20px;
            left: 10px;
        }

        /** {*/
        /*    box-sizing: border-box;*/
        /*}*/

        .row {
            display: flex;
            margin-left:-5px;
            margin-right:-5px;
        }

        .column {
            flex: 50%;
            padding: 5px;
        }
        .border{
            border: 1px solid #dddddd;
            border-collapse: collapse;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            border: 1px solid #ddd;
        }

        th, td {
            text-align: left;
            font-size: 11px;
            padding: 4px;
        }

        .text-right{
            text-align: right;
        }

        .td {
            border-left: 1px solid #dddddd;
        }
        .underline{
            border-bottom: 3px double;
        }


    </style>

</head>

<body onload="cetak()" id="pdf" style="padding: 3px">

<div class="header">
    <img src="{{ asset('img/logo.png') }}"  alt="">
    <h3>West Indonesia Union Mission</h3>
    <h5>JL. MT Haryono Blok A, Kav 4 - 5, Jakarta Selatan, 12810</h5>
    <h3 style="position: relative; top: -30px">Operating Fund</h3>
    {{--    <h4></h4>--}}
</div>
<div class="row">
    <div class="column">
        <table>
            <tr>
                <th>Mission/Conf.</th>
                <td>:</td>
                <td>{{ \Illuminate\Support\Facades\Auth::user()->wilayah->english_name }}</td>
            </tr>
            <tr>
                <th>Account Code</th>
                <td>:</td>
                <td>{{Auth::user()->wilayah->account_on_wium}}</td>
            </tr>
        </table>
    </div>

    <div class="column">
        <table>
            <tr>
                <th>Ending Period</th>
                <td>:</td>
                <td>{{date('F Y', strtotime($periode_akhir))}}</td>
            </tr>
            <tr>
                <th>Created Date Time</th>
                <td>:</td>
                <td>{{date('m F Y H:i:s')}} <strong>WIB</strong></td>
            </tr>
        </table>
    </div>

</div>

<div class="row">
    <table>
        <thead class="border">
        <tr>
            <th>Date</th>
            <th>Reference</th>
            <th>Journal Number</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
        </tr>
        </thead>
        <tbody class="border">
        <tr class="border">
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right"><strong>Opening Balance</strong></td>
            <td></td>
            <td></td>
            <td class="text-right"><strong>{{$saldo_awal}}</strong></td>
        </tr>
        @foreach($list_keuangan_fix as $period => $list)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><strong>Start Period {{$period}}</strong></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @foreach($list as $item)
                <tr class="border">
                    <td>{{date('d/m/Y', strtotime($item['tanggal']))}}</td>
                    <td>{{$item['reference']}}</td>
                    <td>{{$item['nomor_jurnal']}} - {{ $item['journal_line'] }}</td>
                    <td>{{$item['description']}}</td>
                    <td class="text-right">{{$item['debit']}}    </td>
                    <td class="text-right">{{$item['credit']}}</td>
                    <td class="text-right">{{$item['balance']}}    </td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="border">
            <td></td>
            <td></td>
            <td></td>
            <td><strong>Total for Account</strong></td>
            <td></td>
            <td></td>
            <td class="underline"><strong>{{$saldo_akhir}}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    function cetak() {
        // setTimeout(function(){ window.close();},300);
        // window.print();

        const element = document.getElementById('pdf');
        // Choose the element and save the PDF for your user.
        html2pdf().from(element).save('Statement of Account' + ' Period ' + '0{{ date('m/Y', strtotime($periode_awal)) }}' + ' to ' + '0{{ date('m/Y', strtotime($periode_akhir)) }}');
        setTimeout(function(){ window.close();},2000);
    }
</script>

</body>

</html>
