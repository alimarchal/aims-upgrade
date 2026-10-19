@include('partials.simple-form', [
    'title' => 'Edit Government Department: '.$department->name, 'section' => 'Government Departments', 'indexRoute' => 'governmentDepartment.index',
    'action' => route('governmentDepartment.update', $department->id), 'isCreate' => false,
    'fields' => [['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $department->name]],
])
