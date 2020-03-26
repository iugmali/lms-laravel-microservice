<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $cast_member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast_member = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->cast_member->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->cast_member->id]));
        $response
            ->assertStatus(200)
            ->assertJson($this->cast_member->toArray());
    }

    public function testInvalidData()
    {
        $data = [
            'name' => ''
        ];
        $this->assertInvalidStoreAction($data, 'required');
        $this->assertInvalidUpdateAction($data, 'required');
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'type' => ''
        ];
        $this->assertInvalidStoreAction($data, 'required');
        $this->assertInvalidUpdateAction($data, 'required');
        $data = [
            'type' => 'a'
        ];
        $this->assertInvalidStoreAction($data, 'in');
        $this->assertInvalidUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            [
                'name' => 'teste',
                'type' => CastMember::TYPE_DIRECTOR
            ],
            [
                'name' => 'testou',
                'type' => CastMember::TYPE_ACTOR
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'testeupdate',
            'type' => CastMember::TYPE_ACTOR
        ];
        $this->assertUpdate($data, $data);
    }

    public function testDestroy()
    {
        $this->cast_member->refresh();
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member'  => $this->cast_member->id]));
        $response->assertStatus(204);
        $this->assertNull(CastMember::find($this->cast_member->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->cast_member->id));
    }
    protected function routeStore()
    {
        return route('cast_members.store');
    }
    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->cast_member->id]);
    }
    protected function model() {
        return CastMember::class;
    }

}
