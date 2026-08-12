<x-app-layout>
    @push('header')
        <script src="{{ url('js/apexcharts.js') }}"></script>
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(Auth::user()->hasRole('Auditor'))
                <h1 class="p-8 bg-white font-extrabold text-center">Please use the reports for details investigation...</h1>
            @elsecan('view dashboard')
                <div class="grid grid-cols-12 gap-6 ">
                    @can('view chits')
                        <a href="{{ route('chits.issued-today') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $issued_chits }}
                                        </div>

                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Issued Chits Today
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ Storage::url('images/1728946.png') }}" alt="employees on leave"
                                            class="h-12 w-12">

                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('chits.issued-today', ['filter[government_non_gov]=0']) }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ number_format($today_revenue, 0) }}
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Today Revenue
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">

                                        <img src="{{ Storage::url('images/817729.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('chits.issued-today', ['filter[government_non_gov]=0']) }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $non_entitled }}
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Non-Entitled
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ Storage::url('images/3127109.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('chits.issued-today', ['filter[government_non_gov]=1']) }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">

                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $entitled }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Entitled
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">

                                        <img src="{{ Storage::url('images/2906361.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endcan

                    @can('view invoices')
                        <a href="{{ route('invoice.issued-today') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">

                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $issued_invoices }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Invoices
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">

                                        <img src="{{ Storage::url('issue_new_chit.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('invoice.issued-today') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $issued_invoices_revenue }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Invoices Revenue
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/invoice-revenue.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endcan

                    @can('view opd reports')
                        <a href="{{ route('reports.opd.user-wise') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            User Wise
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            OPD Report
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/reports.png') }}" alt="User Wise Report" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('reports.opd.specialist-fees') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-2xl font-bold leading-8">
                                            Specialist Fees
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Daily Summary
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/reports.png') }}" alt="Specialist Fees Report"
                                            class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endcan

                    <a href="javascript:;"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        {{ number_format($issued_invoices_revenue + $today_revenue, 2) }}
                                    </div>
                                    <div class="mt-1 text-base font-bold text-gray-600">
                                        Total Revenue
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/invoice-revenue.png') }}" alt="legal case" class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                    @can('view dashboard statistics')
                        <a href="{{ route('chits.issued') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $issued_chits }}
                                        </div>

                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Chits History
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ Storage::url('images/1728946.png') }}" alt="employees on leave"
                                            class="h-12 w-12">

                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('invoice.issued') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">

                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ $issued_invoices }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Invoices History
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">

                                        <img src="{{ Storage::url('issue_new_chit.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('reports.opd.user-wise') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            User Wise
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            OPD Report
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/reports.png') }}" alt="User Wise Report" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="javascript:;"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ number_format($government_amount_today, 2) }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Today Govt Amount
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/invoice-revenue.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>
                        <a href="javascript:;"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            {{ number_format($hif_amount_today, 2) }}
                                        </div>
                                        <div class="mt-1 text-base font-bold text-gray-600">
                                            Today HIF Amount
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/invoice-revenue.png') }}" alt="legal case" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('reports.opd.user-wise') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-3xl font-bold leading-8">
                                            User Wise
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            OPD Report
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/reports.png') }}" alt="User Wise Report" class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('reports.opd.specialist-fees') }}"
                            class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-4 intro-y bg-white">
                            <div class="p-5">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="col-span-2">
                                        <div class="text-2xl font-bold leading-8">
                                            Specialist Fees
                                        </div>
                                        <div class="mt-1 text-base  font-bold text-gray-600">
                                            Daily Summary
                                        </div>
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <img src="{{ url('images/reports.png') }}" alt="Specialist Fees Report"
                                            class="h-12 w-12">
                                    </div>
                                </div>
                            </div>
                        </a>

                    @endcan
                </div>
                @can('view dashboard statistics')
                    <h1 class="p-4 bg-white font-extrabold text-center text-2xl shadow-xl rounded-lg my-4">Executive Dashboard
                        Daily Statistics</h1>
                    <div class="grid grid-cols-12 gap-6">

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart_two">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="age_wise_chart">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart_subjects">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart_test_report">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart_test_report_operation_theater">
                            </div>
                        </div>

                        <div class="col-span-12 md:col-span-6">
                            <div class="bg-white rounded-lg shadow-lg p-4 h-[420px]" id="chart_test_report_radiology">
                            </div>
                        </div>

                    </div>
                @endcan
            @endif





        </div>
    </div>
    @can('view dashboard statistics')
        @section('custom_script')
            <script>


                var options_two = {
                    series: [
                        @foreach ($gender_wise as $gender => $count)
                            {{ $count }},
                        @endforeach
                                                                                    ],
                    chart: {
                        width: '100%',
                        height: '380px',
                        type: 'pie',
                    },
                    legend: {
                        position: 'right',
                    },
                    title: {
                        text: 'Gender-wise Patient Visits Today',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: '', //undefined
                            color: '#263238'
                        },
                    },
                    labels: [
                        @foreach ($gender_wise as $gender => $count)
                            '{{ $gender }} ({{ $count }})',
                        @endforeach
                                                                                    ],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'right'
                            }
                        }
                    }]
                };

                var chart_two = new ApexCharts(document.querySelector("#chart_two"), options_two);
                chart_two.render();


                var options = {
                    series: [
                        @foreach ($opd_department_wise as $name => $count)
                            {{ $count }},
                        @endforeach
                                                                                    ],
                    chart: {
                        width: '100%',
                        height: '380px',
                        type: 'pie',
                    },
                    legend: {
                        position: 'right',
                    },
                    title: {
                        text: 'OPD Wise Today Visits Count ',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: '', //undefined
                            color: '#263238'
                        },
                    },
                    labels: [
                        @foreach ($opd_department_wise as $name => $count)
                            '{{ $name }}',
                        @endforeach
                                                                                    ],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'right'
                            }
                        }
                    }]
                };
                var chart = new ApexCharts(document.querySelector("#chart"), options);
                chart.render();


                var service_length_options = {
                    series: [
                        @php $age_count = 0; @endphp
                                                                                            @foreach ($age_group_wise_data as $data => $count)
                                                                                                {{ $count }}, @php $age_count = $age_count + $count; @endphp
                                                                                            @endforeach
                                                                                    ],
                    dataLabels: {
                        formatter: function (val, opts) {
                            return opts.w.config.series[opts.seriesIndex]
                        },
                    },
                    chart: {
                        type: 'donut',
                        width: '100%',
                        height: '380px',
                        toolbar: {
                            show: true,
                            offsetX: 0,
                            offsetY: 0,
                            tools: {
                                download: true,
                                selection: true,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: true | '<img src="/static/icons/reset.png" width="20">',
                                customIcons: []
                            },
                            export: {
                                csv: {
                                    filename: undefined,
                                    columnDelimiter: ',',
                                    headerCategory: 'category',
                                    headerValue: 'value',
                                    dateFormatter(timestamp) {
                                        return new Date(timestamp).toDateString()
                                    }
                                },
                                svg: {
                                    filename: undefined,
                                },
                                png: {
                                    filename: undefined,
                                }
                            },
                            autoSelected: 'zoom'
                        },
                    },
                    plotOptions: {
                        pie: {
                            startAngle: -90,
                            endAngle: 90,
                            offsetY: 10
                        }
                    },
                    // theme: {
                    //     monochrome: {
                    //         enabled: true,
                    //         color: '#059f0f',
                    //         shadeTo: 'dark',
                    //         shadeIntensity: 0.65
                    //     }
                    // },
                    // markers: {
                    //     colors: ['#F44336', '#E91E63', '#9C27B0']
                    // },
                    labels: [
                        @foreach ($age_group_wise_data as $data => $count)
                            '{{ $data }}',
                        @endforeach
                                                                                    ],
                    title: {
                        text: 'Patient Age Group Count Today:  {{ $age_count }}',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '18px',
                            fontWeight: 'bold',
                            fontFamily: undefined,
                            color: '#263238'
                        },
                    },
                    grid: {
                        padding: {
                            bottom: -70
                        }
                    },
                    legend: {
                        position: 'bottom',
                    },
                    responsive: [{
                        breakpoint: 678,
                        options: {
                            chart: {
                                width: '200',
                                height: '480px'
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var age_wise_chart = new ApexCharts(document.querySelector("#age_wise_chart"), service_length_options);
                age_wise_chart.render();

                var options_subjects = {
                    series: [{
                        name: 'Admissions',
                        data: [
                            @foreach ($admission_weekly_report as $date => $count)
                                {{ $count }},
                            @endforeach
                                                                                        ]
                    }],
                    chart: {
                        type: 'bar',
                        width: '100%',
                        height: '380px',
                    },

                    title: {
                        text: 'Admission Statistics (Last 13 Days Including Today)',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                            fontFamily: undefined,
                            color: '#263238'
                        },
                    },

                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: [
                            @foreach ($admission_weekly_report as $date => $count)
                                '{{ $date }}',
                            @endforeach
                                                                                        ],
                    },
                    yaxis: {
                        title: {
                            text: 'Total (Count)'
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "" + val + ""
                            }
                        }
                    }
                };

                var chart_options_subjects = new ApexCharts(document.querySelector("#chart_subjects"), options_subjects);
                chart_options_subjects.render();



                var options_test_report = {
                    series: [
                        @foreach ($patient_test_daily_report as $key => $count)
                            {{ $count }},
                        @endforeach
                                                                                    ],
                    chart: {
                        width: '100%',
                        height: '380px',
                        type: 'pie',
                    },
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        text: 'Today Total Test Performed In Laboratory',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: '', //undefined
                            color: '#263238'
                        },
                    },
                    labels: [
                        @foreach ($patient_test_daily_report as $key => $count)
                            '{{ $key }} ({{ $count }})',
                        @endforeach
                                                                                    ],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var chart_test_report = new ApexCharts(document.querySelector("#chart_test_report"), options_test_report);
                chart_test_report.render();


                var options_test_report_operation_theater = {
                    series: [
                        @foreach ($patient_test_daily_report_op as $key => $count)
                            {{ $count }},
                        @endforeach
                                                                                    ],
                    chart: {
                        width: '100%',
                        height: '380px',
                        type: 'pie',
                    },
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        text: 'Operation Theater',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: '', //undefined
                            color: '#263238'
                        },
                    },
                    labels: [
                        @foreach ($patient_test_daily_report_op as $key => $count)
                            '{{ $key }} ({{ $count }})',
                        @endforeach
                                                                                    ],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var chart_test_report_operation_theater = new ApexCharts(document.querySelector("#chart_test_report_operation_theater"), options_test_report_operation_theater);
                chart_test_report_operation_theater.render();


                var options_test_report_radiology = {
                    series: [
                        @foreach ($patient_test_daily_report_rd as $key => $count)
                            {{ $count }},
                        @endforeach
                                                                                    ],
                    chart: {
                        width: '100%',
                        height: '380px',
                        type: 'pie',
                    },
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        text: 'Radiology',
                        align: 'center',
                        margin: 0,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold',
                            fontFamily: '', //undefined
                            color: '#263238'
                        },
                    },
                    labels: [
                        @foreach ($patient_test_daily_report_rd as $key => $count)
                            '{{ $key }} ({{ $count }})',
                        @endforeach
                                                                                    ],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var chart_test_report_radiology = new ApexCharts(document.querySelector("#chart_test_report_radiology"), options_test_report_radiology);
                chart_test_report_radiology.render();



            </script>
        @endsection
    @endcan
</x-app-layout>