<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Traits\Uuid;
use Tests\TestCase;

class CastMemberUnitTest extends TestCase
{

    use DatabaseMigrations;
    private $cast_member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast_member = new CastMember();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class , Uuid::class];
        $categoryTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $categoryTraits);
    }


    public function testFillableAttribute()
    {
        $fillable = ['name','type'];
        $this->assertEquals($fillable, $this->cast_member->getFillable());
    }

    public function testDatesAttribute()
    {
        $dates = ['created_at', 'updated_at', 'deleted_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->cast_member->getDates());
        }
        $this->assertCount(count($dates), $this->cast_member->getDates());
    }

    public function testCasts()
    {
        $casts = ['id' => 'string', 'type' => 'integer'];
        $this->assertEquals($casts, $this->cast_member->getCasts());
    }

    public function testIncrement()
    {
        $this->assertFalse($this->cast_member->incrementing);
    }

}
