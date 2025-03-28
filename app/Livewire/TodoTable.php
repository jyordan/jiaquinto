<?php

namespace App\Livewire;

use Livewire\Component;

class TodoTable extends Component
{
    public $todos = [];
    public $editTodoId = null;
    public $editForm = ['name' => '', 'task' => '', 'country' => '', 'state' => '', 'city' => ''];
    public $newTodo = ['name' => '', 'task' => '', 'country' => '', 'state' => '', 'city' => ''];

    public $countries = [
        'USA' => ['California', 'New York'],
        'India' => ['Delhi', 'Karnataka'],
        'Canada' => ['Ontario', 'Quebec'],
    ];

    public $states = [
        'California' => ['Los Angeles', 'San Francisco'],
        'New York' => ['New York City', 'Buffalo'],
        'Delhi' => ['New Delhi', 'Gurgaon'],
        'Karnataka' => ['Bangalore', 'Mysore'],
        'Ontario' => ['Toronto', 'Ottawa'],
        'Quebec' => ['Montreal', 'Quebec City'],
    ];

    public $cities = [];

    public function mount()
    {
        // Sample To-Do List (Temporary Array)
        $this->todos = [
            ['id' => 1, 'name' => 'Alice', 'task' => 'Buy groceries', 'country' => 'USA', 'state' => 'California', 'city' => 'Los Angeles'],
            ['id' => 2, 'name' => 'Bob', 'task' => 'Complete project report', 'country' => 'India', 'state' => 'Delhi', 'city' => 'New Delhi'],
            ['id' => 3, 'name' => 'Charlie', 'task' => 'Call the electrician', 'country' => 'USA', 'state' => 'New York', 'city' => 'New York City'],
        ];
    }

    public function updatedNewTodoCountry($country)
    {
        // Reset state and city when country is changed
        $this->newTodo['state'] = '';
        $this->newTodo['city'] = '';
    }

    public function updatedNewTodoState($state)
    {
        // Reset city when state is changed
        $this->newTodo['city'] = '';
    }

    public function updatedEditFormCountry($country)
    {
        // Reset state and city when country is changed
        $this->editForm['state'] = '';
        $this->editForm['city'] = '';
    }

    public function updatedEditFormState($state)
    {
        // Reset city when state is changed
        $this->editForm['city'] = '';
    }

    public function edit($todoId)
    {
        $this->editTodoId = $todoId;
        $todo = collect($this->todos)->firstWhere('id', $todoId);
        $this->editForm = $todo;
    }

    public function save()
    {
        foreach ($this->todos as &$todo) {
            if ($todo['id'] === $this->editTodoId) {
                $todo['name'] = $this->editForm['name'];
                $todo['task'] = $this->editForm['task'];
                $todo['country'] = $this->editForm['country'];
                $todo['state'] = $this->editForm['state'];
                $todo['city'] = $this->editForm['city'];
                break;
            }
        }
        $this->editTodoId = null;
    }

    public function cancel()
    {
        $this->editTodoId = null;
    }

    public function add()
    {
        $this->todos[] = [
            'id' => count($this->todos) + 1,
            'name' => $this->newTodo['name'],
            'task' => $this->newTodo['task'],
            'country' => $this->newTodo['country'],
            'state' => $this->newTodo['state'],
            'city' => $this->newTodo['city'],
        ];
        $this->newTodo = ['name' => '', 'task' => '', 'country' => '', 'state' => '', 'city' => ''];
    }

    public function delete($todoId)
    {
        $this->todos = array_filter($this->todos, fn($todo) => $todo['id'] !== $todoId);
    }

    public function render()
    {
        return view('livewire.todo-table');
    }
}
