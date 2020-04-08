<?php


namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CastMemberResource;
use App\Models\CastMember;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;

    private $cast_member, $serializedFields;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast_member = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $this->serializedFields = [
            'name',
            'type',
            'created_at',
            'updated_at',
            'deleted_at'
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));
        $response
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);
        $resource = CastMemberResource::collection(collect([$this->cast_member]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->cast_member->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->serializedFields]);
        $resource = new CastMemberResource(CastMember::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
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

    public function testSave()
    {
        $data = [
            [
                'name' => 'Bruno Falcao',
                'type' => CastMember::TYPE_DIRECTOR
            ],
            [
                'name' => 'Babu Santana',
                'type' => CastMember::TYPE_ACTOR
            ]
        ];
        foreach ($data as $key => $value) {
            // store
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' =>  $this->serializedFields]);
            $resource = new CastMemberResource(CastMember::find($this->getIdFromResponse($response)));
            $this->assertResource($response, $resource);
            // update
            $response = $this->assertUpdate($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['data' =>  $this->serializedFields]);
            $resource = new CastMemberResource(CastMember::find($this->getIdFromResponse($response)));
            $this->assertResource($response, $resource);
        }
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
