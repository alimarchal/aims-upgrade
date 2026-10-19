@include('partials.simple-form', [
    'title' => 'Edit Hospital Fee: '.$feeType->type, 'section' => 'Hospital Fees', 'indexRoute' => 'feeType.index',
    'subtitle' => 'A negative total fee is saved as a Return Fee.',
    'action' => route('feeType.update', $feeType->id), 'isCreate' => false,
    'fields' => [
        ['name' => 'type', 'label' => 'Fee name', 'required' => true, 'value' => $feeType->type],
        ['name' => 'amount', 'label' => 'Total fee', 'type' => 'number', 'required' => true, 'value' => $feeType->amount],
        ['name' => 'hif', 'label' => 'HIF fee', 'type' => 'number', 'value' => $feeType->hif],
    ],
])
