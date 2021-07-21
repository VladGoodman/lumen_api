<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ListsTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    use CreateEntityByRequest;

    public function testFailedCreateList()
    {
        $this->post('/list/create');
        $this->assertResponseStatus(401);

        $this->apost('/list/create');
        $this->assertResponseStatus(422);
    }

    public function testSuccessCreateList()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list1',
                'is_completed' => false,
                'count_tasks' => 10,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $this->seeInDatabase('lists', ['name' => 'list1', 'count_tasks' => 0]);
        $this->response->assertJsonFragment(['name' => 'list1', 'count_tasks' => 0]);
    }

    public function testSuccessTaskCount()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list1',
                'is_completed' => false,
                'count_tasks' => 0,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $list = $this->response->json();

        $this->apost('/task/create', [
            'attributes' => [
                'name' => 'task1',
                'is_completed' => true,
                'is_closed' => false,
                'list_id' => $list['data']['attributes']['id'],
                'urgency' => 3,
            ],
        ]);

        $this->seeInDatabase('lists', ['is_completed' => true, 'count_tasks' => 1]);
        $this->aget('/list/get-item/' . $list['data']['attributes']['id']);
        $this->response->assertJsonFragment(['is_completed' => true, 'count_tasks' => 1]);
    }

    public function testSuccessGetList()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list1',
                'is_completed' => false,
                'count_tasks' => 0,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $list = $this->response->json();

        $this->aget('/list/get-item/' . $list['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->response->assertJsonFragment(['name' => 'list1']);
    }

    public function testSuccessGetLists()
    {
        $this->createList('list1');
        $list = $this->createList('list2');
        $this->createTask($list['data']['attributes']['id']);
        $this->createTask($list['data']['attributes']['id']);
        $list = $this->createList('list3');
        $this->createTask($list['data']['attributes']['id']);

        $this->assertResponseStatus(201);

        $this->aget('/list/get-items/');
        $this->assertResponseStatus(200);
        $this->assertCount(3, $this->response->json('data')['items']);
        $this->response->json()['data']['items'][0]['name'] = 'list1';
        $this->response->json()['data']['items'][1]['name'] = 'list2';
        $this->response->json()['data']['items'][2]['name'] = 'list3';

        $this->aget('/list');
        $this->assertCount(3, $this->response->json('data')['items']);

        $this->aget('/list', ['filter' => [['name', '=', 'list2']]]);
        $this->assertCount(1, $this->response->json('data')['items']);

        $this->aget('/list', ['filter' => [['name', '>=', 'list2']]]);
        $this->assertCount(2, $this->response->json('data')['items']);

        $this->aget('/list', ['filter' => [['name', '>=', 'list2'], ['count_tasks', '<=', '1']]]);
        $this->assertCount(1, $this->response->json('data')['items']);

        $this->aget('/list', ['order' => [['name', 'desc']]]);
        $this->assertCount(3, $this->response->json('data')['items']);
        $this->response->json()['data']['items'][0]['name'] = 'list3';

        $this->aget('/list', ['filter' => [['name', '<=', 'list2']], 'order' => [['name', 'desc']]]);
        $this->assertCount(2, $this->response->json('data')['items']);
        $this->response->json()['data']['items'][0]['name'] = 'list2';

        $this->aget('/list', ['per_page' => 2, 'page' => 2]);
        $this->assertCount(1, $this->response->json('data')['items']);
    }

    public function testSuccessShortGetList()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list1',
                'is_completed' => false,
                'count_tasks' => 0,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $list = $this->response->json();

        $this->aget('/list/' . $list['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->response->assertJsonFragment(['name' => 'list1']);
    }

    public function testSuccessDeleteList()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list2',
                'is_completed' => false,
                'count_tasks' => 0,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $list = $this->response->json();

        $this->adelete('/list/delete/' . $list['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('lists', ['name' => 'list2']);
    }

    public function testSuccessShortDeleteList()
    {
        $this->apost('/list/create', [
            'attributes' => [
                'name' => 'list2',
                'is_completed' => false,
                'count_tasks' => 0,
                'is_closed' => false,
            ],
        ]);
        $this->assertResponseStatus(201);
        $list = $this->response->json();

        $this->adelete('/list/' . $list['data']['attributes']['id']);
        $this->assertResponseStatus(200);
        $this->notSeeInDatabase('lists', ['name' => 'list2']);
    }

}
