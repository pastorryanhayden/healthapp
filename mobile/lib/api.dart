import 'dart:convert';

import 'package:http/http.dart' as http;

const apiBase = 'https://health.haydenindustries.com';

class EstimateFailedException implements Exception {
  @override
  String toString() => 'Could not estimate calories.';
}

class ApiException implements Exception {
  ApiException(this.message, [this.statusCode]);

  final String message;
  final int? statusCode;

  @override
  String toString() => message;
}

class FoodLog {
  const FoodLog({
    required this.id,
    required this.input,
    required this.name,
    required this.calories,
  });

  factory FoodLog.fromJson(Map<String, dynamic> json) {
    return FoodLog(
      id: json['id'] as int,
      input: json['input'] as String,
      name: json['name'] as String,
      calories: json['calories'] as int,
    );
  }

  final int id;
  final String input;
  final String name;
  final int calories;
}

class WalkLog {
  const WalkLog({required this.id, required this.miles});

  factory WalkLog.fromJson(Map<String, dynamic> json) {
    return WalkLog(
      id: json['id'] as int,
      miles: (json['miles'] as num).toDouble(),
    );
  }

  final int id;
  final double miles;
}

class DaySummary {
  const DaySummary({
    required this.date,
    required this.caloriesEaten,
    required this.caloriesGoal,
    required this.caloriesRemaining,
    required this.eating,
    required this.milesWalked,
    required this.milesGoal,
    required this.walking,
    required this.foodLogs,
    required this.walks,
  });

