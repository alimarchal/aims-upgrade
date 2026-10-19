@include('partials.simple-form', [
    'title' => 'Add Hospital Fee', 'section' => 'Hospital Fees', 'indexRoute' => 'feeType.index',
    'subtitle' => 'A negative total fee is saved as a Return Fee.',
    'action' => route('feeType.store'), 'isCreate' => true,
    'fields' => [
        ['name' => 'fee_category_id', 'label' => 'Fee category', 'type' => 'select', 'options' => \App\Models\FeeCategory::orderBy('name')->pluck('name', 'id')->all()],
        ['name' => 'type', 'label' => 'Fee name', 'required' => true, 'placeholder' => 'Fee name'],
        ['name' => 'amount', 'label' => 'Total fee', 'type' => 'number', 'required' => true, 'placeholder' => 'Total fee'],
        ['name' => 'hif', 'label' => 'HIF fee', 'type' => 'number', 'required' => true, 'placeholder' => 'HIF fee'],
    ],
])
