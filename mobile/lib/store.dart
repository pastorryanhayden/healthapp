import 'package:flutter/foundation.dart';
import 'package:intl/intl.dart';

import 'api.dart';

class HealthStore extends ChangeNotifier {
  HealthStore({HealthApi? api}) : api = api ?? HealthApi();

  final HealthApi api;

  DaySummary? today;
  CalendarMonth? calendar;
  WeighInHistory? weighIns;
  String? error;
  bool loading = true;
  bool estimating = false;

  Future<void> refresh() async {
    loading = today == null;
    error = null;
    notifyListeners();
    try {
      final month = DateFormat('yyyy-MM').format(DateTime.now());
      final results = await Future.wait([
        api.today(),
        api.calendar(month: month),
        api.weighIns(),
      ]);
      today = results[0] as DaySummary;
      calendar = results[1] as CalendarMonth;
      weighIns = results[2] as WeighInHistory;
    } catch (e) {
      error = e.toString();
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  Future<void> logFood(String input) async {
    estimating = true;
    error = null;
    notifyListeners();
    try {
      today = await api.logFood(input);
      await _refreshCalendarQuietly();
    } on EstimateFailedException {
      estimating = false;
      notifyListeners();
      rethrow;
    } catch (e) {
      error = e.toString();
    } finally {
      estimating = false;
      notifyListeners();
    }
  }

  Future<void> updateFood({
    required int id,
    required String name,
    required int calories,
  }) async {
    today = await api.updateFood(id: id, name: name, calories: calories);
    notifyListeners();
  }

  Future<void> deleteFood(int id) async {
    today = await api.deleteFood(id);
    await _refreshCalendarQuietly();
    notifyListeners();
  }

  Future<void> logWalk(double miles) async {
    today = await api.logWalk(miles);
    await _refreshCalendarQuietly();
    notifyListeners();
  }

  Future<void> updateWalk({required int id, required double miles}) async {
    today = await api.updateWalk(id: id, miles: miles);
    notifyListeners();
  }

  Future<void> deleteWalk(int id) async {
    today = await api.deleteWalk(id);
    await _refreshCalendarQuietly();
    notifyListeners();
  }

  Future<void> saveWeighIn(double pounds) async {
    weighIns = await api.saveWeighIn(pounds);
    await _refreshCalendarQuietly();
    notifyListeners();
  }

  Future<void> _refreshCalendarQuietly() async {
    try {
      final month = calendar?.month ?? DateFormat('yyyy-MM').format(DateTime.now());
      calendar = await api.calendar(month: month);
    } catch (_) {}
  }
}