  factory DaySummary.fromJson(Map<String, dynamic> json) {
    return DaySummary(
      date: json['date'] as String,
      caloriesEaten: json['calories_eaten'] as int,
      caloriesGoal: json['calories_goal'] as int,
      caloriesRemaining: json['calories_remaining'] as int,
      eating: json['eating'] as String,
      milesWalked: (json['miles_walked'] as num).toDouble(),
      milesGoal: (json['miles_goal'] as num).toDouble(),
      walking: json['walking'] as String,
      foodLogs: (json['food_logs'] as List<dynamic>)
          .map((item) => FoodLog.fromJson(item as Map<String, dynamic>))
          .toList(),
      walks: (json['walks'] as List<dynamic>)
          .map((item) => WalkLog.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }

  final String date;
  final int caloriesEaten;
  final int caloriesGoal;
  final int caloriesRemaining;
  final String eating;
  final double milesWalked;
  final double milesGoal;
  final String walking;
  final List<FoodLog> foodLogs;
  final List<WalkLog> walks;

  bool get eatingPass => eating == 'pass';
  bool get walkingPass => walking == 'pass';
}

class CalendarDay {
  const CalendarDay({
    required this.date,
    required this.eating,
    required this.walking,
    required this.weighIn,
  });

  factory CalendarDay.fromJson(Map<String, dynamic> json) {
    return CalendarDay(
      date: json['date'] as String,
      eating: json['eating'] as String,
      walking: json['walking'] as String,
      weighIn: json['weigh_in'] as bool,
    );
  }

  final String date;
  final String eating;
  final String walking;
  final bool weighIn;

  int get dayNumber => int.parse(date.split('-').last);
}

class CalendarMonth {
  const CalendarMonth({required this.month, required this.days});

  factory CalendarMonth.fromJson(Map<String, dynamic> json) {
    return CalendarMonth(
      month: json['month'] as String,
      days: (json['days'] as List<dynamic>)
          .map((item) => CalendarDay.fromJson(item as Map<String, dynamic>))
          .toList(),
    );
  }

  final String month;
  final List<CalendarDay> days;
}

class WeightPoint {
  const WeightPoint({required this.date, required this.pounds});

  factory WeightPoint.fromJson(Map<String, dynamic> json) {
    return WeightPoint(
      date: json['date'] as String,
      pounds: (json['pounds'] as num).toDouble(),
    );
  }

  final String date;
  final double pounds;
}

class WeighInHistory {
  const WeighInHistory({
    required this.data,
    required this.series,
    required this.goalPounds,
    required this.remainingPounds,
    required this.thisWeek,
    required this.lastWeek,
    required this.delta,
  });

  factory WeighInHistory.fromJson(Map<String, dynamic> json) {
    return WeighInHistory(
      data: (json['data'] as List<dynamic>)
          .map((item) => WeightPoint.fromJson(item as Map<String, dynamic>))
          .toList(),
      series: (json['series'] as List<dynamic>)
          .map((item) => WeightPoint.fromJson(item as Map<String, dynamic>))
          .toList(),
      goalPounds: (json['goal_pounds'] as num).toDouble(),
      remainingPounds: (json['remaining_pounds'] as num?)?.toDouble(),
      thisWeek: (json['this_week'] as num?)?.toDouble(),
      lastWeek: (json['last_week'] as num?)?.toDouble(),
      delta: (json['delta'] as num?)?.toDouble(),
    );
  }

  final List<WeightPoint> data;
  final List<WeightPoint> series;
  final double goalPounds;
  final double? remainingPounds;
  final double? thisWeek;
  final double? lastWeek;
  final double? delta;
}

class HealthApi {
  HealthApi({http.Client? client, this.baseUrl = apiBase})
      : _client = client ?? http.Client();

  final http.Client _client;
  final String baseUrl;

  Uri _uri(String path, [Map<String, String>? query]) {
    return Uri.parse('$baseUrl$path').replace(queryParameters: query);
  }

  Map<String, String> get _headers => const {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  Future<DaySummary> today() async {
    return DaySummary.fromJson(await _get('/api/today'));
  }

  Future<DaySummary> day(String date) async {
    return DaySummary.fromJson(await _get('/api/days/$date'));
  }

  Future<CalendarMonth> calendar({String? month}) async {
    return CalendarMonth.fromJson(
      await _get('/api/calendar', month == null ? null : {'month': month}),
    );
  }

  Future<WeighInHistory> weighIns() async {
    return WeighInHistory.fromJson(await _get('/api/weigh-ins'));
  }

  Future<DaySummary> logFood(String input, {String? date}) async {
    final body = await _send(
      'POST',
      '/api/food-logs',
      {'input': input, 'date': ?date},
      timeout: const Duration(seconds: 90),
    );
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<DaySummary> updateFood({
    required int id,
    required String name,
    required int calories,
  }) async {
    final body = await _send('PATCH', '/api/food-logs/$id', {
      'name': name,
      'calories': calories,
    });
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<DaySummary> deleteFood(int id) async {
    final body = await _send('DELETE', '/api/food-logs/$id', null);
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<DaySummary> logWalk(double miles, {String? date}) async {
    final body = await _send('POST', '/api/walks', {
      'miles': miles,
      'date': ?date,
    });
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<DaySummary> updateWalk({required int id, required double miles}) async {
    final body = await _send('PATCH', '/api/walks/$id', {'miles': miles});
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<DaySummary> deleteWalk(int id) async {
    final body = await _send('DELETE', '/api/walks/$id', null);
    return DaySummary.fromJson(body['day'] as Map<String, dynamic>);
  }

  Future<WeighInHistory> saveWeighIn(double pounds, {String? date}) async {
    final body = await _send('POST', '/api/weigh-ins', {
      'pounds': pounds,
      'date': ?date,
    });
    return WeighInHistory.fromJson(body['history'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> _get(
    String path, [
    Map<String, String>? query,
  ]) async {
    final response = await _client
        .get(_uri(path, query), headers: _headers)
        .timeout(const Duration(seconds: 20));
    return _decode(response);
  }

  Future<Map<String, dynamic>> _send(
    String method,
    String path,
    Map<String, dynamic>? payload, {
    Duration timeout = const Duration(seconds: 20),
  }) async {
    final uri = _uri(path);
    final encoded = payload == null ? null : jsonEncode(payload);
    late http.Response response;
    if (method == 'POST') {
      response = await _client
          .post(uri, headers: _headers, body: encoded)
          .timeout(timeout);
    } else if (method == 'PATCH') {
      response = await _client
          .patch(uri, headers: _headers, body: encoded)
          .timeout(timeout);
    } else if (method == 'DELETE') {
      response = await _client.delete(uri, headers: _headers).timeout(timeout);
    } else {
      throw ApiException('Unsupported method $method');
    }
    return _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    if (response.statusCode == 502) {
      throw EstimateFailedException();
    }
    if (response.statusCode >= 400) {
      var message = 'Request failed (${response.statusCode})';
      try {
        final body = jsonDecode(response.body);
        if (body is Map && body['message'] is String) {
          message = body['message'] as String;
        }
      } catch (_) {}
      throw ApiException(message, response.statusCode);
    }
    if (response.body.isEmpty) {
      return {};
    }
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}
