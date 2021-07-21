<?php

trait CreateEntityByRequest
{
    protected $faker = null;

    public function createList(string $name = null)
    {
        $this->faker = $this->faker ?? \Faker\Factory::create();

        $this->apost('/list/create', [
            'attributes' => [
                'name' => $name ?? $this->faker->name,
                'count_tasks' => 0,
                'is_completed' => false,
                'is_closed' => false,
            ],
        ]);

        return $this->response->json();
    }

    public function createTask($listId)
    {
        $this->faker = $this->faker ?? \Faker\Factory::create();

        $this->apost('/task/create', [
            'attributes' => [
                'name' => $this->faker->name,
                'is_completed' => false,
                'list_id' => $listId,
                'urgency' => $this->faker->randomElement([1, 2, 3, 4, 5]),
            ],
        ]);

        $task = $this->response->json();

        return $task;
    }

}
