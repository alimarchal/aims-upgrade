@include('partials.simple-form', [
    'title' => 'Edit Department: '.$department->name, 'section' => 'Departments', 'indexRoute' => 'department.index',
    'action' => route('department.update', $department->id), 'isCreate' => false,
    'fields' => [
        ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $department->name],
        ['name' => 'daily_patient_limit', 'label' => 'Daily patient limit', 'type' => 'number', 'step' => '1', 'value' => $department->daily_patient_limit],
        ['name' => 'category', 'label' => 'Category', 'value' => $department->category],
    ],
])
