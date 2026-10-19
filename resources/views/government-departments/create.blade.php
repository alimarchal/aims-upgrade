@include('partials.simple-form', [
    'title' => 'Add Government Department', 'section' => 'Government Departments', 'indexRoute' => 'governmentDepartment.index',
    'action' => route('governmentDepartment.store'), 'isCreate' => true,
    'fields' => [['name' => 'name', 'label' => 'Name', 'required' => true, 'placeholder' => 'Department name']],
])
