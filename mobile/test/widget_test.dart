import 'package:flutter_test/flutter_test.dart';
import 'package:healthapp/api.dart';

void main() {
  test('parses a day summary from the API shape', () {
    final day = DaySummary.fromJson({
      'date': '2026-09-10',
      'calories_eaten': 320,
      'calories_goal': 2000,
      'calories_remaining': 1680,
      'eating': 'pass',
      'miles_walked': 8.3,
      'miles_goal': 4,
      'walking': 'pass',
      'food_logs': [
        {
          'id': 1,
          'input': 'oatmeal',
          'name': 'Oatmeal',
          'calories': 320,
        }
      ],
      'walks': [
        {'id': 1, 'miles': 1.2},
      ],
    });

    expect(day.caloriesRemaining, 1680);
    expect(day.eatingPass, isTrue);
    expect(day.foodLogs.single.name, 'Oatmeal');
    expect(day.walks.single.miles, 1.2);
  });

  test('empty day is an eating fail', () {
    final day = DaySummary.fromJson({
      'date': '2026-09-10',
      'calories_eaten': 0,
      'calories_goal': 2000,
      'calories_remaining': 2000,
      'eating': 'fail',
      'miles_walked': 0,
      'miles_goal': 4,
      'walking': 'fail',
      'food_logs': [],
      'walks': [],
    });

    expect(day.eatingPass, isFalse);
    expect(day.walkingPass, isFalse);
  });
}
