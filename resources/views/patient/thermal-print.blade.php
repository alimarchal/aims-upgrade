<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Receipt example</title>
    <style>
        * {
            font-size: 10px;
            margin: 3px;
            font-family: 'Tahoma';
        }

        .centered {
            font-weight: bold;
            text-align: center;
        }

        table,
        td,
        th {
            border: 1px solid black;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        @page {
            margin: 3px;
            size: 80mm auto;

            /* width height (auto means no fixed height) */
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }

        /* Styles for screen */
        .hidden-print {
            display: block;
        }

        /* Styles for print */
        @media print {
            .hidden-print {
                display: none !important;
            }

            .break-after {
                page-break-after: always;
                /* Old syntax */
                break-after: page;
                /* New syntax */
            }
        }

        body {
            margin: 3px;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <table style="border: none;">
            <thead style="border: none;">
                <tr style="border: none;">
                    <th style="border: none;"></th>
                    <th colspan="2" style="border: none;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                            style="width: auto; height: 40px;">
                    </th>
                    <th style="border: none;">
                        @php $patient_id = $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name . "\nMR: " . date('y') . '-' . $patient->id . '-' . $invoice->id . "\nMobile: " . $patient->mobile . "\nInvoice #: " . $invoice->id; @endphp
                        {!! DNS2D::getBarcodeSVG($patient_id, 'QRCODE', 1.5, 1.5) !!}
                    </th>
                </tr>
            </thead>
            <tbody style="border: none;">
                <tr style="border: none;">
                    <td style="border: none;">Patient Name:</td>
                    <td style="border: none;">
                        {{ $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name }}</td>
                    <td style="border: none;">
                        @if(!empty($patient->relationship_title))
                            {{ $patient->relationship_title }}
                        @else
                            Father / Husband
                        @endif

                    </td>
                    <td style="border: none;">
                        {{ $patient->father_husband_name }}
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none;font-weight: bolder;">Medical Record No:</td>
                    <td style="border: none;font-weight: bolder;">
                        {{ \Carbon\Carbon::now()->format('y') . '-' . $patient->id }}-{{ $invoice->id }}</td>
                    <td style="border: none;">Mobile:</td>
                    <td style="border: none;">{{$patient->mobile}}</td>
                </tr>
                <tr style="border: none;">
                    <td style="border: none;">Issue Date:</td>
                    <td style="border: none;">
                        {{ \Carbon\Carbon::parse($invoice->created_at)->format('d-M-Y h:i:sa') }}
                    </td>
                    <td style="border: none;">Blood Group:</td>
                    <td style="border: none;">{{$patient->blood_group}}</td>
                </tr>

                <tr style="border: none;">
                    <td style="border: none;">
                        Category
                    </td>
                    <td style="border: none;">
                        @if($patient->government_non_gov == 1)
                            Entitled
                        @else
                            Non-Entitled
                        @endif

                    </td>
                    <td style="border: none;">Age/Sex</td>
                    <td style="border: none;">
                        {{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1 ? 'Male' : 'Female') }}
                    </td>
                </tr>

                <tr style="border: none;">
                    <td style="border: none;">
                        Department
                    </td>
                    <td style="border: none;">
                        CRP
                        {{-- @if(!empty($department))--}}
                        {{-- {{ $department }} <br>--}}
                        {{-- @endif--}}
                    </td>

                    <td style="border: none;">
                        Head
                    </td>

                    <td style="border: none;">
                        @if(!empty($fee_category_main))
                            {{ $fee_category_main }}
                        @endif
                    </td>
                </tr>

                <tr style="border: none;">
                    <td style="border: none;">Issued By:</td>
                    <td style="border: none;">
                        {{ \App\Models\User::find($invoice->user_id)->name }}
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="font-size: 16px; text-align: center;font-weight: bolder; border: none;"> آپ
                        کا نمبر ہے ({{$chitNumber}})</td>
                </tr>

                {{-- <tr>--}}
                    {{-- <td colspan="4"
                        style="margin: 0px; padding: 0px; font-size: 10px; font-weight: bold; text-align: center">--}}
                        {{-- نوٹ : یہ کمپیوٹر سے تیار کردہ پرچی ہے اور ہم اس پرچی کی دوسری کاپی فراہم نہیں کریں گے۔--}}
                        {{-- فیس کی واپسی صرف ایک گھنٹے میں مکمن ہے۔--}}
                        {{-- </td>--}}
                    {{-- </tr>--}}
                <tr>
                    <td style="border: none; border-bottom: 1px solid black; margin: 0px; padding: 0px; font-size: 7px!important; text-align: center"
                        colspan="4">Software Developed By SeeChange Innovative - 0335-999-1441</td>
                </tr>

            </tbody>
        </table>


        @if(empty($invoice->admission))
            <h1 style="text-align: center;font-weight: bold">Patient Invoice</h1>
            <table>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Test Name</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($invoice->patient_test->groupBy('fee_type_id') as $test)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}</td>
                            <td style="padding-left: 5px!important;">
                                @if(count($test) > 1)
                                    {{ $test[0]->fee_type->type }}
                                @else
                                    {{ $test[0]->fee_type->type }}
                                @endif
                            </td>
                            <td style="text-align: center;">{{ count($test) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif


    </div>

    <button id="btnPrint" class="hidden-print" onclick="window.print()">Print</button>
    <div class="break-after"></div>
    <script>
        window.onload = function () {
            window.print();
            // Add event listener for the afterprint event
            window.onafterprint = function () {
                // Close the window after printing
                window.close();
            }
        }
    </script>
</body>

</html>