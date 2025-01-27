<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_should_create_schedule_successfully_when_no_conflict_exists_and_dates_are_weekdays()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => true,
            'start_at' => Carbon::now()->nextWeekday()->toDateTimeString(),
            'conclusion_at' => Carbon::now()->addDays(1)->nextWeekday()->toDateTimeString(),
            'user_id' => $user->id
        ];

        $response = $this->postJson('/api/schedules', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('schedules', $data);
    }

    /** @test */
    public function it_should_not_create_schedule_when_dates_conflict_with_other_active_schedules()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start_at = Carbon::now()->nextWeekday()->toDateTimeString();
        $conclusion_at = Carbon::now()->addDays(1)->nextWeekday()->toDateTimeString();

        Schedule::factory()->create([
            'user_id' => $user->id,
            'start_at' => $start_at,
            'conclusion_at' => $conclusion_at,
            'status' => true,
        ]);

        $data = [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => true,
            'start_at' => $start_at,
            'conclusion_at' => $conclusion_at,
            'user_id' => $user->id
        ];

        $response = $this->postJson('/api/schedules', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('status');
    }

    /** @test */
    public function it_should_create_schedule_with_conflicting_dates_if_status_is_false()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $start_at = Carbon::now()->nextWeekday()->toDateTimeString();
        $conclusion_at = Carbon::now()->addDays(1)->nextWeekday()->toDateTimeString();

        Schedule::factory()->create([
            'user_id' => $user->id,
            'start_at' => $start_at,
            'conclusion_at' => $conclusion_at,
            'status' => true,
        ]);

        $data = [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => false,
            'start_at' => $start_at,
            'conclusion_at' => $conclusion_at,
            'user_id' => $user->id
        ];

        $response = $this->postJson('/api/schedules', $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('schedules', $data);
    }

    /** @test */
    public function it_should_not_create_schedule_on_weekends()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => true,
            'start_at' => Carbon::now()->next(Carbon::SATURDAY)->toDateTimeString(),
            'conclusion_at' => Carbon::now()->next(Carbon::SUNDAY)->toDateTimeString(),
            'user_id' => $user->id
        ];

        $response = $this->postJson('/api/schedules', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('start_at');
    }

    /** @test */
    public function update_schedule()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $schedule = Schedule::factory()->create([
            'user_id' => $user->id,
            'status' => false
        ]);

        $updateData = [
            'title' => $this->faker->sentence,
            'type' => 'meeting',
            'description' => $this->faker->paragraph,
            'status' => true,
            'start_at' => Carbon::now()->nextWeekday()->toDateTimeString(),
            'conclusion_at' => Carbon::now()->addDays(1)->nextWeekday()->toDateTimeString(),
            'user_id' => $user->id
        ];

        $response = $this->putJson('/api/schedules/' . $schedule->id, $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('schedules', $updateData);
    }

    /** @test */
    public function delete_schedule()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $schedule = Schedule::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson('/api/schedules/' . $schedule->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }
}
