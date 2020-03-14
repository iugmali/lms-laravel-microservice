<?php
declare(strict_types=1);

namespace Tests\Traits;


use Illuminate\Foundation\Testing\TestResponse;

trait TestValidations
{
    protected abstract function model();
    protected abstract function routeStore();
    protected abstract function routeUpdate();

    protected function assertInvalidStoreAction(
        array $data,
        string $rule,
        $ruleParams = []
    ){
        $response = $this->json('POST', $this->routeStore(), $data);
        $this->assertInvalidFields($response, array_keys($data), $rule, $ruleParams);
    }

    protected function assertInvalidUpdateAction(
        array $data,
        string $rule,
        $ruleParams = []
    ){
        $response = $this->json('PUT', $this->routeUpdate($id), $data);
        $this->assertInvalidFields($response, array_keys($data), $rule, $ruleParams);
    }

    protected function assertInvalidFields(
        TestResponse $response,
        array $fields,
        string $rule,
        array $ruleParams = []
    ){
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors($fields);
        foreach ($fields as $field) {
            $fieldname = str_replace('_', ' ', $field);
            $response->assertJsonFragment([
                \Lang::get("validation.{$rule}", ['attribute' => $fieldname] + $ruleParams)
            ]);
        }
    }
}
