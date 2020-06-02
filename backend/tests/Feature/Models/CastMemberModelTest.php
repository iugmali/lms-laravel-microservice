<?php


namespace Tests\Feature\Models;


use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CastMemberModelTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMembers = CastMember::all();
        $this->assertCount(1, $castMembers);
        $castMemberKey = array_keys($castMembers->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'
        ], $castMemberKey);
    }

    public function testCreate()
    {
        $castMember = CastMember::create(['name' => 'Carlos', 'type' => CastMember::TYPE_ACTOR]);
        $castMember->refresh();
        $this->assertEquals(36, strlen($castMember->id));
        $this->assertEquals('Carlos', $castMember->name);
        $this->assertEquals(CastMember::TYPE_ACTOR, $castMember->type);
    }

    public function testUpdate()
    {
        $castMember = CastMember::create(['name' => 'Bruno', 'type' => CastMember::TYPE_DIRECTOR]);
        $castMember->refresh();

        $data = ['name' => 'Bruno Falcao', 'type' => CastMember::TYPE_ACTOR];
        $castMember->update($data);

        foreach ($data as $key => $value){
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testDelete()
    {
        $castMember = CastMember::create(['name' => 'Bruno', 'type' => CastMember::TYPE_DIRECTOR]);
        $castMember->delete();
        $this->assertNull(CastMember::find($castMember->id));
        $castMember->restore();
        $this->assertNotNull(CastMember::find($castMember->id));
    }
}
