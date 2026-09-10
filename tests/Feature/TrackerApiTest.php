<?php

namespace Tests\Feature;

use App\Ai\Agents\CalorieEstimator;
use App\Models\Food;
use App\Models\FoodLog;
use App\Models\Walk;
use App\Models\WeighIn;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackerApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-11 12:00:00', 'America/Chicago'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_today_is_a_fail_with_full_calorie_budget(): void
    {
        $this->getJson('/api/today')
            ->assertOk()
            ->assertJsonPath('date', '2026-09-11')
            ->assertJsonPath('calories_eaten', 0)
            ->assertJsonPath('calories_goal', 2000)
            ->assertJsonPath('calories_remaining', 2000)
            ->assertJsonPath('eating', 'fail')
            ->assertJsonPath('miles_walked', 0)
            ->assertJsonPath('miles_goal', 4)
            ->assertJsonPath('walking', 'fail')
            ->assertJsonPath('food_logs', [])
            ->assertJsonPath('walks', []);
    }

    public function test_catalog_hit_skips_ai_and_logs_food(): void
    {
        CalorieEstimator::fake();

        Food::factory()->create([
            'name' => 'Oatmeal',
            'normalized_name' => 'oatmeal',
            'calories' => 300,
        ]);

        $this->postJson('/api/food-logs', ['input' => '  Oatmeal  '])
            ->assertCreated()
            ->assertJsonPath('food_log.calories', 300)
            ->assertJsonPath('food_log.name', 'Oatmeal')
            ->assertJsonPath('day.calories_eaten', 300)
            ->assertJsonPath('day.calories_remaining', 1700)
            ->assertJsonPath('day.eating', 'pass');

        CalorieEstimator::assertNeverPrompted();
        $this->assertDatabaseCount('food_logs', 1);
        $this->assertDatabaseCount('foods', 1);
    }

    public function test_catalog_miss_calls_agent_and_stores_food(): void
    {
        CalorieEstimator::fake([
            ['name' => 'Oatmeal with banana', 'calories' => 350],
        ]);

        $this->postJson('/api/food-logs', ['input' => 'oatmeal with banana'])
            ->assertCreated()
            ->assertJsonPath('food_log.name', 'Oatmeal with banana')
            ->assertJsonPath('food_log.calories', 350)
            ->assertJsonPath('day.calories_eaten', 350)
            ->assertJsonPath('day.eating', 'pass');

        CalorieEstimator::assertPrompted('oatmeal with banana');
        $this->assertDatabaseHas('foods', [
            'normalized_name' => 'oatmeal with banana',
            'calories' => 350,
        ]);
    }

    public function test_going_over_calorie_goal_is_an_eating_fail(): void
    {
        CalorieEstimator::fake();

        $food = Food::factory()->create([
            'normalized_name' => 'pizza',
            'calories' => 2100,
        ]);

        FoodLog::factory()->create([
            'food_id' => $food->id,
            'date' => '2026-09-11',
            'calories' => 2100,
            'input' => 'pizza',
        ]);

        $this->getJson('/api/today')
            ->assertOk()
            ->assertJsonPath('eating', 'fail')
            ->assertJsonPath('calories_remaining', -100);
    }

    public function test_walks_add_up_to_a_pass(): void
    {
        $this->postJson('/api/walks', ['miles' => 1.2])->assertCreated();
        $this->postJson('/api/walks', ['miles' => 2.8])
            ->assertCreated()
            ->assertJsonPath('day.miles_walked', 4)
            ->assertJsonPath('day.walking', 'pass');
    }

    public function test_weigh_in_on_the_same_date_replaces_the_reading(): void
    {
        $this->postJson('/api/weigh-ins', ['pounds' => 182.4, 'date' => '2026-09-11'])->assertCreated();
        $this->postJson('/api/weigh-ins', ['pounds' => 181.1, 'date' => '2026-09-11'])
            ->assertCreated()
            ->assertJsonPath('weigh_in.pounds', 181.1)
            ->assertJsonPath('history.this_week', 181.1);

        $this->assertDatabaseCount('weigh_ins', 1);
    }

    public function test_weigh_in_history_includes_week_over_week_delta(): void
    {
        WeighIn::factory()->create(['date' => '2026-09-04', 'pounds' => 183.0]);
        WeighIn::factory()->create(['date' => '2026-09-11', 'pounds' => 181.0]);

        $this->getJson('/api/weigh-ins')
            ->assertOk()
            ->assertJsonPath('this_week', 181)
            ->assertJsonPath('last_week', 183)
            ->assertJsonPath('delta', -2);
    }

    public function test_weigh_in_history_includes_goal_and_chronological_series(): void
    {
        WeighIn::factory()->create(['date' => '2026-09-11', 'pounds' => 214.0]);
        WeighIn::factory()->create(['date' => '2026-09-04', 'pounds' => 216.2]);

        $this->getJson('/api/weigh-ins')
            ->assertOk()
            ->assertJsonPath('goal_pounds', 205)
            ->assertJsonPath('remaining_pounds', 9)
            ->assertJsonPath('series.0.date', '2026-09-04')
            ->assertJsonPath('series.0.pounds', 216.2)
            ->assertJsonPath('series.1.date', '2026-09-11')
            ->assertJsonPath('series.1.pounds', 214);
    }

    public function test_calendar_returns_every_day_in_the_month(): void
    {
        $food = Food::factory()->create(['normalized_name' => 'eggs', 'calories' => 180]);
        FoodLog::factory()->create([
            'food_id' => $food->id,
            'date' => '2026-09-11',
            'calories' => 180,
            'input' => 'eggs',
        ]);
        Walk::factory()->create(['date' => '2026-09-11', 'miles' => 4.0]);
        WeighIn::factory()->create(['date' => '2026-09-11', 'pounds' => 181.0]);

        $response = $this->getJson('/api/calendar?month=2026-09')->assertOk();

        $response->assertJsonPath('month', '2026-09');
        $this->assertCount(30, $response->json('days'));
        $this->assertSame('fail', $response->json('days.0.eating'));
        $day = collect($response->json('days'))->firstWhere('date', '2026-09-11');
        $this->assertSame('pass', $day['eating']);
        $this->assertSame('pass', $day['walking']);
        $this->assertTrue($day['weigh_in']);
    }

    public function test_empty_food_input_is_a_validation_error(): void
    {
        $this->postJson('/api/food-logs', ['input' => '   '])
            ->assertStatus(422)
            ->assertJsonValidationErrors('input');
    }

    public function test_deleting_a_missing_walk_is_not_found(): void
    {
        $this->deleteJson('/api/walks/999')->assertNotFound();
    }

    public function test_agent_failure_returns_502_and_stores_nothing(): void
    {
        CalorieEstimator::fake(function () {
            throw new \RuntimeException('provider down');
        });

        $this->postJson('/api/food-logs', ['input' => 'mystery stew'])
            ->assertStatus(502)
            ->assertJsonPath('message', 'Could not estimate calories.');

        $this->assertDatabaseCount('foods', 0);
        $this->assertDatabaseCount('food_logs', 0);
    }

    public function test_day_endpoint_returns_a_specific_date(): void
    {
        $this->getJson('/api/days/2026-09-10')
            ->assertOk()
            ->assertJsonPath('date', '2026-09-10')
            ->assertJsonPath('eating', 'fail');
    }

    public function test_foods_index_lists_the_catalog(): void
    {
        Food::factory()->create([
            'name' => 'Oatmeal',
            'normalized_name' => 'oatmeal',
            'calories' => 300,
        ]);

        $this->getJson('/api/foods')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Oatmeal')
            ->assertJsonPath('data.0.calories', 300);
    }

    public function test_can_update_a_food_log(): void
    {
        $food = Food::factory()->create([
            'name' => 'Oatmeal',
            'normalized_name' => 'oatmeal',
            'calories' => 300,
        ]);
        $log = FoodLog::factory()->create([
            'food_id' => $food->id,
            'date' => '2026-09-11',
            'calories' => 300,
            'input' => 'oatmeal',
        ]);

        $this->patchJson("/api/food-logs/{$log->id}", [
            'name' => 'Oatmeal with banana',
            'calories' => 450,
        ])
            ->assertOk()
            ->assertJsonPath('food_log.name', 'Oatmeal with banana')
            ->assertJsonPath('food_log.calories', 450)
            ->assertJsonPath('day.calories_eaten', 450)
            ->assertJsonPath('day.calories_remaining', 1550);

        $this->assertDatabaseHas('food_logs', ['id' => $log->id, 'calories' => 450]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'name' => 'Oatmeal with banana', 'calories' => 450]);
    }

    public function test_can_update_a_walk(): void
    {
        $walk = Walk::factory()->create([
            'date' => '2026-09-11',
            'miles' => 1.2,
        ]);

        $this->patchJson("/api/walks/{$walk->id}", ['miles' => 2.5])
            ->assertOk()
            ->assertJsonPath('walk.miles', 2.5)
            ->assertJsonPath('day.miles_walked', 2.5)
            ->assertJsonPath('day.walking', 'fail');
    }

    public function test_website_can_update_and_delete_food_and_walks(): void
    {
        $food = Food::factory()->create([
            'name' => 'Oatmeal',
            'normalized_name' => 'oatmeal',
            'calories' => 300,
        ]);
        $log = FoodLog::factory()->create([
            'food_id' => $food->id,
            'date' => '2026-09-11',
            'calories' => 300,
            'input' => 'oatmeal',
        ]);
        $walk = Walk::factory()->create([
            'date' => '2026-09-11',
            'miles' => 1.2,
        ]);

        $this->from('/')
            ->patch(route('food-logs.update', $log), ['name' => 'Oatmeal', 'calories' => 280])
            ->assertRedirect(route('home'));

        $this->from('/')
            ->patch(route('walks.update', $walk), ['miles' => 4])
            ->assertRedirect(route('home'));

        $this->from('/')
            ->delete(route('food-logs.destroy', $log))
            ->assertRedirect(route('home'));

        $this->from('/')
            ->delete(route('walks.destroy', $walk))
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('food_logs', ['id' => $log->id]);
        $this->assertDatabaseMissing('walks', ['id' => $walk->id]);
        $this->get('/')->assertSee('Save');
    }

    public function test_home_page_renders_today(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('left')
            ->assertSee('Log food')
            ->assertSee('205');
    }
}
