<?php

use App\Models\Task;
use App\Models\User;
use App\Models\Lists;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TasksTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    use CreateEntityByRequest;

    public function testFailedCreateTask()
    {
        $this->post('/task/create');
        $this->assertResponseStatus(401);

        $this->auth();

        $this->apost('/task/create');
        $this->assertResponseStatus(422);

        $this->apost('/task/create', [
            'attributes' => [
                'name' => 'task1',
                'is_completed' => false,
                'list_id' => 100,
                'executor_user_id' => 100,
                'urgency' => 3,
                'description' => 'asdfd',
            ]
        ]);
        $this->assertResponseStatus(422);
    }

    public function testSuccessCreateTask()
    {
        $list = $this->createList();

        $this->apost('/task/create', [
            'attributes' => [
                'name' => 'task1',
                'is_completed' => false,
                'list_id' => $list['data']['attributes']['id'],
                'urgency' => 3,
                'description' => 'asdfd',
            ],
        ]);
        $this->assertResponseStatus(201);
        $this->seeInDatabase('tasks', ['name' => 'task1']);
        $this->seeInDatabase('lists', ['id' => $list['data']['attributes']['id'], 'count_tasks' => 1]);
    }

    public function testFailedUpdateTask()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->apost('/task/update/' . $task['data']['attributes']['id'], [
            'attributes' => [
                'name' => 'task1',
            ],
        ]);
        $this->assertResponseStatus(405);
    }

    public function testSuccessUpdateTask()
    {
        $list = $this->createList('list1');
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->seeInDatabase('tasks', ['name' => $task['data']['attributes']['name']]);
        $this->aput('/task/update/' . $task['data']['attributes']['id'], [
            'attributes' => [
                'name' => 'task1',
                'is_completed' => false,
                'list_id' => $list['data']['attributes']['id'],
                'urgency' => $task['data']['attributes']['urgency'],
            ],
        ]);
        $this->seeInDatabase('tasks', ['name' => 'task1', 'is_completed' => false]);
        $this->seeInDatabase('lists', ['name' => 'list1', 'is_completed' => false]);
        $this->notSeeInDatabase('tasks', ['name' => $task['data']['attributes']['name']]);
        $this->assertResponseStatus(200);

        $this->aput('/task/' . $task['data']['attributes']['id'], [
            'attributes' => [
                'name' => 'task1',
                'is_completed' => true,
                'list_id' => $list['data']['attributes']['id'],
                'urgency' => $task['data']['attributes']['urgency'],
            ],
        ]);
        $this->seeInDatabase('tasks', ['name' => 'task1', 'is_completed' => true]);
        $this->seeInDatabase('lists', ['name' => 'list1', 'is_completed' => true]);
        $this->assertResponseStatus(200);
    }

    public function testFailedDeleteTask()
    {
        $this->delete('/task/delete/' . 1);
        $this->assertResponseStatus(401);

        $this->adelete('/task/delete/' . 1);
        $this->assertResponseStatus(422);
    }

    public function testSuccessDeleteTask()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->adelete('/task/delete/' . $task['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('tasks', ['id' => $task['data']['attributes']['id']]);
    }

    public function testSuccessShortDeleteTask()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->adelete('/task/' . $task['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('tasks', ['id' => $task['data']['attributes']['id']]);
    }

    public function testFailedGetTasks()
    {
        $this->get('/task/get-items');
        $this->assertResponseStatus(401);

    }

    public function testSuccessShortGetTasks()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->aget('/task');
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('data', $this->response->json());
        $this->assertArrayHasKey('items', $this->response->json('data'));
        $this->response->assertJsonFragment(['name' => $task['data']['attributes']['name']]);
    }

    public function testSuccessGetTasks()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->aget('/task/get-items');
        $this->assertResponseStatus(200);
        $this->assertArrayHasKey('data', $this->response->json());
        $this->assertArrayHasKey('items', $this->response->json('data'));
        $this->response->assertJsonFragment(['name' => $task['data']['attributes']['name']]);
    }

    public function testFailedGetTask()
    {
        $this->get('/task/get-item/1');
        $this->assertResponseStatus(401);

        $this->get('/task/4');
        $this->assertResponseStatus(401);

        $this->aget('/task/get-item');
        $this->assertResponseStatus(404);

        $this->aget('/task/4');
        $this->assertResponseStatus(422);
    }

    public function testSuccessGetTask()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);
        $this->aget('/task/get-item/' . $task['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->response->assertJsonFragment(['name' => $task['data']['attributes']['name'], 'id' => $task['data']['attributes']['id']]);
    }

    public function testSuccessShortGetTask()
    {
        $list = $this->createList();
        $task = $this->createTask($list['data']['attributes']['id']);

        $this->aget('/task/' . $task['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->response->assertJsonFragment(['name' => $task['data']['attributes']['name'], 'id' => $task['data']['attributes']['id']]);
    }

}
