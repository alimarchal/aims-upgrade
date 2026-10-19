@include('partials.simple-form', [
    'title' => 'Add Department', 'section' => 'Departments', 'indexRoute' => 'department.index',
    'subtitle' => 'New OPD department. The daily limit and category are set automatically.',
    'action' => route('department.store'), 'isCreate' => true,
    'fields' => [['name' => 'name', 'label' => 'Name', 'required' => true, 'placeholder' => 'Department name']],
])
