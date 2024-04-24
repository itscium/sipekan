<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>WIUM PAYROL SLIP</title>

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
            border: 1px solid #ddd;
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
            font-size: 12px;
            padding: 6px;
        }

        .td {
            border-left: 1px solid #dddddd;
        }

        /*tr:nth-child(even) {*/
        /*    background-color: #f2f2f2;*/
        /*}*/
        table.pay {
            table-layout: auto;
            width: 250px;
            margin-left: auto;
            margin-right: auto;
        }
        th.center, td.center {
            text-align: center;
            font-size: 20px;
        }

        #personal_identification {
            background-color: #c7dede;
        }

    </style>

</head>

<body onload="cetak()" id="pdf" style="padding: 3px">

<div class="header">
        <img src="{{ asset('img/logo.png') }}"  alt="">
        <h4>West Indonesia Union Mission</h4>
        <h5>JL. MT Haryono Blok A, Kav 4 - 5, Jakarta Selatan, 12810</h5>
</div>

<div class="row" id="personal_identification">
    <div class="column">
        <table>
            <tr>
                <th>Employee ID</th>
                <td>: </td>
                <td>{{$profile->enrollment_code}}</td>
            </tr>
            <tr>
                <th>Employee Name</th>
                <td>: </td>
                <td>{{ucwords(strtolower($profile->posted_payment->name_regular))}}</td>
            </tr>
            <tr>
                <th>Soc Sec Num</th>
                <td>: </td>
                <td>{{$profile->posted_payment->employee_code_national}}</td>
            </tr>
        </table>
    </div>
    <div class="column">
        <table>
            <tr>
                <th>Pay Period</th>
                <td>: </td>
                <td>{{date('01/m/Y', strtotime($profile->posted_payment->date_payment))}} - {{date('t/m/Y', strtotime($profile->posted_payment->date_payment))}}</td>
            </tr>
            <tr>
                <th>Payment Date</th>
                <td>: </td>
                <td>{{date('d/m/Y', strtotime($profile->posted_payment->date_payment))}}</td>
            </tr>
            <tr>
                <th>EFT #</th>
                <td>: </td>
                <td>0{{$profile->posted_payment->eft_number}}</td>
            </tr>
        </table>
    </div>
</div>
<div class="row" id="earning_deduction">
    <div class="column">
        <table>
            <thead>
                <tr class="border">
                    <th colspan="3" style="text-align: center; background-color: #c4c0c0">EARNINGS</th>
                </tr>
                <tr class="border td" style="background-color: #e7e5e5">
                    <th width="5%">No.</th>
                    <th class="td">Description</th>
                    <th class="td" style=" text-align: center;">Value</th>
                </tr>
            </thead>
            <tbody class="border">
            @foreach($earning as $index=>$earn)
                <tr>
                    <td style="text-align: center">{{$index+1}}</td>
                    <td class="td">{{$earn->allowance_name}}</td>
                    <td class="td" style="text-align: right">{{number_format($total_earn[] = $earn->value)}}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="border">
            <tr style="background-color: #e7e5e5">
                <td colspan="2"><strong>Total Earnings</strong></td>
                <td class="td" style="text-align: right"><strong>{{number_format(array_sum($total_earn))}}</strong></td>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="column">
        <table>
            <thead>
                <tr class="border">
                    <th colspan="3" style="text-align: center; background-color: #c4c0c0">DEDUCTIONS</th>
                </tr>
                <tr style="background-color: #e7e5e5">
                    <th width="5%">No.</th>
                    <th>Description</th>
                    <th class="td">Value</th>
                </tr>
            </thead>
            <tbody class="border">
            @foreach($deduction as $index=>$deduct)
                <tr>
                    <td style="text-align: center">{{$index+1}}</td>
                    <td class="td">{{$deduct->allowance_name}}</td>
                    <td class="td" style="text-align: right">{{number_format($total_deduct[] = $deduct->value)}}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="border">
            <tr style="background-color: #e7e5e5">
                <td colspan="2"><strong>Total Deductions</strong></td>
                <td class="td" style="text-align: right"><strong>{{number_format(array_sum($total_deduct))}}</strong></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<div style="margin-top: 20px" class="row">
    <table class="pay">
        <tr class="border">
            <th style="background-color: #9ba99b" class="center">Pay Amount</th>
        </tr>
        <tr>
            <td  class="center">*** <strong>Rp. {{number_format($profile->posted_payment->value)}}</strong> ***</td>
        </tr>
    </table>
</div>

<div style="margin-top: 30px; float: right; margin-right: 25px" class="row">
    Generated by <br><br> <br><br> <br>
    Payrol System
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    function cetak() {
        // setTimeout(function(){ window.close();},300);
        // window.print();

        const element = document.getElementById('pdf');
        // Choose the element and save the PDF for your user.
        html2pdf().from(element).save('Payroll' + '#0' + '{{$profile->posted_payment->eft_number}}');
        setTimeout(function(){ window.close();},3000);
    }
</script>

</body>
</html>
